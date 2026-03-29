<x-layouts.app :titel="'Serie bearbeiten — ' . $klient->vollname()">
<div style="max-width: 600px;">

    <div class="seiten-kopf">
        <a href="{{ route('klienten.show', $klient) }}" class="link-gedaempt text-klein">← {{ $klient->vollname() }}</a>
    </div>

    @if($errors->any())
    <div class="alert alert-fehler" style="margin-bottom: 1rem;">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <div class="karte">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div class="abschnitt-label" style="margin: 0;">Serie bearbeiten</div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" form="serie-edit-form" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('klienten.show', $klient) }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </div>

        <div style="font-size: 0.8125rem; color: var(--cs-text-hell); margin-bottom: 1.25rem; padding: 0.625rem 0.875rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius);">
            Gültig ab: <strong>{{ $serie->gueltig_ab->format('d.m.Y') }}</strong>
            · Einsätze bisher: <strong>{{ $serie->einsaetze()->count() }}</strong>
            · Zukünftig geplant: <strong>{{ $serie->einsaetze()->whereDate('datum', '>=', today())->where('status','geplant')->count() }}</strong>
        </div>

        <form id="serie-edit-form" method="POST" action="{{ route('klienten.serien.aktualisieren', [$klient, $serie]) }}">
            @csrf @method('PUT')

            {{-- Rhythmus --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Rhythmus <span style="color:var(--cs-fehler)">*</span></label>
                <select name="rhythmus" class="feld" style="max-width:180px;" onchange="zeigeWochentage(this)" required>
                    <option value="woechentlich" {{ old('rhythmus', $serie->rhythmus) === 'woechentlich' ? 'selected' : '' }}>Wöchentlich</option>
                    <option value="taeglich"     {{ old('rhythmus', $serie->rhythmus) === 'taeglich'     ? 'selected' : '' }}>Täglich</option>
                </select>
                <div class="text-klein text-hell" style="margin-top: 0.25rem;">Rhythmus-Änderung löscht zukünftige Einsätze und generiert sie neu.</div>
            </div>

            {{-- Wochentage --}}
            <div id="block-wochentage" style="margin-bottom: 0.875rem; {{ old('rhythmus', $serie->rhythmus) === 'taeglich' ? 'display:none;' : '' }}">
                <div class="feld-label" style="font-size: 0.75rem; margin-bottom: 0.375rem;">Wochentage</div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @php $gewaehlteTag = old('wochentage', $serie->wochentage ?? []); @endphp
                    @foreach(['1'=>'Mo','2'=>'Di','3'=>'Mi','4'=>'Do','5'=>'Fr','6'=>'Sa','0'=>'So'] as $nr => $tag)
                    <label class="wochentag-label" style="display:flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border:1px solid var(--cs-border); border-radius:999px; font-size:0.8125rem; cursor:pointer; background: {{ in_array((int)$nr, array_map('intval', $gewaehlteTag)) ? 'var(--cs-primaer)' : '#fff' }}; color: {{ in_array((int)$nr, array_map('intval', $gewaehlteTag)) ? '#fff' : 'inherit' }}; border-color: {{ in_array((int)$nr, array_map('intval', $gewaehlteTag)) ? 'var(--cs-primaer)' : 'var(--cs-border)' }};">
                        <input type="checkbox" name="wochentage[]" value="{{ $nr }}"
                            {{ in_array((int)$nr, array_map('intval', $gewaehlteTag)) ? 'checked' : '' }}
                            style="display:none;" class="wochentag-cb">
                        <span>{{ $tag }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Leistungsarten --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Leistungsarten <span style="color:var(--cs-fehler)">*</span></label>
                @php
                    $gespeicherteLa = collect($serie->leistungsarten ?? [])
                        ->keyBy(fn($la) => (int)($la['id'] ?? 0));
                @endphp
                @foreach($leistungsarten as $i => $la)
                @php $el = $gespeicherteLa[$la->id] ?? null; $checked = !!$el; @endphp
                <div style="display:flex; align-items:center; gap:0.5rem; padding:0.25rem 0; border-bottom:1px solid var(--cs-border); font-size:0.875rem;">
                    <label style="display:flex; align-items:center; gap:0.4rem; flex:1; cursor:pointer;">
                        <input type="checkbox" name="leistungsarten[{{ $i }}][id]" value="{{ $la->id }}"
                            {{ $checked ? 'checked' : '' }}
                            onchange="toggleMin(this)"
                            style="width:1rem; height:1rem; accent-color:var(--cs-primaer);">
                        {{ $la->bezeichnung }}
                    </label>
                    <input type="number" name="leistungsarten[{{ $i }}][minuten]"
                        value="{{ $el['minuten'] ?? 30 }}" min="5" step="5"
                        style="width:65px; {{ $checked ? '' : 'opacity:0.3;' }}" class="feld la-min">
                    <span class="text-hell" style="font-size:0.8rem;">Min.</span>
                </div>
                @endforeach
            </div>

            {{-- Zeiten --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.875rem;">
                <div>
                    <label class="feld-label">Von (geplant)</label>
                    <input type="time" name="zeit_von" class="feld" step="300"
                        value="{{ old('zeit_von', $serie->zeit_von ? substr($serie->zeit_von, 0, 5) : '') }}">
                </div>
                <div>
                    <label class="feld-label">Bis (geplant)</label>
                    <input type="time" name="zeit_bis" class="feld" step="300"
                        value="{{ old('zeit_bis', $serie->zeit_bis ? substr($serie->zeit_bis, 0, 5) : '') }}">
                </div>
            </div>

            {{-- Gültig bis --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Gültig bis</label>
                <input type="date" name="gueltig_bis" class="feld" style="max-width:200px;"
                    value="{{ old('gueltig_bis', $serie->gueltig_bis?->format('Y-m-d')) }}">
                <div class="text-klein text-hell" style="margin-top:0.25rem;">Leer lassen = unbegrenzt</div>
            </div>

            {{-- Mitarbeiter --}}
            @if(auth()->user()->rolle === 'admin')
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Mitarbeiter</label>
                <select name="benutzer_id" class="feld">
                    <option value="">— Eigener Account —</option>
                    @foreach($mitarbeiter as $m)
                    <option value="{{ $m->id }}" {{ old('benutzer_id', $serie->benutzer_id) == $m->id ? 'selected' : '' }}>
                        {{ $m->nachname }} {{ $m->vorname }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Leistungserbringer --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Leistungserbringer</label>
                <select name="leistungserbringer_typ" class="feld" style="max-width:260px;">
                    <option value="fachperson" {{ old('leistungserbringer_typ', $serie->leistungserbringer_typ) === 'fachperson' ? 'selected' : '' }}>Fachperson</option>
                    <option value="angehoerig" {{ old('leistungserbringer_typ', $serie->leistungserbringer_typ) === 'angehoerig' ? 'selected' : '' }}>Pflegender Angehöriger</option>
                </select>
            </div>

            {{-- Bemerkung --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Bemerkung</label>
                <textarea name="bemerkung" class="feld" rows="2" maxlength="500">{{ old('bemerkung', $serie->bemerkung) }}</textarea>
            </div>

        </form>
    </div>

</div>

@push('scripts')
<script>
function zeigeWochentage(sel) {
    document.getElementById('block-wochentage').style.display = sel.value === 'taeglich' ? 'none' : '';
}
function toggleMin(cb) {
    cb.closest('div').querySelector('.la-min').style.opacity = cb.checked ? '1' : '0.3';
}
document.querySelectorAll('.wochentag-cb').forEach(cb => {
    const label = cb.closest('label');
    function upd() {
        label.style.background  = cb.checked ? 'var(--cs-primaer)' : '#fff';
        label.style.color       = cb.checked ? '#fff' : 'inherit';
        label.style.borderColor = cb.checked ? 'var(--cs-primaer)' : 'var(--cs-border)';
    }
    cb.addEventListener('change', upd);
});
</script>
@endpush
</x-layouts.app>
