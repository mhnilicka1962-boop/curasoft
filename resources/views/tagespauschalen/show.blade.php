<x-layouts.app :titel="'Tagespauschale'">
<div style="max-width: 620px;">

    <a href="{{ route('klienten.show', $tagespauschale->klient) }}"
       class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">
       ← {{ $tagespauschale->klient->nachname }} {{ $tagespauschale->klient->vorname }}
    </a>

    @if(session('erfolg'))
        <div class="meldung meldung-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
    @endif
    @if(session('fehler'))
        <div class="meldung meldung-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    {{-- Status-Badge --}}
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
        @if($tagespauschale->istBeendet())
            <span class="badge badge-grau">Beendet</span>
        @elseif($tagespauschale->istGeplant())
            <span class="badge badge-warnung">Geplant</span>
        @else
            <span class="badge badge-erfolg">Aktiv</span>
        @endif
        @if($tagespauschale->auto_verlaengern)
            <span class="badge badge-info" style="background: #e0f2fe; color: #0369a1;">Automatisch verlängert</span>
        @endif
    </div>

    {{-- Info bereits verrechnete Einsätze --}}
    @if($letzteVerrechnungDatum)
    <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--cs-radius); padding: 0.625rem 0.875rem; margin-bottom: 1rem; font-size: 0.875rem;">
        <span style="font-weight: 600; color: #15803d;">{{ $tagespauschale->anzahlVerrechnet() }} Tage bereits verrechnet</span>
        <span class="text-hell"> — letzter verrechneter Einsatz: {{ \Carbon\Carbon::parse($letzteVerrechnungDatum)->format('d.m.Y') }}</span>
    </div>
    @endif

    {{-- Edit-Formular --}}
    <div class="karte" style="margin-bottom: 1.25rem;">

        @php $beendet = $tagespauschale->istBeendet(); @endphp

        {{-- Buttons oben --}}
        <div style="display: flex; gap: 0.625rem; flex-wrap: wrap; align-items: center; margin-bottom: 1.25rem;">
            @if(!$beendet)
                <button form="tp-form" type="submit" class="btn btn-primaer">Speichern</button>
            @endif

            @if($tagespauschale->istAktiv() || $tagespauschale->istGeplant())
                <form method="POST" action="{{ route('tagespauschalen.beenden', $tagespauschale) }}"
                    onsubmit="return confirm('Tagespauschale per gestern beenden — unverrechnete zukünftige Einsätze werden gelöscht. Fortfahren?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-gefahr">Beenden</button>
                </form>
            @elseif($beendet)
                <form method="POST" action="{{ route('tagespauschalen.neustart', $tagespauschale) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sekundaer">Neu starten</button>
                </form>
            @endif

            <a href="{{ route('klienten.show', $tagespauschale->klient) }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>

        <form id="tp-form" method="POST" action="{{ route('tagespauschalen.update', $tagespauschale) }}">
            @csrf
            @method('PATCH')

            <fieldset @if($beendet) disabled style="opacity: 0.5;" @endif>

            {{-- Automatisch verlängern --}}
            <div style="margin-bottom: 0.875rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem 1rem;">
                <label style="display: flex; align-items: center; gap: 0.625rem; cursor: pointer; font-size: 0.9375rem; font-weight: 600;">
                    <input type="checkbox" name="auto_verlaengern" id="auto_verlaengern" value="1"
                        {{ old('auto_verlaengern', $tagespauschale->auto_verlaengern ? '1' : '0') == '1' ? 'checked' : '' }}
                        onchange="toggleDatumBis()">
                    Automatisch verlängern
                </label>
                <div id="hint-auto" class="text-hell" style="font-size: 0.8125rem; margin-top: 0.375rem; padding-left: 1.75rem;"></div>
            </div>

            {{-- Zeitraum --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Gültig von *</label>
                    <input type="date" name="datum_von" class="feld"
                        value="{{ old('datum_von', $tagespauschale->datum_von->format('Y-m-d')) }}" required>
                    @error('datum_von') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label" id="label-datum-bis">Gültig bis</label>
                    <input type="date" name="datum_bis" id="datum_bis" class="feld"
                        value="{{ old('datum_bis', $tagespauschale->datum_bis?->format('Y-m-d')) }}">
                    @error('datum_bis') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            <input type="hidden" name="rechnungstyp" value="kvg">

            {{-- Ansatz --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Ansatz (CHF/Tag)</label>
                <input type="number" name="ansatz" id="ansatz" class="feld"
                    step="0.05" min="0"
                    value="{{ old('ansatz', number_format($tagespauschale->ansatz, 2, '.', '')) }}" required>
                @error('ansatz') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            {{-- Text --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Text auf Rechnung</label>
                <input type="text" name="text" class="feld" maxlength="500"
                    value="{{ old('text', $tagespauschale->text) }}">
                @error('text') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            </fieldset>
        </form>
    </div>

    {{-- Monatsübersicht --}}
    @if($einsaetzeStats->isNotEmpty())
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Einsätze nach Monat</div>
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Monat</th>
                    <th class="text-rechts">Tage</th>
                    <th class="text-rechts">Verrechnet</th>
                    <th class="text-rechts">Offen</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($einsaetzeStats as $stat)
                @php $offen = $stat->anzahl - $stat->verrechnet; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $stat->monat)->format('F Y') }}</td>
                    <td class="text-rechts">{{ $stat->anzahl }}</td>
                    <td class="text-rechts" style="color: var(--erfolg, #15803d);">{{ $stat->verrechnet }}</td>
                    <td class="text-rechts">{{ $offen }}</td>
                    <td>
                        @if($offen === 0)
                            <span class="badge badge-erfolg">Verrechnet</span>
                        @elseif($stat->verrechnet > 0)
                            <span class="badge badge-warnung">Teilweise</span>
                        @else
                            <span class="badge badge-grau">Offen</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

@push('scripts')
<script>
function toggleDatumBis() {
    const auto  = document.getElementById('auto_verlaengern').checked;
    const bis   = document.getElementById('datum_bis');
    const label = document.getElementById('label-datum-bis');
    const hint  = document.getElementById('hint-auto');

    bis.disabled = auto;
    bis.style.opacity = auto ? '0.4' : '1';
    if (auto) bis.value = '';
    label.textContent = auto ? 'Gültig bis (nicht benötigt)' : 'Gültig bis *';
    hint.textContent  = auto
        ? 'Aktiv — der nächtliche Batchjob erstellt täglich neue Einsätze bis zum Planungshorizont. Kein manuelles Eingreifen nötig.'
        : 'Inaktiv — Einsätze werden nur bis zum Enddatum generiert. Danach läuft die Tagespauschale aus.';
}

document.getElementById('auto_verlaengern')?.addEventListener('change', toggleDatumBis);
toggleDatumBis();
</script>
@endpush
</x-layouts.app>
