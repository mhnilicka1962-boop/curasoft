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
                <input type="date" name="periode_von" class="feld" value="{{ request('periode_von') }}"
                    max="{{ today()->format('Y-m-d') }}" required>
            </div>
            <div class="form-feld">
                <label class="form-label">Periode bis</label>
                <input type="date" name="periode_bis" class="feld" value="{{ request('periode_bis') }}"
                    max="{{ today()->format('Y-m-d') }}" required>
            </div>
            <div class="form-feld" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-sekundaer" style="flex: 1;">Vorschau laden</button>
                @if($vorschau !== null && $vorschau['anzahl_mit'] > 0)
                <button type="submit" form="lauf-form" class="btn btn-primaer" style="flex: 1;" onclick="return bestaetigeStart()">
                    Rechnungslauf starten (<span class="selected-count-display">{{ $vorschau['anzahl_mit'] }}</span>)
                </button>
                @endif
            </div>
        </div>
    </form>
    <div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.625rem;">
        Rechnungstyp und Tarife werden pro Klient aus den Stammdaten übernommen.
        Der Rechnungslauf kann jederzeit storniert werden, solange keine Rechnung als «Gesendet» markiert wurde — alle Einsätze werden dabei zurückgesetzt.
    </div>
    @if(request('periode_bis') && request('periode_bis') > today()->format('Y-m-d'))
    <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:var(--cs-radius); padding:0.5rem 0.75rem; margin-top:0.625rem; font-size:0.875rem; color:#dc2626; font-weight:600;">
        Periode bis liegt in der Zukunft — Rechnungsläufe dürfen nur bis heute ({{ today()->format('d.m.Y') }}) erstellt werden.
    </div>
    @endif
</div>

{{-- Vorschau --}}
@if($vorschau !== null)
@php
    $typen      = ['kombiniert' => 'Kombi.', 'kvg' => 'KVG', 'klient' => 'Klient', 'gemeinde' => 'Gemeinde'];
    $typenBadge = ['kombiniert' => 'badge-grau', 'kvg' => 'badge-info', 'klient' => 'badge-erfolg', 'gemeinde' => ''];
    $zeilen = collect($vorschau['zeilen'])->sortBy('ohne_einsaetze');
    $mitEinsaetzen = $zeilen->where('ohne_einsaetze', false);
@endphp

<div class="karte" style="margin-bottom: 1.5rem;">

    {{-- Header --}}
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

    {{-- Suchfeld --}}
    @if($zeilen->count() > 5)
    <div style="margin-bottom: 0.75rem;">
        <input type="text" id="klient-suche" class="feld" placeholder="Klient suchen…" style="max-width: 280px; font-size: 0.875rem;">
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
                        <th class="text-mitte">Typ</th>
                        <th class="text-mitte">Einsätze</th>
                        <th class="text-mitte">Minuten</th>
                        <th class="text-mitte">Betrag Pat.</th>
                        <th class="text-mitte">Betrag KK</th>
                        <th class="text-mitte">Total CHF</th>
                        <th class="text-mitte">Versand</th>
                    </tr>
                </thead>
                <tbody id="klient-tbody">
                    @foreach($zeilen as $z)
                    @if(!$z['ohne_einsaetze'])
                    {{-- Klient mit Einsätzen --}}
                    <tr class="klient-zeile" data-name="{{ strtolower($z['klient']->nachname . ' ' . $z['klient']->vorname) }}"
                        @if($z['ohne_tarif']) style="background:#fffbeb;" @endif>
                        <td style="text-align:center;">
                            <input type="checkbox" name="klienten[]" value="{{ $z['klient']->id }}"
                                checked class="klient-cb"
                                style="cursor:pointer; width:15px; height:15px;">
                        </td>
                        <td>
                            <a href="{{ route('klienten.show', $z['klient']) }}" class="link-primaer" style="font-weight:600;">
                                {{ $z['klient']->nachname }} {{ $z['klient']->vorname }}
                            </a>
                            @if($z['label'] ?? null)
                                <span class="badge badge-info" style="font-size:0.7rem; margin-left:0.25rem;">{{ $z['label'] }}</span>
                            @endif
                            @if($z['ohne_tarif'])<span title="Einsätze ohne Leistungsart/Tarif"> ⚠</span>@endif
                            <div style="margin-top:0.25rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                                <a href="{{ route('rechnungslauf.vorschau-pdf', ['klient_id' => $z['klient']->id, 'periode_von' => request('periode_von'), 'periode_bis' => request('periode_bis'), 'pauschale' => ($z['label'] === 'Pauschale' ? 1 : 0)]) }}"
                                   class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.15rem 0.5rem;" target="_blank">
                                    📄 PDF Vorschau
                                </a>
                                <a href="{{ route('klienten.show', $z['klient']) . '?back=' . urlencode(request()->fullUrl()) }}"
                                   class="link-gedaempt" style="font-size:0.75rem; line-height:2;">
                                    Klient →
                                </a>
                            </div>
                        </td>
                        <td class="text-mitte">
                            <span class="badge {{ $typenBadge[$z['rechnungstyp']] ?? 'badge-grau' }}">
                                {{ $typen[$z['rechnungstyp']] ?? $z['rechnungstyp'] }}
                            </span>
                        </td>
                        <td class="text-mitte">{{ $z['anzahl'] }}</td>
                        <td class="text-mitte">{{ $z['minuten'] }}'</td>
                        <td class="text-mitte">{{ number_format($z['betrag_patient'], 2, '.', "'") }}</td>
                        <td class="text-mitte">{{ number_format($z['betrag_kk'], 2, '.', "'") }}</td>
                        <td class="text-mitte text-fett">{{ number_format($z['betrag'], 2, '.', "'") }}</td>
                        <td class="text-mitte">
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
                    <tr class="klient-zeile" style="background:#fef2f2;"
                        data-name="{{ strtolower($z['klient']->nachname . ' ' . $z['klient']->vorname) }}">
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
                        <td class="text-mitte">
                            <span class="badge {{ $typenBadge[$z['rechnungstyp']] ?? 'badge-grau' }}" style="opacity:0.6;">
                                {{ $typen[$z['rechnungstyp']] ?? $z['rechnungstyp'] }}
                            </span>
                        </td>
                        <td class="text-mitte" style="color:#dc2626;">0</td>
                        <td class="text-mitte text-hell">—</td>
                        <td class="text-mitte text-hell">—</td>
                        <td class="text-mitte text-hell">—</td>
                        <td class="text-mitte text-hell">—</td>
                        <td class="text-mitte">
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
                        <td class="text-mitte">{{ number_format($mitEinsaetzen->sum('betrag_patient'), 2, '.', "'") }}</td>
                        <td class="text-mitte">{{ number_format($mitEinsaetzen->sum('betrag_kk'), 2, '.', "'") }}</td>
                        <td class="text-mitte text-fett">CHF {{ number_format($vorschau['total_betrag'], 2, '.', "'") }}</td>
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
        const alle = klientCbs();
        const checkedCount = document.querySelectorAll('.klient-cb:checked').length;
        if (alleCheckbox) {
            alleCheckbox.checked       = checkedCount === alle.length;
            alleCheckbox.indeterminate = checkedCount > 0 && checkedCount < alle.length;
        }
    }

    if (alleCheckbox) {
        alleCheckbox.addEventListener('change', function () {
            klientCbs().forEach(cb => cb.checked = this.checked);
            aktualisiereAnzahl();
        });
    }

    klientCbs().forEach(cb => cb.addEventListener('change', aktualisiereAnzahl));

    // Klient-Suchfeld
    const suchfeld = document.getElementById('klient-suche');
    if (suchfeld) {
        suchfeld.addEventListener('input', function () {
            const suche = this.value.toLowerCase();
            document.querySelectorAll('#klient-tbody tr.klient-zeile').forEach(function (tr) {
                const name = tr.dataset.name || '';
                tr.style.display = name.includes(suche) ? '' : 'none';
            });
        });
    }
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
