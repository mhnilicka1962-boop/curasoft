<x-layouts.app>


<form method="POST" action="{{ route('klienten.rapportierung.speichern', [$klient, $jahr, $monat]) }}">
@csrf

{{-- Kompakter Header: Zurück | Monat-Nav | Speichern --}}
<div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
    <a href="{{ route('klienten.show', $klient) }}" class="link-gedaempt" style="font-size: 0.8rem; white-space: nowrap;">← {{ $klient->vorname }} {{ $klient->nachname }}</a>
    <div style="display:flex; align-items:center; gap:0.4rem; margin-left:auto;">
        <a href="{{ route('klienten.rapportierung', [$klient, $vorMonat->year, $vorMonat->month]) }}" class="btn btn-sekundaer" style="padding:0.2rem 0.6rem; font-size:0.9rem;">‹</a>
        <strong style="font-size:0.85rem; min-width:120px; text-align:center;">{{ $periodeVon->locale('de')->isoFormat('MMMM YYYY') }}</strong>
        <a href="{{ route('klienten.rapportierung', [$klient, $naechMonat->year, $naechMonat->month]) }}" class="btn btn-sekundaer" style="padding:0.2rem 0.6rem; font-size:0.9rem;">›</a>
    </div>
    @if($verfuegbareMonate->flatten()->count() > 1)
    <div style="display:flex; align-items:center; gap:0.3rem;">
        <select id="nav-jahr" class="feld" style="padding:0.2rem 0.4rem; font-size:0.78rem; width:auto;">
            @foreach($verfuegbareMonate->keys() as $j)
            <option value="{{ $j }}" {{ $j == $jahr ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
        </select>
        <select id="nav-monat" class="feld" style="padding:0.2rem 0.4rem; font-size:0.78rem; width:auto;">
            @foreach($verfuegbareMonate->get($jahr, collect()) as $m)
            <option value="{{ $m }}" {{ $m == $monat ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->locale('de')->isoFormat('MMMM') }}</option>
            @endforeach
        </select>
        <button type="button" onclick="navigiereZuMonat()" class="btn btn-sekundaer" style="padding:0.2rem 0.6rem; font-size:0.78rem;">→</button>
    </div>
    @endif
    <a href="{{ route('klienten.rapportierung.vorschau', [$klient, $jahr, $monat]) }}" target="_blank" class="btn btn-sekundaer" style="font-size: 0.8rem; padding: 0.3rem 0.9rem;">PDF Vorschau</a>
    <button type="submit" class="btn btn-primaer" style="font-size: 0.8rem; padding: 0.3rem 0.9rem;">Speichern</button>
</div>

{{-- Farblegende --}}
<div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:0.5rem; font-size:0.72rem; align-items:center;">
    <span style="display:flex; align-items:center; gap:0.3rem;"><span style="width:12px;height:12px;background:#d4edda;border:1px solid #ccc;border-radius:2px;display:inline-block;"></span> App-Einsatz</span>
    <span style="display:flex; align-items:center; gap:0.3rem;"><span style="width:12px;height:12px;background:#fed7aa;border:1px solid #ccc;border-radius:2px;display:inline-block;"></span> Admin korrigiert / erfasst</span>
    <span style="display:flex; align-items:center; gap:0.3rem;"><span style="width:12px;height:12px;background:#fee2e2;border:1px solid #ccc;border-radius:2px;display:inline-block;"></span> Gesperrt (Einsatz läuft)</span>
</div>

<div style="overflow-x: auto;">
<table class="raster-tabelle">
    <thead>
        <tr>
            <th class="raster-label-col">Leistungstyp</th>
            @for($t = 1; $t <= $tage; $t++)
            <th class="raster-tag-col {{ isset($aktivTage[$t]) ? 'raster-tag-aktiv' : '' }}"
                title="{{ $aktivTage[$t] ?? '' }}">
                {{ $t }}
                @if(isset($aktivEinsaetze[$t]))
                @php $ai = $aktivEinsaetze[$t]; @endphp
                <br><span style="font-size:0.58rem; line-height:1.3; display:block;">
                    {{ $ai['datum'] }}<br>
                    {{ $ai['name'] }}<br>
                    Login: {{ $ai['checkin_zeit'] }}
                </span>
                <button type="button"
                    onclick="zeigeCheckout({{ $ai['einsatz_id'] }})"
                    style="margin-top:0.2rem; font-size:0.58rem; padding:0.1rem 0.3rem; background:#dc2626; color:white; border:none; border-radius:3px; cursor:pointer; width:100%;">
                    Abschliessen
                </button>
                @endif
            </th>
            @endfor
        </tr>
    </thead>
    <tbody>
    @foreach($leistungsarten as $la)
        {{-- Leistungsart-Header mit Tagessummen --}}
        <tr class="raster-la-header">
            <td>{{ $la->bezeichnung }}</td>
            @for($t = 1; $t <= $tage; $t++)
            @php
                $appMin  = $appRaster[$la->id][$t]['minuten'] ?? 0;
                $rapMin  = collect($la->leistungstypen)->sum(fn($lt) => $raster[$lt->id][$t]['minuten'] ?? 0);
                $sumMin  = $appMin ?: $rapMin;
            @endphp
            <td style="text-align:center; font-size:0.65rem; font-weight:600; color:rgba(255,255,255,0.9); padding:1px 2px;">
                {{ $sumMin ?: '' }}
            </td>
            @endfor
        </tr>

        {{-- Leistungstypen --}}
        @foreach($la->leistungstypen as $lt)
        <tr class="raster-zeile">
            <td class="raster-label">{{ $lt->bezeichnung }}</td>
            @for($t = 1; $t <= $tage; $t++)
            @php
                $min           = $raster[$lt->id][$t]['minuten'] ?? 0;
                $adminOverride = $raster[$lt->id][$t]['admin_override'] ?? false;
                $adminEntry    = $raster[$lt->id][$t]['admin_entry'] ?? false;
                $history       = $raster[$lt->id][$t]['history'] ?? [];
                $hatKommentar  = ($adminOverride || $adminEntry) && !empty($history);
            @endphp
            <td class="raster-zelle {{ isset($aktivTage[$t]) ? 'raster-aktiv-gesperrt' : (($adminOverride || $adminEntry) ? 'raster-admin' : ($min > 0 ? 'raster-gefuellt' : '')) }}"
                title="{{ isset($aktivTage[$t]) ? ($aktivTage[$t] ?? '') : '' }}">
                <div style="display:flex; align-items:center; height:100%;">
                    <input type="number"
                        name="eintraege[{{ $lt->id }}][{{ $t }}]"
                        value="{{ $min ?: '' }}"
                        min="0" max="999"
                        class="raster-input"
                        style="flex:1; min-width:0;"
                        {{ isset($aktivTage[$t]) ? 'disabled' : '' }}>
                    @if($hatKommentar)
                    <button type="button"
                        class="raster-info-btn"
                        onclick="zeigeKommentarPopup(this)"
                        data-text="{{ e(implode("\n", $history)) }}">ℹ</button>
                    @endif
                </div>
            </td>
            @endfor
        </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
</div>


</form>

{{-- Popup: Kommentar-History --}}
<div id="popup-kommentar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.5rem; width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 0.75rem; font-size:0.95rem;">Admin-Korrekturen</h3>
        <pre id="kommentar-popup-text" style="font-size:0.78rem; white-space:pre-wrap; word-break:break-word; background:#f8f9fa; border-radius:4px; padding:0.75rem; margin:0 0 1rem;"></pre>
        <div style="text-align:right;">
            <button onclick="schliessePopup('popup-kommentar')" class="btn btn-sekundaer">Schliessen</button>
        </div>
    </div>
</div>

{{-- Popup: Legende --}}
<div id="popup-legende" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.25rem 1.5rem; width:280px; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 0.75rem; font-size:0.95rem;">Farblegende</h3>
        <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.82rem;">
            <span style="display:flex; align-items:center; gap:0.5rem;"><span style="width:14px;height:14px;background:#d4edda;border:1px solid #ccc;border-radius:2px;flex-shrink:0;"></span> App-Einsatz (erfasst via App)</span>
            <span style="display:flex; align-items:center; gap:0.5rem;"><span style="width:14px;height:14px;background:#fed7aa;border:1px solid #ccc;border-radius:2px;flex-shrink:0;"></span> Durch Admin korrigiert oder erfasst</span>
            <span style="display:flex; align-items:center; gap:0.5rem;"><span style="width:14px;height:14px;background:#fee2e2;border:1px solid #ccc;border-radius:2px;flex-shrink:0;"></span> Gesperrt — Einsatz läuft gerade</span>
        </div>
        <div style="margin-top:1rem; text-align:right;">
            <button onclick="document.getElementById('popup-legende').style.display='none'" class="btn btn-sekundaer">OK</button>
        </div>
    </div>
</div>

{{-- Popup: Checkout --}}
<div id="popup-checkout" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.5rem; width:300px; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 0.75rem;">Checkout eintragen</h3>
        <p class="text-klein text-hell" style="margin:0 0 0.75rem;">Mitarbeiter noch eingecheckt:</p>
        <input type="time" id="checkout-zeit" class="feld" style="margin-bottom:1rem;">
        <div style="display:flex; gap:0.5rem;">
            <button onclick="speichereCheckout()" class="btn btn-primaer" style="flex:1;">Speichern</button>
            <button onclick="schliessePopup('popup-checkout')" class="btn btn-sekundaer">Abbrechen</button>
        </div>
    </div>
</div>

{{-- Popup: Korrektur --}}
<div id="popup-korrektur" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.5rem; width:300px; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 0.75rem;">Minuten korrigieren</h3>
        <label class="feld-label">Minuten</label>
        <input type="number" id="korrektur-minuten" class="feld" min="0" style="margin-bottom:0.75rem;">
        <label class="feld-label">Grund (optional)</label>
        <input type="text" id="korrektur-kommentar" class="feld" maxlength="500" placeholder="z.B. Sandra hat falsch gestoppt" style="margin-bottom:1rem;">
        <div style="display:flex; gap:0.5rem;">
            <button onclick="speichereKorrektur()" class="btn btn-primaer" style="flex:1;">Speichern</button>
            <button onclick="schliessePopup('popup-korrektur')" class="btn btn-sekundaer">Abbrechen</button>
        </div>
    </div>
</div>

<style>
.raster-tabelle {
    border-collapse: collapse;
    font-size: 0.78rem;
    width: 100%;
    table-layout: fixed;
}
.raster-label-col {
    width: 170px;
    min-width: 170px;
    text-align: left;
    padding: 0.2rem 0.5rem;
    background: var(--cs-hintergrund);
    border: 1px solid var(--cs-border);
    position: sticky;
    left: 0;
    z-index: 2;
    font-weight: 600;
    font-size: 0.76rem;
}
.raster-tag-col {
    text-align: center;
    padding: 0.15rem 0;
    background: var(--cs-hintergrund);
    border: 1px solid var(--cs-border);
    font-weight: 500;
    font-size: 0.74rem;
    width: calc((100% - 170px) / {{ $tage }});
}
.raster-la-header td {
    background: var(--cs-primaer);
    color: white;
    padding: 0.2rem 0.5rem;
    font-weight: 600;
    font-size: 0.76rem;
    position: sticky;
    left: 0;
}
thead .raster-label-col {
    background: #1a1a1a;
    color: white;
}
.raster-zeile:hover td { background: #f8f9fa; }
.raster-label {
    padding: 0.2rem 0.5rem;
    border: 1px solid var(--cs-border);
    background: white;
    position: sticky;
    left: 0;
    z-index: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 170px;
}
.raster-app-zeile .raster-label {
    font-style: italic;
    color: var(--cs-text-hell);
    font-size: 0.72rem;
}
.raster-zelle {
    border: 1px solid var(--cs-border);
    padding: 0;
    text-align: center;
    height: 24px;
    position: relative;
}
.raster-gefuellt { background: #d4edda; }
.raster-aktiv    { background: #fff3cd; }
.raster-app      { background: #e8f4fd; }
.raster-input {
    width: 100%;
    height: 24px;
    border: none;
    background: transparent;
    text-align: center;
    font-size: 0.76rem;
    padding: 0;
    -moz-appearance: textfield;
}
.raster-input:focus { outline: 2px solid var(--cs-primaer); }
.raster-input::-webkit-outer-spin-button,
.raster-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.raster-btn {
    width: 100%;
    height: 28px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.78rem;
    padding: 0;
}
.raster-btn-aktiv { color: #e67e22; font-weight: 700; }
.raster-admin { background: #fed7aa !important; }
.raster-tag-aktiv { background: #fca5a5 !important; color: #7f1d1d !important; font-weight: 700; line-height: 1.2; padding: 0.2rem 0 !important; }
.raster-aktiv-gesperrt { background: #fee2e2 !important; cursor: not-allowed; }
.raster-aktiv-gesperrt .raster-input { cursor: not-allowed; color: #9ca3af; }
.raster-info-btn {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.65rem;
    line-height: 1;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.raster-info-btn:hover { background: #2563eb; }
</style>

<script>
let aktiverEinsatzId = null;

function zeigeCheckout(einsatzId) {
    aktiverEinsatzId = einsatzId;
    const jetzt = new Date();
    document.getElementById('checkout-zeit').value =
        String(jetzt.getHours()).padStart(2,'0') + ':' + String(jetzt.getMinutes()).padStart(2,'0');
    document.getElementById('popup-checkout').style.display = 'flex';
}

function speichereCheckout() {
    const zeit = document.getElementById('checkout-zeit').value;
    if (!zeit) return;
    fetch('{{ url('/rapportierung/einsatz') }}/' + aktiverEinsatzId + '/checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({checkout_zeit: zeit})
    }).then(r => r.json()).then(d => { if (d.ok) location.reload(); });
}

function zeigeKorrektur(einsatzId, minuten, kommentar) {
    aktiverEinsatzId = einsatzId;
    document.getElementById('korrektur-minuten').value = minuten;
    document.getElementById('korrektur-kommentar').value = kommentar;
    document.getElementById('popup-korrektur').style.display = 'flex';
}

function speichereKorrektur() {
    const minuten   = document.getElementById('korrektur-minuten').value;
    const kommentar = document.getElementById('korrektur-kommentar').value;
    fetch('{{ url('/rapportierung/einsatz') }}/' + aktiverEinsatzId + '/korrigieren', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({minuten: parseInt(minuten), admin_kommentar: kommentar})
    }).then(r => r.json()).then(d => { if (d.ok) location.reload(); });
}

function schliessePopup(id) {
    document.getElementById(id).style.display = 'none';
    aktiverEinsatzId = null;
}

const verfuegbareMonate = @json($verfuegbareMonate);
const baseUrl = '{{ url("/klienten/{$klient->id}/rapportierung") }}';
const monatsNamen = ['','Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];

function navigiereZuMonat() {
    const j = document.getElementById('nav-jahr').value;
    const m = document.getElementById('nav-monat').value;
    window.location.href = baseUrl + '/' + j + '/' + m;
}

document.getElementById('nav-jahr')?.addEventListener('change', function() {
    const j = parseInt(this.value);
    const monate = verfuegbareMonate[j] || [];
    const sel = document.getElementById('nav-monat');
    sel.innerHTML = monate.map(m => `<option value="${m}">${monatsNamen[m]}</option>`).join('');
});

function zeigeKommentarPopup(btn) {
    const text = btn.getAttribute('data-text');
    document.getElementById('kommentar-popup-text').textContent = text;
    document.getElementById('popup-kommentar').style.display = 'flex';
}
</script>
</x-layouts.app>
