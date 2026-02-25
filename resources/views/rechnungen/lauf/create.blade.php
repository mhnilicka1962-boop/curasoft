<x-layouts.app :titel="'Neuer Rechnungslauf'">

<div class="seiten-kopf" style="margin-bottom: 1.5rem;">
    <div>
        <a href="{{ route('rechnungslauf.index') }}" class="link-primaer" style="font-size: 0.875rem;">← Rechnungsläufe</a>
        <h2 style="margin: 0.25rem 0 0;">Neuer Rechnungslauf</h2>
    </div>
</div>

@if($errors->any())
    <div class="meldung meldung-fehler" style="margin-bottom: 1rem;">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

{{-- Periode-Formular --}}
<div class="karte" style="margin-bottom: 1.5rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Abrechnungsperiode</div>
    <form method="GET" action="{{ route('rechnungslauf.create') }}" id="vorschau-form">
        <div class="form-grid">
            <div class="form-feld">
                <label class="form-label">Periode von</label>
                <input type="date" name="periode_von" class="feld" value="{{ request('periode_von') }}" required>
            </div>
            <div class="form-feld">
                <label class="form-label">Periode bis</label>
                <input type="date" name="periode_bis" class="feld" value="{{ request('periode_bis') }}" required>
            </div>
            <div class="form-feld" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-sekundaer" style="width: 100%;">Vorschau laden</button>
            </div>
        </div>
    </form>
    <div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.625rem;">
        Rechnungstyp und Tarife werden pro Klient aus den Stammdaten übernommen
        (Rechnungstyp unter Klient → Abrechnung, Tarife aus Leistungsarten → Regionen).
    </div>
</div>

{{-- Vorschau --}}
@if($vorschau !== null)
@php
    $typen      = ['kombiniert' => 'Kombi.', 'kvg' => 'KVG', 'klient' => 'Klient', 'gemeinde' => 'Gemeinde'];
    $typenBadge = ['kombiniert' => 'badge-grau', 'kvg' => 'badge-info', 'klient' => 'badge-erfolg', 'gemeinde' => ''];
    // Mit Einsätzen zuerst, ohne danach
    $zeilen = collect($vorschau['zeilen'])->sortBy('ohne_einsaetze');
    $mitEinsaetzen = $zeilen->where('ohne_einsaetze', false);
@endphp

<div class="karte" style="margin-bottom: 1.5rem;">

    {{-- Header + Top-Button --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <h3 style="margin: 0;">
            Vorschau —
            <span id="selected-count">{{ $vorschau['anzahl_mit'] }}</span> Rechnung(en) ausgewählt,
            CHF {{ number_format($vorschau['total_betrag'], 2, '.', "'") }}
        </h3>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if($vorschau['anzahl_ohne'] > 0)
                <span class="badge" style="background:#fef2f2; color:#dc2626; border:1px solid #fca5a5;">
                    {{ $vorschau['anzahl_ohne'] }} ohne Einsätze
                </span>
            @endif
            @if($vorschau['ohne_leistungsart'] > 0)
                <span class="badge badge-warnung" title="Einsätze ohne Leistungsart haben Tarif 0">
                    ⚠ {{ $vorschau['ohne_leistungsart'] }} mit Tarif 0
                </span>
            @endif
            @if($vorschau['anzahl_mit'] > 0)
            <button type="submit" form="lauf-form" class="btn btn-primaer" id="btn-starten-top"
                onclick="return bestaetigeStart()">
                Lauf starten (<span class="selected-count-display">{{ $vorschau['anzahl_mit'] }}</span>)
            </button>
            @endif
        </div>
    </div>

    {{-- Warnungen --}}
    @if(count($vorschau['regionen_ohne_tarife']) > 0)
    <div style="background:#fef3c7; border:2px solid #f59e0b; border-radius:6px; padding:0.875rem 1rem; margin-bottom:1rem; display:flex; gap:0.625rem;">
        <span style="font-size:1.2rem; line-height:1.3; flex-shrink:0;">⚠</span>
        <div style="font-size:0.875rem; color:#78350f;">
            <div style="font-weight:700; margin-bottom:0.25rem;">Kanton nicht konfiguriert — Rechnungen werden CHF 0.00</div>
            <div>Fehlende Tarife in: <strong>{{ implode(', ', $vorschau['regionen_ohne_tarife']) }}</strong></div>
            <div style="margin-top:0.375rem;">→ <a href="{{ route('leistungsarten.index') }}" class="link-primaer" style="color:#92400e; font-weight:600;">Leistungsarten → Kantone konfigurieren</a></div>
        </div>
    </div>
    @endif

    @if($vorschau['ohne_leistungsart'] > 0)
    <div class="meldung meldung-warnung" style="margin-bottom: 1rem; font-size: 0.875rem;">
        {{ $vorschau['ohne_leistungsart'] }} Klient(en) mit Einsätzen ohne Leistungsart — Tarif 0.
        <a href="{{ route('leistungsarten.index') }}" class="link-primaer">Leistungsarten konfigurieren</a>
    </div>
    @endif

    {{-- Tabelle + Starten-Formular --}}
    <form method="POST" action="{{ route('rechnungslauf.store') }}" id="lauf-form">
        @csrf
        <input type="hidden" name="periode_von" value="{{ request('periode_von') }}">
        <input type="hidden" name="periode_bis" value="{{ request('periode_bis') }}">

        <div style="overflow-x: auto;">
            <table class="tabelle" style="font-size: 0.8125rem;">
                <thead>
                    <tr>
                        <th style="width: 36px; text-align: center;">
                            <input type="checkbox" id="alle-waehlen" checked
                                title="Alle auswählen / abwählen"
                                style="cursor:pointer; width:15px; height:15px;">
                        </th>
                        <th>Klient</th>
                        <th>Typ</th>
                        <th class="text-rechts">Einsätze</th>
                        <th class="text-rechts">Minuten</th>
                        <th class="text-rechts">Betrag Pat.</th>
                        <th class="text-rechts">Betrag KK</th>
                        <th class="text-rechts">Total CHF</th>
                        <th>Versand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zeilen as $z)
                    @if(!$z['ohne_einsaetze'])
                    {{-- Klient mit Einsätzen --}}
                    <tr class="klient-zeile" @if($z['ohne_tarif']) style="background:#fffbeb;" @endif>
                        <td style="text-align:center;">
                            <input type="checkbox" name="klienten[]" value="{{ $z['klient']->id }}"
                                checked class="klient-cb"
                                style="cursor:pointer; width:15px; height:15px;">
                        </td>
                        <td>
                            {{ $z['klient']->nachname }} {{ $z['klient']->vorname }}
                            @if($z['ohne_tarif'])<span title="Einsätze ohne Leistungsart/Tarif"> ⚠</span>@endif
                        </td>
                        <td>
                            <span class="badge {{ $typenBadge[$z['rechnungstyp']] ?? 'badge-grau' }}">
                                {{ $typen[$z['rechnungstyp']] ?? $z['rechnungstyp'] }}
                            </span>
                        </td>
                        <td class="text-rechts">{{ $z['anzahl'] }}</td>
                        <td class="text-rechts">{{ $z['minuten'] }}'</td>
                        <td class="text-rechts">{{ number_format($z['betrag_patient'], 2, '.', "'") }}</td>
                        <td class="text-rechts">{{ number_format($z['betrag_kk'], 2, '.', "'") }}</td>
                        <td class="text-rechts text-fett">{{ number_format($z['betrag'], 2, '.', "'") }}</td>
                        <td>
                            @php
                                $va      = $z['versandart'];
                                $vaLabel = match($va) { 'email' => 'Email', 'manuell' => 'Manuell', default => 'Post' };
                                $vaBadge = match($va) { 'email' => 'badge-info', 'manuell' => 'badge-warnung', default => 'badge-grau' };
                            @endphp
                            <span class="badge {{ $vaBadge }}">{{ $vaLabel }}</span>
                        </td>
                    </tr>
                    @else
                    {{-- Klient ohne Einsätze — rot, nicht selektierbar --}}
                    <tr style="background:#fef2f2;">
                        <td style="text-align:center; color:#dc2626; font-weight:700;">—</td>
                        <td>
                            <a href="{{ route('klienten.show', $z['klient']) }}"
                               style="color:#dc2626; font-weight:600; text-decoration:none;"
                               title="Klient öffnen und Problem beheben">
                                {{ $z['klient']->nachname }} {{ $z['klient']->vorname }}
                            </a>
                            <div style="font-size:0.75rem; color:#b91c1c; margin-top:0.15rem;">
                                {{ $z['grund'] }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $typenBadge[$z['rechnungstyp']] ?? 'badge-grau' }}" style="opacity:0.6;">
                                {{ $typen[$z['rechnungstyp']] ?? $z['rechnungstyp'] }}
                            </span>
                        </td>
                        <td class="text-rechts" style="color:#dc2626;">0</td>
                        <td class="text-rechts text-hell">—</td>
                        <td class="text-rechts text-hell">—</td>
                        <td class="text-rechts text-hell">—</td>
                        <td class="text-rechts text-hell">—</td>
                        <td>
                            @php
                                $va      = $z['versandart'];
                                $vaLabel = match($va) { 'email' => 'Email', 'manuell' => 'Manuell', default => 'Post' };
                                $vaBadge = match($va) { 'email' => 'badge-info', 'manuell' => 'badge-warnung', default => 'badge-grau' };
                            @endphp
                            <span class="badge {{ $vaBadge }}" style="opacity:0.6;">{{ $vaLabel }}</span>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: var(--hintergrund-alt, #f8f9fa);">
                        <td></td>
                        <td colspan="4">Total ausgewählt</td>
                        <td class="text-rechts">{{ number_format($mitEinsaetzen->sum('betrag_patient'), 2, '.', "'") }}</td>
                        <td class="text-rechts">{{ number_format($mitEinsaetzen->sum('betrag_kk'), 2, '.', "'") }}</td>
                        <td class="text-rechts text-fett">CHF {{ number_format($vorschau['total_betrag'], 2, '.', "'") }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Starten-Button unten --}}
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-top: 1.25rem;">
            <button type="submit" class="btn btn-primaer" onclick="return bestaetigeStart()">
                Rechnungslauf starten (<span class="selected-count-display">{{ $vorschau['anzahl_mit'] }}</span> Rechnungen)
            </button>
            <span class="text-hell" style="font-size: 0.8125rem;">
                Ausgewählte Einsätze werden als «verrechnet» markiert.
            </span>
        </div>
    </form>
</div>

@else
<div class="karte" style="margin-bottom: 1.5rem;">
    <div class="text-hell text-mitte" style="padding: 2rem;">
        Keine verrechenbaren Einsätze in dieser Periode gefunden.
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alleCheckbox  = document.getElementById('alle-waehlen');
    const klientCbs     = () => document.querySelectorAll('.klient-cb');
    const countDisplays = document.querySelectorAll('.selected-count-display');
    const selectedCount = document.getElementById('selected-count');

    function aktualisiereAnzahl() {
        const n = document.querySelectorAll('.klient-cb:checked').length;
        countDisplays.forEach(el => el.textContent = n);
        if (selectedCount) selectedCount.textContent = n;
        // "Alle"-Checkbox: checked wenn alle, indeterminate wenn teilweise
        const alle = klientCbs();
        const checkedCount = document.querySelectorAll('.klient-cb:checked').length;
        alleCheckbox.checked       = checkedCount === alle.length;
        alleCheckbox.indeterminate = checkedCount > 0 && checkedCount < alle.length;
    }

    if (alleCheckbox) {
        alleCheckbox.addEventListener('change', function () {
            klientCbs().forEach(cb => cb.checked = this.checked);
            aktualisiereAnzahl();
        });
    }

    klientCbs().forEach(cb => cb.addEventListener('change', aktualisiereAnzahl));
});

function bestaetigeStart() {
    const n = document.querySelectorAll('.klient-cb:checked').length;
    if (n === 0) {
        alert('Keine Klienten ausgewählt.');
        return false;
    }
    return confirm(n + ' Rechnung(en) jetzt erstellen?\n\nEinsätze werden als «verrechnet» markiert.');
}
</script>

</x-layouts.app>
