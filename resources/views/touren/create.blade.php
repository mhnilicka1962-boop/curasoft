<x-layouts.app titel="Neue Tour">
<div style="max-width: 640px;">

    <a href="{{ route('touren.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Touren</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">Neue Tour</h1>

    <div class="karte">
        <form method="POST" action="{{ route('touren.store') }}">
            @csrf

            <div class="form-grid-2" style="margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label">Mitarbeiter *</label>
                    <select name="benutzer_id" id="sel-ma" class="feld" required onchange="ladeEinsaetze()">
                        <option value="">— wählen —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ $vorBenutzerId == $m->id ? 'selected' : '' }}>
                                {{ $m->vorname }} {{ $m->nachname }}
                            </option>
                        @endforeach
                    </select>
                    @error('benutzer_id')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Datum *</label>
                    <input type="date" name="datum" id="sel-datum" class="feld" required
                        value="{{ $vorDatum }}" onchange="ladeEinsaetze()">
                </div>
            </div>

            <div style="margin-bottom: 0.75rem;">
                <label class="feld-label">Bezeichnung *</label>
                <input type="text" name="bezeichnung" id="bezeichnung" class="feld" required
                    placeholder="z.B. Morgenrunde, Nachmittag West"
                    value="{{ old('bezeichnung', $vorBezeichnung) }}">
                @error('bezeichnung')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="feld-label">Startzeit (geplant)</label>
                <input type="time" name="start_zeit" class="feld" style="max-width: 160px;" value="{{ old('start_zeit') }}">
            </div>

            {{-- Verfügbare Einsätze --------------------------------}}
            @if($verfuegbareEinsaetze->count())
            <div style="margin-bottom: 1rem; padding: 0.875rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.625rem;">
                    <span class="feld-label" style="margin-bottom: 0;">
                        Einsätze direkt zuweisen ({{ $verfuegbareEinsaetze->count() }} verfügbar)
                    </span>
                    <label style="font-size: 0.8125rem; cursor: pointer; color: var(--cs-primaer); display: flex; align-items: center; gap: 0.3rem;">
                        <input type="checkbox" id="alle-toggle" onchange="alleToggle(this.checked)" checked>
                        Alle
                    </label>
                </div>
                @foreach($verfuegbareEinsaetze as $e)
                <label style="display: flex; align-items: center; gap: 0.625rem; padding: 0.375rem 0; font-size: 0.875rem; cursor: pointer; border-bottom: 1px solid var(--cs-border);">
                    <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}" checked class="einsatz-cb">
                    <span style="font-weight: 600;">{{ $e->klient?->vollname() }}</span>
                    <span class="text-hell">{{ $e->leistungsart?->bezeichnung }}</span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="margin-left: auto; font-size: 0.8rem; flex-shrink: 0;">{{ \Carbon\Carbon::parse($e->zeit_von)->format('H:i') }}</span>
                    @endif
                </label>
                @endforeach
            </div>
            @elseif($vorBenutzerId)
            <div style="padding: 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <span class="text-hell">Keine offenen Einsätze für diesen Mitarbeiter an diesem Datum.</span>
                <a href="{{ route('einsaetze.create', ['benutzer_id' => $vorBenutzerId, 'datum' => $vorDatum, '_nach_touren' => 1]) }}"
                   class="btn btn-primaer" style="font-size: 0.8125rem; white-space: nowrap;">+ Einsatz anlegen</a>
            </div>
            @endif

            <div class="abschnitt-trenn" style="margin-top: 0; padding-top: 1rem;">
                <button type="submit" class="btn btn-primaer">Tour erstellen</button>
                <a href="{{ route('touren.index') }}" class="btn btn-sekundaer" style="margin-left: 0.5rem;">Abbrechen</a>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function ladeEinsaetze() {
    const ma    = document.getElementById('sel-ma').value;
    const datum = document.getElementById('sel-datum').value;
    const bez   = document.getElementById('bezeichnung').value;
    if (!ma || !datum) return;
    const url = new URL(window.location.href.split('?')[0]);
    url.searchParams.set('benutzer_id', ma);
    url.searchParams.set('datum', datum);
    if (bez) url.searchParams.set('bezeichnung', bez);
    window.location.href = url.toString();
}

function alleToggle(checked) {
    document.querySelectorAll('.einsatz-cb').forEach(cb => cb.checked = checked);
}
</script>
@endpush
</x-layouts.app>
