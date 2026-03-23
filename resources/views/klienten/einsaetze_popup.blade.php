{{-- Popup-Inhalt: Einsätze für einen Klienten (wird via AJAX geladen) --}}
@php $heute = $heute ?? today(); @endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
    <div>
        <div style="font-size:1rem; font-weight:700;">Einsätze — {{ $klient->vollname() }}</div>
        <div class="text-hell text-klein">{{ $anstehend->count() }} anstehend · {{ $vergangen->count() }} vergangen</div>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="{{ route('klienten.rapportierung', [$klient, now()->year, now()->month]) }}"
           style="display:inline-flex; flex-direction:column; align-items:flex-start; padding:0.3rem 0.75rem; background:var(--cs-primaer); color:#fff; border-radius:var(--cs-radius); text-decoration:none; line-height:1.4; font-size:0.8rem;">
            Rapportierung
        </a>
        <a href="{{ route('klienten.qr', $klient) }}" target="_blank" class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.3rem 0.75rem;">QR Check-in</a>
    </div>
</div>

{{-- Tabs --}}
<div style="display:flex; border-bottom:2px solid var(--cs-border); margin-bottom:0.75rem;">
    <button onclick="einsatzTabPopup('anstehend')" id="ptab-anstehend"
        style="padding:0.375rem 0.875rem; font-size:0.8125rem; font-weight:600; background:none; border:none; border-bottom:2px solid var(--cs-primaer); margin-bottom:-2px; cursor:pointer; color:var(--cs-primaer);">
        Anstehend ({{ $anstehend->count() }})
    </button>
    <button onclick="einsatzTabPopup('vergangen')" id="ptab-vergangen"
        style="padding:0.375rem 0.875rem; font-size:0.8125rem; font-weight:600; background:none; border:none; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer; color:var(--cs-text-hell);">
        Vergangen ({{ $vergangen->count() }})
    </button>
    <button onclick="einsatzTabPopup('monat')" id="ptab-monat"
        style="padding:0.375rem 0.875rem; font-size:0.8125rem; font-weight:600; background:none; border:none; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer; color:var(--cs-text-hell);">
        {{ now()->format('F Y') }} ({{ $monat->count() }})
    </button>
</div>

@php
function renderEinsatzZeile($e, $heute, $withDelete = false) {
    // rendered inline below
}
@endphp

{{-- Anstehend --}}
<div id="ppanel-anstehend" style="max-height:400px; overflow-y:auto;">
    @forelse($anstehend as $e)
    @php $istHeute = $e->datum->isToday(); @endphp
    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.4375rem 0.25rem; border-bottom:1px solid var(--cs-border); font-size:0.875rem; gap:0.5rem; flex-wrap:wrap; {{ $istHeute ? 'background:#eff6ff; border-radius:4px;' : '' }}">
        <div style="display:flex; gap:0.625rem; align-items:center; flex-wrap:wrap; flex:1;">
            <span style="color:{{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; min-width:80px; font-weight:{{ $istHeute ? '700' : '400' }}; white-space:nowrap;">{{ $e->datum->format('d.m.Y') }}</span>
            @if($e->zeit_von)<span class="text-hell" style="white-space:nowrap; font-size:0.8rem;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>@endif
            <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
            @if($e->tagespauschale_id)<span class="badge badge-info" style="font-size:0.7rem;">Pauschale</span>@endif
            @if($e->benutzer)<span class="text-hell" style="font-size:0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>@endif
            @if($e->tour)<a href="{{ route('touren.show', $e->tour) }}" class="badge badge-primaer" style="font-size:0.7rem; text-decoration:none;">{{ $e->tour->bezeichnung }}</a>
            @elseif(!$e->tagespauschale_id && $e->status === 'geplant')<span class="badge badge-warnung" style="font-size:0.7rem;">Keine Tour</span>@endif
        </div>
        <div style="display:flex; gap:0.375rem; align-items:center; flex-shrink:0;">
            <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
            @if(!$e->tagespauschale_id)
                <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                @if($e->status === 'geplant' && !$e->tour_id)
                <form method="POST" action="{{ route('einsaetze.destroy', $e) }}" style="margin:0;" onsubmit="return confirm('Einsatz löschen?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--cs-fehler); font-size:0.8rem; padding:0; line-height:1.4;">× löschen</button>
                </form>
                @endif
            @endif
        </div>
    </div>
    @empty
    <p class="text-klein text-hell" style="padding:0.5rem 0; margin:0;">Keine anstehenden Einsätze.</p>
    @endforelse
</div>

{{-- Vergangen --}}
<div id="ppanel-vergangen" style="display:none; max-height:400px; overflow-y:auto;">
    @forelse($vergangen as $e)
    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.4375rem 0.25rem; border-bottom:1px solid var(--cs-border); font-size:0.875rem; gap:0.5rem; flex-wrap:wrap;">
        <div style="display:flex; gap:0.625rem; align-items:center; flex-wrap:wrap; flex:1;">
            <span class="text-hell" style="min-width:80px; white-space:nowrap;">{{ $e->datum->format('d.m.Y') }}</span>
            @if($e->zeit_von)<span class="text-hell" style="white-space:nowrap; font-size:0.8rem;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>@endif
            <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
            @if($e->tagespauschale_id)<span class="badge badge-info" style="font-size:0.7rem;">Pauschale</span>@endif
            @if($e->benutzer)<span class="text-hell" style="font-size:0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>@endif
        </div>
        <div style="display:flex; gap:0.375rem; align-items:center; flex-shrink:0;">
            <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
            @if($e->checkin_methode === 'rapportierung')<span class="badge badge-info" style="font-size:0.7rem;">Rapportierung</span>@endif
            @if(!$e->tagespauschale_id)<a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>@endif
        </div>
    </div>
    @empty
    <p class="text-klein text-hell" style="padding:0.5rem 0; margin:0;">Keine vergangenen Einsätze.</p>
    @endforelse
</div>

{{-- Monat --}}
<div id="ppanel-monat" style="display:none; max-height:400px; overflow-y:auto;">
    @forelse($monat as $e)
    @php $istHeute = $e->datum->isToday(); @endphp
    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.4375rem 0.25rem; border-bottom:1px solid var(--cs-border); font-size:0.875rem; gap:0.5rem; flex-wrap:wrap; {{ $istHeute ? 'background:#eff6ff; border-radius:4px;' : '' }}">
        <div style="display:flex; gap:0.625rem; align-items:center; flex-wrap:wrap; flex:1;">
            <span style="min-width:80px; white-space:nowrap; color:{{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; font-weight:{{ $istHeute ? '700' : '400' }}">{{ $e->datum->format('d.m.Y') }}</span>
            <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
            @if($e->tagespauschale_id)<span class="badge badge-info" style="font-size:0.7rem;">Pauschale</span>@endif
            @if($e->benutzer)<span class="text-hell" style="font-size:0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>@endif
        </div>
        <div style="display:flex; gap:0.375rem; align-items:center; flex-shrink:0;">
            <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
            @if(!$e->tagespauschale_id)<a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>@endif
        </div>
    </div>
    @empty
    <p class="text-klein text-hell" style="padding:0.5rem 0; margin:0;">Keine Einsätze in diesem Monat.</p>
    @endforelse
</div>

{{-- Einsatz planen --}}
@if(auth()->user()->rolle === 'admin')
<details style="margin-top:0.75rem;" {{ session('erfolg') && str_contains(session('erfolg',''), 'geplant') ? 'open' : '' }}>
    <summary style="font-size:0.8125rem; font-weight:600; color:var(--cs-primaer); cursor:pointer; padding:0.375rem 0; list-style:none;">+ Einsatz planen</summary>
    <div style="margin-top:0.75rem; padding:1rem; border:1px solid var(--cs-border); border-radius:var(--cs-radius); background:var(--cs-hintergrund);">
        <form method="POST" action="{{ route('einsaetze.store') }}">
            @csrf
            <input type="hidden" name="klient_id" value="{{ $klient->id }}">
            <input type="hidden" name="_klient_redirect" value="1">
            <div class="form-grid" style="margin-bottom:0.75rem;">
                <div>
                    <label class="feld-label" style="font-size:0.75rem;">Leistungsart *</label>
                    <select name="leistungsart_id" class="feld" required style="font-size:0.875rem;" id="popup-plan-la">
                        <option value="">— wählen —</option>
                        @foreach($leistungsarten as $la)
                            <option value="{{ $la->id }}" data-einheit="{{ $la->einheit }}" {{ old('leistungsart_id') == $la->id ? 'selected' : '' }}>{{ $la->bezeichnung }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label" style="font-size:0.75rem;">Datum *</label>
                    <input type="date" name="datum" class="feld" required style="font-size:0.875rem;" value="{{ old('datum', date('Y-m-d')) }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size:0.75rem;">Von</label>
                    <input type="time" name="zeit_von" class="feld" style="font-size:0.875rem;" value="{{ old('zeit_von') }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size:0.75rem;">Bis</label>
                    <input type="time" name="zeit_bis" class="feld" style="font-size:0.875rem;" value="{{ old('zeit_bis') }}">
                </div>
                @if($mitarbeiter->count())
                <div>
                    <label class="feld-label" style="font-size:0.75rem;">Mitarbeiter</label>
                    <select name="benutzer_id" class="feld" style="font-size:0.875rem;">
                        <option value="">— selbst —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>{{ $m->nachname }} {{ $m->vorname }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div style="margin-bottom:0.75rem;">
                <label class="feld-label" style="font-size:0.75rem;">Bemerkung</label>
                <textarea name="bemerkung" class="feld" rows="2" style="font-size:0.875rem; resize:vertical;" maxlength="1000">{{ old('bemerkung') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primaer" style="font-size:0.875rem;">Einsatz planen</button>
        </form>
    </div>
</details>
@endif
