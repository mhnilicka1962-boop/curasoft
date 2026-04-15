# SECURITY.md — Curasoft / Spitex

Stand: 2026-04-06

---

## HTTP Observatory Score (Mozilla)

**B+ / 80 von 100** — [https://observatory.mozilla.org](https://observatory.mozilla.org)

| Test | Status | Bemerkung |
|---|---|---|
| Content Security Policy (CSP) | **FAIL −20** | `unsafe-inline` in script-src (siehe unten) |
| Cookies | Pass | Secure + HttpOnly + SameSite gesetzt |
| CORS | Pass | nicht exponiert |
| HTTP → HTTPS Redirect | Pass | korrekt |
| Referrer-Policy | Pass | `strict-origin-when-cross-origin` |
| HSTS | Pass | 1 Jahr + includeSubDomains + preload |
| X-Content-Type-Options | Pass | `nosniff` |
| X-Frame-Options | Pass | via `frame-ancestors 'none'` |

Bonus (kein Abzug, aber kein Zusatzpunkt):
- Subresource Integrity (SRI) nicht gesetzt
- Cross-Origin Resource Policy (CORP) nicht gesetzt

---

## CSP — `unsafe-inline` in script-src

### Warum ist es drin?

Die App verwendet 39 Inline-`<script>`-Blöcke in 34 Blade-Templates (z.B. Alpine.js-Init, Kalender, Karten-Logik). Der Browser kann ohne Nonce nicht unterscheiden ob ein Script vom Entwickler stammt oder von einem Angreifer injiziert wurde — deshalb markiert der Observatory-Scanner `unsafe-inline` als unsicher.

### Warum ist es kein akutes Risiko?

1. **Laravel escaped automatisch:** `{{ $variable }}` produziert HTML-Entities — kein Script-Injection möglich
2. **CSRF-Schutz** auf allen Formularen
3. **Kein öffentlicher Kommentarbereich** — alle Inputs kommen von eingeloggten Mitarbeitern

### `{!! !!}` Audit (2026-04-06)

Alle Stellen mit unescapierter Blade-Ausgabe geprüft:

| Datei | Variable | Risiko |
|---|---|---|
| `klienten/show.blade.php` | `$w['text']` | Kein Risiko — Text ist hardcoded im PHP-Code, nie aus DB/User-Input |
| `klienten/show.blade.php` | `$r->typBadge()` / `$r->statusBadge()` | Kein Risiko — `match()`-Statement mit hardcodierten Strings |
| `rechnungen/index.blade.php` | `$r->typBadge()` / `$r->statusBadge()` | Kein Risiko — siehe oben |
| `rechnungen/lauf/show.blade.php` | `$r->statusBadge()` | Kein Risiko — siehe oben |
| `rechnungen/show.blade.php` | `$rechnung->typBadge()` / `->statusBadge()` | Kein Risiko — siehe oben |
| `pdfs/rapportblatt.blade.php` | `$org->postfach` | Minimal — nur Admin kann Firmendaten ändern |

**Befund: kein einziges `{!! !!}` gibt User-Input ungefiltert aus.**

---

## Was für Score A (100/100) nötig wäre

Nonce-basierte CSP einführen:

1. `SecurityHeaders.php` — Nonce pro Request generieren, in CSP einsetzen: `script-src 'self' 'nonce-{xyz}'`
2. Alle 39 `<script>`-Tags in Blade-Templates: `<script nonce="{{ $cspNonce }}">`
3. Alpine.js: CSP-kompatiblen Build `@alpinejs/csp` verwenden (evaluiert Expressions ohne `new Function()`)

Aufwand: ca. 1–2h. Aktuell nicht prioritär, da kein realer Angriffsvektor identifiziert.

---

## Security-Header Implementierung

Datei: `app/Http/Middleware/SecurityHeaders.php`

Gesetzte Header:
- `Content-Security-Policy` (siehe oben)
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (nur HTTPS)
