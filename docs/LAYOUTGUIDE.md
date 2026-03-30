# CURASOFT — Layout-Guide

Referenz für alle neuen Seiten. Zwei Vorlagen:
- **Übersicht** → `/klienten` (Tabelle)
- **Kachel-Liste** → `/dashboard` (listen-zeile)

---

## Seitenstruktur

```blade
<x-layouts.app titel="Seitentitel">
<div style="max-width: 1000px;">

    {{-- 1. Seiten-Kopf --}}
    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Titel</h1>
        <a href="..." class="btn btn-primaer">+ Neuer Eintrag</a>
    </div>

    {{-- 2. Inhalt --}}

</div>
</x-layouts.app>
```

**Regeln:**
- Alerts NICHT selbst ausgeben — Layout (`alerts.blade.php`) macht das automatisch
- `max-width`: Listen = 1000px, Formulare = 720px, Tour/Detail = 960px
- Kein `<h1>` ohne `seiten-kopf`

---

## Buttons — Wann was

| Klasse | Verwendung |
|--------|-----------|
| `btn-primaer` | Hauptaktion: Speichern, + Neu, Erstellen |
| `btn-sekundaer` | Sekundär: Bearbeiten, Abbrechen, Filter |
| `btn-gefahr` | Löschen/Stornieren — immer mit `onsubmit="return confirm('...')"` |

**Anordnung:**
- Primäre Aktion immer **oben rechts** im `seiten-kopf`
- In Formularen: Speichern links, Löschen rechts (mit `flex: 1` Abstand dazwischen)
- Kleine Buttons: `style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;"`
- Niemals `btn-sekundaer` für Speichern oder Hauptaktionen

```blade
{{-- Buttons in Formular-Fusszeile --}}
<div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 1rem;">
    <button type="submit" class="btn btn-primaer">Speichern</button>
    <a href="{{ route('...') }}" class="btn btn-sekundaer">Abbrechen</a>
    <span style="flex: 1;"></span>
    {{-- Löschen ganz rechts --}}
    <button type="submit" form="loeschen-form" class="btn btn-gefahr">Löschen</button>
</div>
```

---

## Übersicht mit Tabelle (→ Klienten-Referenz)

```blade
{{-- Filter-Zeile --}}
<div class="seiten-kopf">
    <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex: 1;">
        <input type="text" name="suche" class="feld" style="max-width: 260px;" placeholder="Suchen…" value="{{ request('suche') }}">
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if(request('suche'))
            <a href="{{ route('...') }}" class="btn btn-sekundaer">✕</a>
        @endif
    </form>
    <a href="{{ route('....create') }}" class="btn btn-primaer">+ Neu</a>
</div>

{{-- Tabelle --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Name</th>
                <th class="col-desktop">Detail</th>
                <th></th>  {{-- Aktionen-Spalte --}}
            </tr>
        </thead>
        <tbody>
            @forelse($eintraege as $e)
            <tr>
                <td><a href="..." class="link-primaer text-mittel">{{ $e->name }}</a></td>
                <td class="col-desktop text-hell" style="font-size: 0.8125rem;">{{ $e->detail }}</td>
                <td class="text-rechts">
                    <a href="{{ route('....edit', $e) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">Bearbeiten</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-mitte text-hell" style="padding: 2.5rem;">Keine Einträge.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
```

---

## Kachel mit Listen-Zeilen (→ Dashboard-Referenz)

```blade
<div class="karte" style="margin-bottom: 1rem;">
    {{-- Kachel-Kopf --}}
    <div class="karten-kopf">
        <div class="abschnitt-label">Abschnittstitel</div>
        <a href="..." class="text-klein link-primaer">Alle →</a>
    </div>

    {{-- Zeilen --}}
    @foreach($eintraege as $e)
    <div class="listen-zeile">
        <div class="listen-zeile-inner" style="flex-wrap: wrap;">
            {{-- Links: Hauptinfo --}}
            <div class="flex-1-min">
                <a href="..." class="text-fett link-primaer">{{ $e->name }}</a>
                <span class="badge badge-klein ml-klein {{ $e->badgeKlasse() }}">{{ $e->label() }}</span>
                <div class="text-hell listen-meta">Untertitel / Leistungsarten</div>
            </div>
            {{-- Rechts: Meta + Aktion --}}
            <div class="text-mini text-hell text-rechts flex-shrink-0">
                <div>{{ $e->zeit }}</div>
                <a href="..." class="badge badge-klein badge-grau" style="text-decoration: none;">Detail →</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
```

---

## Kachel mit Formular (Bearbeiten)

```blade
<div class="karte" style="margin-bottom: 1rem;">
    <form method="POST" action="{{ route('....update', $obj) }}">
        @csrf @method('PUT')

        <div class="form-grid" style="margin-bottom: 0.875rem;">
            <div>
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld" value="{{ $obj->bezeichnung }}" required>
            </div>
            {{-- weitere Felder --}}
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <button type="submit" class="btn btn-primaer">Speichern</button>
            <span style="flex: 1;"></span>
            <button type="submit" form="loeschen-form" class="btn btn-gefahr">Löschen</button>
        </div>
    </form>

    {{-- Löschen-Formular versteckt --}}
    <form method="POST" action="{{ route('....destroy', $obj) }}" id="loeschen-form"
          onsubmit="return confirm('Wirklich löschen?')">
        @csrf @method('DELETE')
    </form>
</div>
```

---

## Badges

```blade
<span class="badge badge-erfolg">Aktiv</span>
<span class="badge badge-warnung">Ausstehend</span>
<span class="badge badge-fehler">Fehler</span>
<span class="badge badge-grau">Inaktiv</span>
<span class="badge badge-info">Info</span>
```

Inline in Texten: `badge-klein ml-klein` zusätzlich.

---

## Formular-Grids

```blade
{{-- Auto-Grid (empfohlen für Formulare) --}}
<div class="form-grid"> ... </div>

{{-- Festes 2-Spalten --}}
<div class="form-grid-2"> ... </div>

{{-- Manuelles Grid (wenn Spaltenbreiten unterschiedlich) --}}
<div style="display: grid; grid-template-columns: 2fr 1fr 120px; gap: 0.75rem; align-items: end;">
```

---

## Leer-Zustand

```blade
{{-- In Tabelle --}}
<tr><td colspan="5" class="text-mitte text-hell" style="padding: 2.5rem;">Keine Einträge gefunden.</td></tr>

{{-- In Kachel --}}
<p class="text-klein text-hell m-05">Keine Einträge.</p>

{{-- Grosse leere Kachel --}}
<div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">Keine Daten.</div>
```

---

## CSS-Klassen Kurzreferenz

| Klasse | Bedeutung |
|--------|-----------|
| `.seiten-kopf` | Flex-Zeile: Titel links, Button rechts |
| `.karte` | Weisse Kachel mit Padding (1.25rem) |
| `.karte-null` | Kachel ohne Padding (für Tabellen) |
| `.karten-kopf` | Flex-Zeile innerhalb Kachel: Label + Link |
| `.listen-zeile` | Zeile in Kachel-Liste (hover, border-bottom) |
| `.listen-zeile-inner` | Flex-Container in listen-zeile |
| `.tabelle` | Standard-Tabelle |
| `.feld` | Input, Select, Textarea |
| `.feld-label` | Label über Feld |
| `.form-grid` | Auto-Grid für Formularfelder |
| `.abschnitt-label` | Grau, klein, uppercase — Abschnittstitel |
| `.detail-raster` | 2-spaltig für Detailansicht (Label + Wert) |
| `.text-hell` | Grauer Text |
| `.text-fett` | font-weight: 600 |
| `.text-klein` | 0.875rem |
| `.col-desktop` | Auf Mobile ausgeblendet |
| `.link-primaer` | Blauer Link ohne Unterstrich |
| `.link-gedaempt` | Grauer Link (Breadcrumb) |
