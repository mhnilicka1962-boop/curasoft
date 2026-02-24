<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\WebAuthnCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthnController extends Controller
{
    // ── REGISTRATION ──────────────────────────────────────────────

    public function registerOptions(Request $request): JsonResponse
    {
        $user      = Auth::user();
        $challenge = random_bytes(32);
        $request->session()->put('webauthn_challenge', base64_encode($challenge));

        return response()->json([
            'challenge' => $this->b64u($challenge),
            'rp' => [
                'id'   => $request->getHost(),
                'name' => config('theme.app_name', 'Spitex'),
            ],
            'user' => [
                'id'          => $this->b64u(pack('N', $user->id)),
                'name'        => $user->email,
                'displayName' => $user->vorname . ' ' . $user->nachname,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification'        => 'required',
                'residentKey'             => 'preferred',
                'requireResidentKey'      => false,
            ],
            'timeout'     => 60000,
            'attestation' => 'none',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $user            = Auth::user();
        $data            = $request->json()->all();
        $clientDataJSON  = $this->b64uDecode($data['response']['clientDataJSON']);
        $attestationObj  = $this->b64uDecode($data['response']['attestationObject']);
        $credentialId    = $data['id'];
        $geraetName      = trim($data['geraet_name'] ?? '') ?: null;

        $clientData = json_decode($clientDataJSON, true);
        if ($clientData['type'] !== 'webauthn.create') {
            return response()->json(['error' => 'Falscher Typ'], 400);
        }

        $savedChallenge = $request->session()->pull('webauthn_challenge');
        if (!$savedChallenge || $clientData['challenge'] !== $this->b64u(base64_decode($savedChallenge))) {
            return response()->json(['error' => 'Challenge ungültig'], 400);
        }

        $expectedOrigin = $request->getScheme() . '://' . $request->getHttpHost();
        if ($clientData['origin'] !== $expectedOrigin) {
            return response()->json(['error' => 'Origin ungültig: ' . $clientData['origin']], 400);
        }

        $decoded  = $this->cborDecode($attestationObj);
        $authData = $decoded['authData'];

        $rpIdHash = hash('sha256', $request->getHost(), true);
        if (substr($authData, 0, 32) !== $rpIdHash) {
            return response()->json(['error' => 'rpId stimmt nicht überein'], 400);
        }

        $parsed = $this->parseAuthData($authData);
        if (!$parsed['credentialId'] || !$parsed['publicKey']) {
            return response()->json(['error' => 'Kein Credential in authData'], 400);
        }

        $spki = $this->coseKeyToSpki($parsed['publicKey']);
        if (!$spki) {
            return response()->json(['error' => 'Schlüsseltyp nicht unterstützt'], 400);
        }

        WebAuthnCredential::updateOrCreate(
            ['credential_id' => $credentialId],
            [
                'benutzer_id'     => $user->id,
                'public_key_spki' => base64_encode($spki),
                'counter'         => $parsed['signCount'],
                'geraet_name'     => $geraetName,
            ]
        );

        return response()->json(['ok' => true]);
    }

    // ── AUTHENTICATION ─────────────────────────────────────────────

    public function authenticateOptions(Request $request): JsonResponse
    {
        $challenge = random_bytes(32);
        $request->session()->put('webauthn_challenge', base64_encode($challenge));

        $allowCredentials = [];
        if ($email = $request->query('email')) {
            $benutzer = Benutzer::where('email', $email)->first();
            if ($benutzer) {
                $allowCredentials = WebAuthnCredential::where('benutzer_id', $benutzer->id)
                    ->pluck('credential_id')
                    ->map(fn($id) => ['type' => 'public-key', 'id' => $id])
                    ->values()
                    ->all();
            }
        }

        return response()->json([
            'challenge'        => $this->b64u($challenge),
            'rpId'             => $request->getHost(),
            'allowCredentials' => $allowCredentials,
            'userVerification' => 'required',
            'timeout'          => 60000,
        ]);
    }

    public function authenticate(Request $request): JsonResponse
    {
        $data           = $request->json()->all();
        $credentialId   = $data['id'];
        $clientDataJSON = $this->b64uDecode($data['response']['clientDataJSON']);
        $authDataRaw    = $this->b64uDecode($data['response']['authenticatorData']);
        $signatureRaw   = $this->b64uDecode($data['response']['signature']);

        $credential = WebAuthnCredential::where('credential_id', $credentialId)->first();
        if (!$credential) {
            return response()->json(['error' => 'Gerät nicht registriert'], 400);
        }

        $clientData = json_decode($clientDataJSON, true);
        if ($clientData['type'] !== 'webauthn.get') {
            return response()->json(['error' => 'Falscher Typ'], 400);
        }

        $savedChallenge = $request->session()->pull('webauthn_challenge');
        if (!$savedChallenge) {
            return response()->json(['error' => 'Keine Challenge in Session'], 400);
        }
        if ($clientData['challenge'] !== $this->b64u(base64_decode($savedChallenge))) {
            return response()->json(['error' => 'Challenge ungültig'], 400);
        }

        $expectedOrigin = $request->getScheme() . '://' . $request->getHttpHost();
        if ($clientData['origin'] !== $expectedOrigin) {
            return response()->json(['error' => 'Origin ungültig'], 400);
        }

        $rpIdHash = hash('sha256', $request->getHost(), true);
        if (substr($authDataRaw, 0, 32) !== $rpIdHash) {
            return response()->json(['error' => 'rpId stimmt nicht überein'], 400);
        }

        $flags = ord($authDataRaw[32]);
        if (!($flags & 0x04)) {
            return response()->json(['error' => 'Benutzerverifikation nicht bestätigt'], 400);
        }

        $spkiDer    = base64_decode($credential->public_key_spki);
        $signedData = $authDataRaw . hash('sha256', $clientDataJSON, true);

        if (!$this->verifySignature($spkiDer, $signedData, $signatureRaw)) {
            return response()->json(['error' => 'Signatur ungültig'], 400);
        }

        $newCounter = unpack('N', substr($authDataRaw, 33, 4))[1];
        if ($newCounter !== 0 && $newCounter <= $credential->counter) {
            return response()->json(['error' => 'Counter-Fehler (möglicher Replay-Angriff)'], 400);
        }
        $credential->update(['counter' => $newCounter]);

        Auth::login($credential->benutzer, remember: true);
        $request->session()->regenerate();

        return response()->json(['ok' => true, 'redirect' => route('dashboard')]);
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $credential = WebAuthnCredential::where('id', $id)
            ->where('benutzer_id', Auth::id())
            ->firstOrFail();
        $credential->delete();
        return response()->json(['ok' => true]);
    }

    // ── HELPERS ───────────────────────────────────────────────────

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64uDecode(string $data): string
    {
        $pad = strlen($data) % 4;
        if ($pad) $data .= str_repeat('=', 4 - $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function cborDecode(string $data): mixed
    {
        $offset = 0;
        return $this->cborDecodeItem($data, $offset);
    }

    private function cborDecodeItem(string $data, int &$offset): mixed
    {
        $byte  = ord($data[$offset++]);
        $major = ($byte >> 5) & 0x07;
        $info  = $byte & 0x1f;
        $arg   = $this->cborArgument($data, $offset, $info);

        if ($major === 0) return $arg;
        if ($major === 1) return -1 - $arg;
        if ($major === 2 || $major === 3) return $this->cborReadBytes($data, $offset, $arg);
        if ($major === 4) {
            $arr = [];
            for ($i = 0; $i < $arg; $i++) $arr[] = $this->cborDecodeItem($data, $offset);
            return $arr;
        }
        if ($major === 5) {
            $map = [];
            for ($i = 0; $i < $arg; $i++) {
                $k       = $this->cborDecodeItem($data, $offset);
                $map[$k] = $this->cborDecodeItem($data, $offset);
            }
            return $map;
        }
        if ($major === 6) return $this->cborDecodeItem($data, $offset);
        throw new \RuntimeException("CBOR major $major nicht unterstützt");
    }

    private function cborArgument(string $data, int &$offset, int $info): int
    {
        if ($info <= 23) return $info;
        if ($info === 24) return ord($data[$offset++]);
        if ($info === 25) { $v = unpack('n', substr($data, $offset, 2))[1]; $offset += 2; return $v; }
        if ($info === 26) { $v = unpack('N', substr($data, $offset, 4))[1]; $offset += 4; return $v; }
        if ($info === 27) {
            $hi = unpack('N', substr($data, $offset, 4))[1];
            $lo = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
            return ($hi << 32) | $lo;
        }
        throw new \RuntimeException("CBOR info $info nicht unterstützt");
    }

    private function cborReadBytes(string $data, int &$offset, int $len): string
    {
        $bytes   = substr($data, $offset, $len);
        $offset += $len;
        return $bytes;
    }

    private function parseAuthData(string $authData): array
    {
        $signCount    = unpack('N', substr($authData, 33, 4))[1];
        $flags        = ord($authData[32]);
        $credentialId = null;
        $publicKey    = null;
        $offset       = 37;

        if ($flags & 0x40) {
            $offset      += 16;
            $credIdLen    = unpack('n', substr($authData, $offset, 2))[1];
            $offset      += 2;
            $credIdRaw    = substr($authData, $offset, $credIdLen);
            $offset      += $credIdLen;
            $credentialId = $this->b64u($credIdRaw);
            $coseKeyRaw   = substr($authData, $offset);
            $tmp          = 0;
            $publicKey    = $this->cborDecodeItem($coseKeyRaw, $tmp);
        }

        return compact('signCount', 'flags', 'credentialId', 'publicKey');
    }

    private function coseKeyToSpki(array $coseKey): ?string
    {
        $kty = $coseKey[1] ?? null;

        if ($kty === 2) {
            $x = $coseKey[-2] ?? null;
            $y = $coseKey[-3] ?? null;
            if (!$x || !$y || strlen($x) !== 32 || strlen($y) !== 32) return null;
            $point    = "\x04" . $x . $y;
            $ecOid    = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
            $curveOid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
            $algSeq   = $this->derSeq($ecOid . $curveOid);
            $bitStr   = "\x03" . $this->derLen(strlen($point) + 1) . "\x00" . $point;
            return $this->derSeq($algSeq . $bitStr);
        }

        if ($kty === 3) {
            $n = $coseKey[-1] ?? null;
            $e = $coseKey[-2] ?? null;
            if (!$n || !$e) return null;
            $nDer   = "\x02" . $this->derLen(strlen($n) + 1) . "\x00" . $n;
            $eDer   = "\x02" . $this->derLen(strlen($e)) . $e;
            $keySeq = $this->derSeq($nDer . $eDer);
            $bitStr = "\x03" . $this->derLen(strlen($keySeq) + 1) . "\x00" . $keySeq;
            $rsaOid = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
            $algSeq = $this->derSeq($rsaOid);
            return $this->derSeq($algSeq . $bitStr);
        }

        return null;
    }

    private function derSeq(string $content): string
    {
        return "\x30" . $this->derLen(strlen($content)) . $content;
    }

    private function derLen(int $len): string
    {
        if ($len < 0x80) return chr($len);
        if ($len < 0x100) return "\x81" . chr($len);
        return "\x82" . chr($len >> 8) . chr($len & 0xff);
    }

    private function verifySignature(string $spkiDer, string $signedData, string $signature): bool
    {
        $pem    = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spkiDer), 64) . "-----END PUBLIC KEY-----";
        $pubKey = openssl_pkey_get_public($pem);
        if (!$pubKey) return false;
        return openssl_verify($signedData, $signature, $pubKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
