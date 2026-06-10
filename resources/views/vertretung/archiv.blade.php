<x-layouts.app titel="Vertretung — Archiv">
<div style="max-width: 900px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Vertretung — Archiv</h1>
        <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">← Aktuelle Vertretungen</a>
    </div>

    <form method="GET" action="{{ route('vertretung.archiv') }}" style="margin-bottom: 1.25rem; display: flex; gap: 0.5rem; align-items: center;">
        <input type="text" name="q" value="{{ $suche }}" placeholder="Nach Mitarbeiter oder Klient suchen …"
               class="feld" style="max-width: 320px; margin: 0;"
               autofocus>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if($suche !== '')
            <a href="{{ route('vertretung.archiv') }}" class="btn btn-sekundaer">✕ Zurücksetzen</a>
        @endif
    </form>

    @if($abwesenheiten->isEmpty())
    <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">
        {{ $suche !== '' ? 'Keine Einträge für «' . $suche . '» gefunden.' : 'Keine abgeschlossenen Vertretungen vorhanden.' }}
    </div>
    @else
    <table class="tabelle">
        <thead>
            <tr>
                <th>Abwesend</th>
                <th>Zeitraum</th>
                <th>Vertretung durch</th>
                <th>Klienten</th>
                <th style="text-align: right;">Einsätze</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($abwesenheiten as $abw)
            <tr>
                <td style="font-weight: 600;">{{ $abw->benutzer->vorname }} {{ $abw->benutzer->nachname }}</td>
                <td class="text-klein" style="white-space: nowrap;">{{ $abw->datum_von->format('d.m.Y') }} – {{ $abw->datum_bis->format('d.m.Y') }}</td>
                <td>{{ $abw->vertretung ? $abw->vertretung->vorname . ' ' . $abw->vertretung->nachname : '—' }}</td>
                <td class="text-klein">
                    @if($abw->klienten->isNotEmpty())
                        {{ $abw->klienten->implode(', ') }}
                    @else
                        <span class="text-hell">—</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    @if($abw->anzahl_einsaetze > 0)
                        <span class="badge">{{ $abw->anzahl_einsaetze }}</span>
                    @else
                        <span class="text-hell">—</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('vertretung.vorschau.get', [
                        'benutzer_id' => $abw->benutzer_id,
                        'datum_von'   => $abw->datum_von->format('Y-m-d'),
                        'datum_bis'   => $abw->datum_bis->format('Y-m-d'),
                        'mit_vergangenheit' => 1,
                    ]) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">
                        Detail →
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

</div>
</x-layouts.app>
