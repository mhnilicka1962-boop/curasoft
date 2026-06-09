<x-layouts.app titel="Vertretung — Vorschau">
<div style="max-width: 900px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Vertretung — Vorschau</h1>
            <div class="text-hell text-klein" style="margin-top: 0.25rem;">
                {{ $benutzer->vorname }} {{ $benutzer->nachname }}
                &nbsp;·&nbsp;
                {{ \Carbon\Carbon::parse($daten['datum_von'])->format('d.m.Y') }}
                –
                {{ \Carbon\Carbon::parse($daten['datum_bis'])->format('d.m.Y') }}
            </div>
        </div>
        <div style="display:flex; gap:0.5rem; align-items:center;">
            @php
                $toggleParams = array_merge($daten, ['mit_vergangenheit' => $mitVergangenheit ? 0 : 1]);
            @endphp
            <a href="{{ route('vertretung.vorschau.get', $toggleParams) }}"
               class="btn btn-sekundaer" style="font-size:0.8125rem;">
                {{ $mitVergangenheit ? 'Vergangene ausblenden' : 'Vergangene einblenden' }}
            </a>
            <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">← Zurück</a>
        </div>
    </div>

    @if($alleEinsaetze->isEmpty() && $einsaetzeWarnung->isEmpty())
    <div class="karte">
        <div class="text-hell" style="text-align: center; padding: 2rem 0;">
            Keine geplanten Einsätze in diesem Zeitraum.
        </div>
    </div>
    @else

    {{-- Qualifikations-Warnung --}}
    @if($einsaetzeWarnung->isNotEmpty())
    <div class="warn-box" style="margin-bottom: 1.25rem;">
        <strong>⚠ {{ $einsaetzeWarnung->count() }} Einsatz/Einsätze können nicht übertragen werden</strong> —
        Qualifikation nicht ausreichend. Bitte manuell zuweisen.
        <div class="tabelle-wrapper" style="margin-top: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Datum</th><th>Klient</th><th>Leistungsart</th><th>Zeit</th></tr></thead>
            <tbody>
                @foreach($einsaetzeWarnung as $e)
                <tr>
                    <td>{{ $e->datum->format('d.m.Y') }}</td>
                    <td>{{ $e->klient?->vollname() ?? '—' }}</td>
                    <td>{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') ?: '—' }}</td>
                    <td>{{ $e->zeit_von ? substr($e->zeit_von,0,5) : '—' }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    @if($alleEinsaetze->isNotEmpty())
    <form action="{{ route('vertretung.ausfuehren') }}" method="POST">
        @csrf
        <input type="hidden" name="benutzer_id" value="{{ $daten['benutzer_id'] }}">
        <input type="hidden" name="datum_von"   value="{{ $daten['datum_von'] }}">
        <input type="hidden" name="datum_bis"   value="{{ $daten['datum_bis'] }}">

        @if(!empty($konflikte))
        <div class="warn-box" style="margin-bottom: 1rem;">
            <strong>⚠ Zeitkonflikte vorhanden</strong> — bitte betroffene Tour danach prüfen.
        </div>
        @endif

        <div class="karte">
            {{-- Globales Dropdown + Alle-Checkbox --}}
            <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; padding-bottom: 0.75rem; border-bottom: 1px solid var(--cs-border);">
                <label style="font-size: 0.875rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                    <input type="checkbox" id="alle-waehlen" style="margin-right: 0.3rem;" checked>
                    Alle auswählen
                </label>
                <span class="text-hell text-klein" id="anzahl-gewaehlt"></span>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                    <label class="feld-label" style="margin: 0; white-space: nowrap;">Für alle:</label>
                    <select id="vertretung-alle" class="feld" style="min-width: 180px;">
                        <option value="">— wählen —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ $vertretung?->id == $m->id ? 'selected' : '' }}>
                                {{ $m->nachname }} {{ $m->vorname }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="tabelle-wrapper">
            <table class="tabelle">
                <thead>
                    <tr>
                        <th style="width: 2rem;"></th>
                        <th style="width: 1.5rem;"></th>
                        <th>Datum</th>
                        <th>Klient</th>
                        <th>Zeit</th>
                        <th>Vertretung durch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alleEinsaetze as $e)
                    <tr>
                        <td>
                            <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}"
                                class="einsatz-cb" {{ $e->bereits_uebertragen ? '' : 'checked' }} style="cursor: pointer;">
                        </td>
                        <td title="{{ $e->bereits_uebertragen ? 'Bereits übertragen' : 'Noch offen' }}">
                            {{ $e->bereits_uebertragen ? '🟢' : '🔴' }}
                        </td>
                        <td>{{ $e->datum->format('d.m.Y') }}</td>
                        <td>{{ $e->klient?->vollname() ?? '—' }}</td>
                        <td style="white-space: nowrap;">
                            {{ $e->zeit_von ? substr($e->zeit_von,0,5) : '—' }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}
                            @if(!empty($konflikte[$e->id]))
                                <br><span class="badge badge-warnung" style="font-size: 0.7rem;">⚠ {{ $konflikte[$e->id] }}</span>
                            @endif
                        </td>
                        <td>
                            <select name="vertretung_ids[{{ $e->id }}]" class="feld vertretung-select" style="min-width: 160px;" {{ $e->bereits_uebertragen ? '' : 'required' }}>
                                <option value="">— wählen —</option>
                                @foreach($mitarbeiter as $m)
                                    @php
                                        $selected = $e->bereits_uebertragen
                                            ? $e->benutzer_id == $m->id
                                            : ($vertretung?->id == $m->id);
                                    @endphp
                                    <option value="{{ $m->id }}" {{ $selected ? 'selected' : '' }}>
                                        {{ $m->nachname }} {{ $m->vorname }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1rem;">
                <button type="submit" class="btn btn-primaer" id="btn-ausfuehren">
                    Einsätze übertragen →
                </button>
                <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </div>
    </form>
    @endif

    @endif

</div>

@push('scripts')
<script>
const alleBox        = document.getElementById('alle-waehlen');
const cbs            = document.querySelectorAll('.einsatz-cb');
const anzeige        = document.getElementById('anzahl-gewaehlt');
const btn            = document.getElementById('btn-ausfuehren');
const vertretungAlle = document.getElementById('vertretung-alle');

function aktualisieren() {
    const n = [...cbs].filter(c => c.checked).length;
    anzeige.textContent = n + ' von ' + cbs.length + ' gewählt';
    if (btn) btn.textContent = n + ' Einsatz' + (n !== 1 ? 'ätze' : '') + ' übertragen →';
}

alleBox?.addEventListener('change', () => { cbs.forEach(c => c.checked = alleBox.checked); aktualisieren(); });
cbs.forEach(c => c.addEventListener('change', aktualisieren));
vertretungAlle?.addEventListener('change', () => {
    document.querySelectorAll('.vertretung-select').forEach(s => s.value = vertretungAlle.value);
});
aktualisieren();
</script>
@endpush
</x-layouts.app>
