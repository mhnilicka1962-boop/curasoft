<x-layouts.app :titel="'Neuer Einsatz'">
<div style="max-width: 600px;">
    <a href="{{ route('einsaetze.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Einsätze</a>

    @if($errors->any())
        <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Neuer Einsatz</div>

        <form method="POST" action="{{ route('einsaetze.store') }}">
            @csrf

            {{-- Klient --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="klient_id">Klient <span style="color:var(--cs-fehler);">*</span></label>
                <select id="klient_id" name="klient_id" class="feld" required>
                    <option value="">— bitte wählen —</option>
                    @foreach($klienten as $k)
                        <option value="{{ $k->id }}"
                            data-kanton="{{ $k->region?->kuerzel ?? '' }}"
                            {{ old('klient_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->vollname() }}
                            @if($k->region) ({{ $k->region->kuerzel }}) @endif
                        </option>
                    @endforeach
                </select>
                <div id="klient-kanton" class="text-klein text-hell" style="margin-top: 0.25rem;"></div>
            </div>

            {{-- Leistungsart --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="leistungsart_id">Leistungsart <span style="color:var(--cs-fehler);">*</span></label>
                <select id="leistungsart_id" name="leistungsart_id" class="feld" required>
                    <option value="">— bitte wählen —</option>
                    @foreach($leistungsarten as $la)
                        <option value="{{ $la->id }}"
                            data-einheit="{{ $la->einheit }}"
                            {{ old('leistungsart_id') == $la->id ? 'selected' : '' }}>
                            {{ $la->bezeichnung }}
                            @if($la->einheit === 'tage') (Tagespauschale) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Datum (immer) --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="datum" id="label-datum">Datum <span style="color:var(--cs-fehler);">*</span></label>
                <input type="date" id="datum" name="datum" class="feld"
                    value="{{ old('datum', date('Y-m-d')) }}" required>
            </div>

            {{-- Datum bis (nur Tagespauschale) --}}
            <div id="block-datum-bis" style="margin-bottom: 1rem; display: none;">
                <label class="feld-label" for="datum_bis">Datum bis <span style="color:var(--cs-fehler);">*</span></label>
                <input type="date" id="datum_bis" name="datum_bis" class="feld"
                    value="{{ old('datum_bis') }}">
                <div id="anzahl-tage" style="font-size: 0.8125rem; color: var(--cs-primaer); margin-top: 0.25rem;"></div>
            </div>

            {{-- Zeit (nur Minuten/Stunden) --}}
            <div id="block-zeit" class="form-grid-2" style="margin-bottom: 1rem;">
                <div>
                    <label class="feld-label" for="zeit_von">Von (geplant)</label>
                    <input type="time" id="zeit_von" name="zeit_von" class="feld"
                        value="{{ old('zeit_von') }}">
                </div>
                <div>
                    <label class="feld-label" for="zeit_bis">Bis (geplant)</label>
                    <input type="time" id="zeit_bis" name="zeit_bis" class="feld"
                        value="{{ old('zeit_bis') }}">
                </div>
            </div>

            {{-- Mitarbeiter (nur Admin) --}}
            @if(auth()->user()->rolle === 'admin' && $mitarbeiter->count())
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="benutzer_id">Mitarbeiter</label>
                <select id="benutzer_id" name="benutzer_id" class="feld">
                    <option value="">— Eigener Account —</option>
                    @foreach($mitarbeiter as $m)
                        <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nachname }} {{ $m->vorname }} ({{ $m->rolle }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Bemerkung --}}
            <div style="margin-bottom: 1.5rem;">
                <label class="feld-label" for="bemerkung">Bemerkung</label>
                <textarea id="bemerkung" name="bemerkung" class="feld" rows="3"
                    style="resize: vertical;" maxlength="1000">{{ old('bemerkung') }}</textarea>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Einsatz anlegen</button>
                <a href="{{ route('einsaetze.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const klientSelect     = document.getElementById('klient_id');
const leistungsSelect  = document.getElementById('leistungsart_id');
const kantonInfo       = document.getElementById('klient-kanton');
const blockDatumBis    = document.getElementById('block-datum-bis');
const blockZeit        = document.getElementById('block-zeit');
const datumInput       = document.getElementById('datum');
const datumBisInput    = document.getElementById('datum_bis');
const anzahlTageInfo   = document.getElementById('anzahl-tage');
const labelDatum       = document.getElementById('label-datum');

function aktualisiereKanton() {
    const opt = klientSelect.options[klientSelect.selectedIndex];
    const kuerzel = opt ? opt.dataset.kanton : '';
    kantonInfo.textContent = kuerzel ? 'Kanton: ' + kuerzel : '';
}

function aktualisiereLeistungsart() {
    const opt = leistungsSelect.options[leistungsSelect.selectedIndex];
    const einheit = opt ? opt.dataset.einheit : '';
    const istPauschale = einheit === 'tage';

    blockDatumBis.style.display = istPauschale ? 'block' : 'none';
    blockZeit.style.display     = istPauschale ? 'none'  : 'grid';
    datumBisInput.required      = istPauschale;
    labelDatum.querySelector('label') ??
        (labelDatum.textContent = istPauschale ? 'Datum von *' : 'Datum *');

    berechneAnzahlTage();
}

function berechneAnzahlTage() {
    const von = datumInput.value;
    const bis = datumBisInput.value;
    if (von && bis && von <= bis) {
        const diffMs  = new Date(bis) - new Date(von);
        const tage    = Math.round(diffMs / 86400000) + 1;
        anzahlTageInfo.textContent = tage + ' Tag' + (tage !== 1 ? 'e' : '');
    } else {
        anzahlTageInfo.textContent = '';
    }
}

klientSelect.addEventListener('change', aktualisiereKanton);
leistungsSelect.addEventListener('change', aktualisiereLeistungsart);
datumInput.addEventListener('change', berechneAnzahlTage);
datumBisInput.addEventListener('change', berechneAnzahlTage);

aktualisiereKanton();
aktualisiereLeistungsart();
</script>
@endpush
</x-layouts.app>
