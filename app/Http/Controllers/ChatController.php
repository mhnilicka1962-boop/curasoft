<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Chat;
use App\Models\ChatNachricht;
use App\Models\ChatTeilnehmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index()
    {
        $orgId  = $this->orgId();
        $userId = auth()->id();

        // Auto-Delete älterer Nachrichten (einmal pro Tag)
        $cacheKey = 'chat_cleanup_' . $orgId;
        if (!Cache::has($cacheKey)) {
            ChatNachricht::whereHas('chat', fn($q) => $q->where('organisation_id', $orgId))
                ->where('created_at', '<', now()->subDays(14))
                ->whereNull('geloescht_am')
                ->update(['geloescht_am' => now()]);
            Cache::put($cacheKey, true, now()->addDay());
        }

        // Team-Chat: einmalig anlegen wenn noch nicht vorhanden
        $teamChat = Chat::firstOrCreate(
            ['organisation_id' => $orgId, 'typ' => 'team'],
            ['name' => 'Team']
        );
        ChatTeilnehmer::firstOrCreate(['chat_id' => $teamChat->id, 'benutzer_id' => $userId]);

        // Direkt-Chats des aktuellen Benutzers
        $direktChats = Chat::where('organisation_id', $orgId)
            ->where('typ', 'direkt')
            ->whereHas('teilnehmer', fn($q) => $q->where('benutzer_id', $userId))
            ->with(['teilnehmer.benutzer'])
            ->get()
            ->map(function ($chat) use ($userId) {
                $anderer = $chat->teilnehmer->first(fn($t) => $t->benutzer_id !== $userId);
                $chat->anderer_benutzer = $anderer?->benutzer;
                return $chat;
            })
            ->filter(fn($c) => $c->anderer_benutzer !== null)
            ->sortByDesc('updated_at')
            ->values();

        // Alle anderen Benutzer für neue Direktnachricht
        $benutzer = Benutzer::where('organisation_id', $orgId)
            ->where('id', '!=', $userId)
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->get();

        // Ungelesen pro Chat
        $ungelesenzahl = fn(int $chatId) => DB::table('chat_nachrichten as cn')
            ->join('chat_teilnehmer as ct', function ($j) use ($userId) {
                $j->on('ct.chat_id', '=', 'cn.chat_id')->where('ct.benutzer_id', $userId);
            })
            ->where('cn.chat_id', $chatId)
            ->whereNull('cn.geloescht_am')
            ->where('cn.absender_id', '!=', $userId)
            ->whereRaw('cn.id > ct.letzte_gesehen_id')
            ->count();

        $teamUngelesen = $ungelesenzahl($teamChat->id);
        $direktChats   = $direktChats->map(function ($chat) use ($ungelesenzahl) {
            $chat->ungelesen = $ungelesenzahl($chat->id);
            return $chat;
        });

        return view('chat.index', compact('teamChat', 'teamUngelesen', 'direktChats', 'benutzer'));
    }

    public function sidebarDaten()
    {
        $orgId  = $this->orgId();
        $userId = auth()->id();

        $direktChats = Chat::where('organisation_id', $orgId)
            ->where('typ', 'direkt')
            ->whereHas('teilnehmer', fn($q) => $q->where('benutzer_id', $userId))
            ->with(['teilnehmer.benutzer'])
            ->withMax('nachrichten', 'id')
            ->get()
            ->map(function ($chat) use ($userId) {
                $anderer = $chat->teilnehmer->first(fn($t) => $t->benutzer_id !== $userId);
                return [
                    'id'     => $chat->id,
                    'name'   => $anderer?->benutzer?->vorname . ' ' . $anderer?->benutzer?->nachname,
                    'initials' => $anderer ? strtoupper(substr($anderer->benutzer->vorname,0,1).substr($anderer->benutzer->nachname,0,1)) : '?',
                    'letzteId' => $chat->nachrichten_max_id ?? 0,
                ];
            })
            ->filter(fn($c) => trim($c['name']));

        return response()->json($direktChats->values());
    }

    public function nachrichten(Chat $chat, Request $request)
    {
        $this->authorizeChat($chat);

        $query = $chat->nachrichten()
            ->whereNull('geloescht_am')
            ->where('created_at', '>=', now()->subDays(14))
            ->with('absender:id,vorname,nachname')
            ->orderBy('created_at');

        if ($request->filled('seit')) {
            $query->where('id', '>', (int) $request->seit);
        }

        $nachrichten = $query->get()->map(fn($n) => [
            'id'            => $n->id,
            'inhalt'        => e($n->inhalt),
            'absender_id'   => $n->absender_id,
            'absender_name' => $n->absender->vorname . ' ' . $n->absender->nachname,
            'ich'           => $n->absender_id === auth()->id(),
            'zeit'          => $n->created_at->format('H:i'),
            'datum'         => $n->created_at->format('d.m.Y'),
            'ts'            => $n->created_at->timestamp,
            'kannLoeschen'  => $n->absender_id === auth()->id() || auth()->user()->rolle === 'admin',
        ]);

        // Letzte gesehene ID aktualisieren
        if ($nachrichten->isNotEmpty()) {
            ChatTeilnehmer::where('chat_id', $chat->id)
                ->where('benutzer_id', auth()->id())
                ->update(['letzte_gesehen_id' => $nachrichten->last()['id']]);
        }

        return response()->json($nachrichten);
    }

    public function store(Request $request, Chat $chat)
    {
        $this->authorizeChat($chat);
        $request->validate(['inhalt' => 'required|string|max:2000']);

        $n = ChatNachricht::create([
            'chat_id'     => $chat->id,
            'absender_id' => auth()->id(),
            'inhalt'      => trim($request->inhalt),
        ]);

        // updated_at des Chats aktualisieren (für Sortierung Direktchats)
        $chat->touch();

        $n->load('absender:id,vorname,nachname');

        return response()->json([
            'id'            => $n->id,
            'inhalt'        => e($n->inhalt),
            'absender_id'   => $n->absender_id,
            'absender_name' => $n->absender->vorname . ' ' . $n->absender->nachname,
            'ich'           => true,
            'zeit'          => $n->created_at->format('H:i'),
            'datum'         => $n->created_at->format('d.m.Y'),
            'ts'            => $n->created_at->timestamp,
            'kannLoeschen'  => true,
        ]);
    }

    public function destroy(Chat $chat, ChatNachricht $chatNachricht)
    {
        $this->authorizeChat($chat);

        if ($chatNachricht->absender_id !== auth()->id() && auth()->user()->rolle !== 'admin') {
            abort(403);
        }

        $chatNachricht->update(['geloescht_am' => now()]);

        return response()->json(['ok' => true]);
    }

    public function startDirekt(Benutzer $benutzer)
    {
        $orgId  = $this->orgId();
        $userId = auth()->id();

        if ($benutzer->id === $userId || $benutzer->organisation_id !== $orgId) {
            abort(422);
        }

        // Bestehenden DM-Chat suchen
        $existing = Chat::where('organisation_id', $orgId)
            ->where('typ', 'direkt')
            ->whereHas('teilnehmer', fn($q) => $q->where('benutzer_id', $userId))
            ->whereHas('teilnehmer', fn($q) => $q->where('benutzer_id', $benutzer->id))
            ->first();

        if ($existing) {
            return redirect()->route('chat.index', ['chat' => $existing->id]);
        }

        $chat = Chat::create(['organisation_id' => $orgId, 'typ' => 'direkt']);
        ChatTeilnehmer::create(['chat_id' => $chat->id, 'benutzer_id' => $userId]);
        ChatTeilnehmer::create(['chat_id' => $chat->id, 'benutzer_id' => $benutzer->id]);

        return redirect()->route('chat.index', ['chat' => $chat->id]);
    }

    private function authorizeChat(Chat $chat): void
    {
        if ($chat->organisation_id !== $this->orgId()) abort(403);
        if ($chat->typ === 'direkt' && !$chat->hatTeilnehmer(auth()->id())) abort(403);
    }
}
