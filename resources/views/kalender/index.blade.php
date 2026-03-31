<x-layouts.app titel="Einsatzplanung — Kalender">

@push('styles')
<style>
    .kalender-wrap { height: calc(100vh - 115px); min-height: 500px; }

    .kl-controls { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; font-size: 0.8125rem; }
    .kl-controls label { display: flex; align-items: center; gap: 0.3rem; }
    .kl-controls .feld { width: auto; padding: 0.2rem 0.4rem; font-size: 0.8125rem; }
    .legende { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; flex: 1; }
    .legende-item { display: flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; }
    .legende-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

    .kl-popup {
        position: fixed; z-index: 9999;
        background: white; border: 1px solid var(--cs-border);
        border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        padding: 1rem 1.125rem; min-width: 220px; max-width: 280px;
        font-size: 0.875rem;
    }
    .kl-popup-titel { font-weight: 700; margin-bottom: 0.5rem; font-size: 0.9375rem; }
    .kl-popup-zeile { display: flex; gap: 0.5rem; margin-bottom: 0.25rem; color: var(--cs-text-hell); }
    .kl-popup-zeile strong { color: var(--cs-text); }
    .kl-popup-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }
    .kl-popup-close { position: absolute; top: 0.5rem; right: 0.625rem; cursor: pointer; font-size: 1.1rem; color: var(--cs-text-hell); background: none; border: none; line-height: 1; }

    .fc .fc-datagrid-cell-main { font-size: 0.8125rem; }
    .fc-event { cursor: pointer; border-radius: 4px !important; font-size: 0.75rem !important; padding: 1px 4px !important; }
    .fc .fc-col-header-cell-cushion { font-size: 0.75rem !important; font-weight: 500 !important; }
    .fc .fc-timeline-slot-label { font-size: 0.7rem !important; font-weight: 400 !important; color: #6b7280 !important; text-align: center !important; }
    .fc .fc-timeline-slot-label-cushion { display: block !important; text-align: center !important; }
    .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; }
    .fc .fc-button { font-size: 0.8125rem !important; padding: 0.3rem 0.65rem !important; background-color: var(--cs-primaer) !important; border-color: var(--cs-primaer) !important; }
    .fc .fc-button:hover { filter: brightness(0.9) !important; }
    .fc .fc-button-active { background-color: var(--cs-primaer-dunkel, #1a4a7a) !important; border-color: var(--cs-primaer-dunkel, #1a4a7a) !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2) !important; }
    .fc-resource-unzugeteilt .fc-datagrid-cell { background: #fefce8; }
</style>
@endpush

<div class="seiten-kopf" style="margin-bottom:0.5rem;">
    <h1>Einsatzplanung <span style="font-size:0.75rem; font-weight:400; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:999px; padding:0.15rem 0.6rem; vertical-align:middle;">💡 Doppelklick = neuer Einsatz</span></h1>
    <a href="{{ route('einsaetze.create') }}" class="btn btn-primaer">+ Neuer Einsatz</a>
</div>

<div class="kl-controls">
    <label>Von
        <select id="kl-von" class="feld">
            @for($h = 0; $h <= 23; $h++)
                <option value="{{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00:00" {{ $h === 6 ? 'selected' : '' }}>{{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00</option>
            @endfor
        </select>
    </label>
    <label>Bis
        <select id="kl-bis" class="feld">
            @for($h = 1; $h <= 24; $h++)
                <option value="{{ $h === 24 ? '24:00:00' : str_pad($h,2,'0',STR_PAD_LEFT).':00:00' }}" {{ $h === 22 ? 'selected' : '' }}>{{ $h === 24 ? '24:00' : str_pad($h,2,'0',STR_PAD_LEFT).':00' }}</option>
            @endfor
        </select>
    </label>
    <div class="legende">
        <span class="legende-item"><span class="legende-dot" style="background:#2563eb"></span> Geplant</span>
        <span class="legende-item"><span class="legende-dot" style="background:#d97706"></span> Aktiv</span>
        <span class="legende-item"><span class="legende-dot" style="background:#16a34a"></span> Abgeschlossen</span>
        <span class="legende-item"><span class="legende-dot" style="background:#dc2626"></span> ⚠ Doppelb.</span>
        <span class="legende-item"><span class="legende-dot" style="background:#fbbf24; border:1px solid #d97706;"></span> Nicht zugeteilt</span>
        <span class="legende-item"><span class="legende-dot" style="background:#fee2e2; border:1px solid #fca5a5;"></span> Keine Planung</span>
    </div>
    <button id="kl-ansicht-toggle" class="btn btn-sekundaer">Ansicht: Angestellte</button>
</div>

<div class="karte karte-null" style="padding: 0.75rem;">
    <div id="kalender" class="kalender-wrap"></div>
</div>

{{-- Popup --}}
<div id="kl-popup" class="kl-popup" style="display:none; position:fixed;">
    <button class="kl-popup-close" onclick="schliessePopup()">×</button>
    <div class="kl-popup-titel" id="kl-popup-titel"></div>
    <div id="kl-popup-body"></div>
    <div class="kl-popup-actions">
        <a id="kl-popup-edit"   href="#" class="btn btn-sekundaer" style="font-size:.8rem;padding:.3rem .65rem;">Bearbeiten</a>
        <a id="kl-popup-klient" href="#" class="btn btn-sekundaer" style="font-size:.8rem;padding:.3rem .65rem;">Klient</a>
    </div>
</div>

@push('scripts')
@vite('resources/js/kalender.js')
<script>
    const mitarbeiter       = @json($mitarbeiter->map(fn($m) => ['id' => $m->id, 'vorname' => $m->vorname, 'nachname' => $m->nachname]));
    const klienten          = @json($klienten->map(fn($k) => ['id' => $k->id, 'vorname' => $k->vorname, 'nachname' => $k->nachname]));
    const einsatzHorizont   = '{{ \App\Models\Organisation::find(auth()->user()->organisation_id)?->einsatz_vorlauf_tage ? today()->addDays(\App\Models\Organisation::find(auth()->user()->organisation_id)->einsatz_vorlauf_tage)->format('Y-m-d') : today()->addDays(10)->format('Y-m-d') }}';
    document.addEventListener('DOMContentLoaded', function() {
        window.KalenderInit(mitarbeiter, klienten, einsatzHorizont);
    });
</script>
@endpush

</x-layouts.app>
