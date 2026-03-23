<x-layouts.app titel="Ferienvertretung — Vorschau">
<div style="max-width: 800px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Vertretung — Vorschau</h1>
            <div class="text-hell text-klein" style="margin-top: 0.25rem;">
                {{ $benutzer->vorname }} {{ $benutzer->nachname }}
                &nbsp;·&nbsp;
                {{ \Carbon\Carbon::parse($daten['datum_von'])->format('d.m.Y') }}
                –
                {{ \Carbon\Carbon::parse($daten['datum_bis'])->format('d.m.Y') }}
                @if($vertretung)
                    &nbsp;·&nbsp; Vertretung: <strong>{{ $vertretung->vorname }} {{ $vertretung->nachname }}</strong>
                @endif
            </div>
        </div>
        <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">← Zurück</a>
    </div>

    @if($einsaetzeOk->isEmpty() && $einsaetzeWarnung->isEmpty())
    <div class="karte">
        <div class="text-hell" style="text-align: center; padding: 2rem 0;">
            Keine geplanten Einsätze in diesem Zeitraum.
        </div>
    </div>
    @else

    @if($einsaetzeWarnung->isNotEmpty())
    <div class="warn-box" style="margin-bottom: 1.25rem;">
        <strong>⚠ {{ $einsaetzeWarnung->count() }} Einsatz/Einsätze können nicht übertragen werden</strong> —
        {{ $vertretung?->vorname }} {{ $vertretung?->nachname }} ist für diese Leistungsart(en) nicht freigegeben.
        Diese Einsätze müssen manuell zugewiesen werden.
        <div class="tabelle-wrapper" style="margin-top: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Datum</th><th>Klient</th><th>Leistungsart</th><th>Zeit</th></tr></thead>
            <tbody>
                @foreach($einsaetzeWarnung as $e)
                <tr>
                    <td>{{ $e->datum->format('d.m.Y') }}</td>
                    <td>{{ $e->klient?->vollname() ?? '—' }}</td>
                    <td>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</td>
                    <td>{{ $e->zeit_von ? substr($e->zeit_von,0,5) : '—' }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    @if($einsaetzeOk->isNotEmpty())
    <form action="{{ route('vertretung.ausfuehren') }}" method="POST">
        @csrf
        <input type="hidden" name="vertretung_id" value="{{ $vertretung?->id ?? '' }}">

        <div class="karte" style="margin-bottom: 1.25rem;">
            <div class="abschnitt-label" style="margin-bottom: 0.75rem;">
                {{ $einsaetzeOk->count() }} Einsätze übertragen
                @if(!$vertretung)
                    <span class="badge badge-warnung" style="margin-left: 0.5rem;">Vertretung noch nicht gewählt</span>
                @endif
            </div>

            @if(!$vertretung)
            <div style="margin-bottom: 1rem;">
                <label class="feld-label">Vertretung wählen</label>
                <select name="vertretung_id" class="feld" style="max-width: 320px;" required>
                    <option value="">— wählen —</option>
                    @foreach($mitarbeiter as $m)
                        <option value="{{ $m->id }}">{{ $m->nachname }} {{ $m->vorname }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="margin-bottom: 0.75rem; display: flex; gap: 0.75rem; align-items: center;">
                <label style="font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                    <input type="checkbox" id="alle-waehlen" style="margin-right: 0.3rem;" checked>
                    Alle auswählen / abwählen
                </label>
                <span class="text-hell text-klein" id="anzahl-gewaehlt"></span>
            </div>

            <div class="tabelle-wrapper">
            <table class="tabelle">
                <thead>
                    <tr>
                        <th style="width: 2rem;"></th>
                        <th>Datum</th>
                        <th>Klient</th>
                        <th>Leistungsart</th>
                        <th>Zeit</th>
                        <th>Tour</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($einsaetzeOk as $e)
                    <tr>
                        <td>
                            <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}"
                                class="einsatz-cb" checked style="cursor: pointer;">
                        </td>
                        <td>{{ $e->datum->format('d.m.Y') }}</td>
                        <td>{{ $e->klient?->vollname() ?? '—' }}</td>
                        <td>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</td>
                        <td>{{ $e->zeit_von ? substr($e->zeit_von,0,5) : '—' }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</td>
                        <td class="text-hell text-klein">{{ $e->tour?->bezeichnung ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="submit" class="btn btn-primaer" id="btn-ausfuehren">
                Einsätze übertragen →
            </button>
            <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>
    @endif

    @endif

</div>

@push('scripts')
<script>
const alleBox  = document.getElementById('alle-waehlen');
const cbs      = document.querySelectorAll('.einsatz-cb');
const anzeige  = document.getElementById('anzahl-gewaehlt');
const btnAusf  = document.getElementById('btn-ausfuehren');

function aktualisiereAnzeige() {
    const n = [...cbs].filter(c => c.checked).length;
    anzeige.textContent = n + ' von ' + cbs.length + ' gewählt';
    if (btnAusf) btnAusf.textContent = n + ' Einsatz' + (n !== 1 ? 'ätze' : '') + ' übertragen →';
}

alleBox?.addEventListener('change', () => {
    cbs.forEach(c => c.checked = alleBox.checked);
    aktualisiereAnzeige();
});
cbs.forEach(c => c.addEventListener('change', aktualisiereAnzeige));
aktualisiereAnzeige();
</script>
@endpush
</x-layouts.app>
