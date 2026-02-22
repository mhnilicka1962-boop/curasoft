@php $k = $klient ?? null; @endphp

{{-- Fehler --}}
@if($errors->any())
<div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
    @foreach($errors->all() as $fehler)<div>{{ $fehler }}</div>@endforeach
</div>
@endif

{{-- Persönliche Daten --}}
<div class="karte" style="margin-bottom: 1rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Persönliche Daten</div>

    <div style="display: grid; grid-template-columns: 140px 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
        <div>
            <label class="feld-label" for="anrede">Anrede</label>
            <select id="anrede" name="anrede" class="feld">
                <option value="">—</option>
                <option value="Herr"     {{ old('anrede', $k?->anrede) === 'Herr'     ? 'selected' : '' }}>Herr</option>
                <option value="Frau"     {{ old('anrede', $k?->anrede) === 'Frau'     ? 'selected' : '' }}>Frau</option>
                <option value="Dr. Herr" {{ old('anrede', $k?->anrede) === 'Dr. Herr' ? 'selected' : '' }}>Dr. Herr</option>
                <option value="Dr. Frau" {{ old('anrede', $k?->anrede) === 'Dr. Frau' ? 'selected' : '' }}>Dr. Frau</option>
            </select>
        </div>
        <div>
            <label class="feld-label" for="vorname">Vorname *</label>
            <input type="text" id="vorname" name="vorname" class="feld"
                value="{{ old('vorname', $k?->vorname) }}" required>
        </div>
        <div>
            <label class="feld-label" for="nachname">Nachname *</label>
            <input type="text" id="nachname" name="nachname" class="feld"
                value="{{ old('nachname', $k?->nachname) }}" required>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 80px; gap: 0.75rem;">
        <div>
            <label class="feld-label" for="geburtsdatum">Geburtsdatum</label>
            <input type="date" id="geburtsdatum" name="geburtsdatum" class="feld"
                value="{{ old('geburtsdatum', $k?->geburtsdatum?->format('Y-m-d')) }}">
        </div>
        <div>
            <label class="feld-label" for="geschlecht">Geschlecht</label>
            <select id="geschlecht" name="geschlecht" class="feld">
                <option value="">—</option>
                <option value="m" {{ old('geschlecht', $k?->geschlecht) === 'm' ? 'selected' : '' }}>Männlich</option>
                <option value="w" {{ old('geschlecht', $k?->geschlecht) === 'w' ? 'selected' : '' }}>Weiblich</option>
                <option value="x" {{ old('geschlecht', $k?->geschlecht) === 'x' ? 'selected' : '' }}>Divers</option>
            </select>
        </div>
        <div>
            <label class="feld-label" for="zivilstand">Zivilstand</label>
            <select id="zivilstand" name="zivilstand" class="feld">
                <option value="">—</option>
                <option value="ledig"        {{ old('zivilstand', $k?->zivilstand) === 'ledig'        ? 'selected' : '' }}>Ledig</option>
                <option value="verheiratet"  {{ old('zivilstand', $k?->zivilstand) === 'verheiratet'  ? 'selected' : '' }}>Verheiratet</option>
                <option value="geschieden"   {{ old('zivilstand', $k?->zivilstand) === 'geschieden'   ? 'selected' : '' }}>Geschieden</option>
                <option value="verwitwet"    {{ old('zivilstand', $k?->zivilstand) === 'verwitwet'    ? 'selected' : '' }}>Verwitwet</option>
                <option value="eingetragen"  {{ old('zivilstand', $k?->zivilstand) === 'eingetragen'  ? 'selected' : '' }}>Eingetr. Partnerschaft</option>
            </select>
        </div>
        <div>
            <label class="feld-label" for="anzahl_kinder">Kinder</label>
            <input type="number" id="anzahl_kinder" name="anzahl_kinder" class="feld" min="0"
                value="{{ old('anzahl_kinder', $k?->anzahl_kinder) }}">
        </div>
    </div>
</div>

{{-- Kontakt & Adresse --}}
<div class="karte" style="margin-bottom: 1rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Kontakt & Adresse</div>

    <div style="margin-bottom: 0.75rem;">
        <label class="feld-label" for="adresse">Strasse & Hausnummer</label>
        <input type="text" id="adresse" name="adresse" class="feld"
            value="{{ old('adresse', $k?->adresse) }}" placeholder="Musterstrasse 12">
    </div>
    <div style="display: grid; grid-template-columns: 120px 1fr 100px; gap: 0.75rem; margin-bottom: 0.75rem;">
        <div>
            <label class="feld-label" for="plz">PLZ</label>
            <input type="text" id="plz" name="plz" class="feld"
                value="{{ old('plz', $k?->plz) }}" placeholder="6340">
        </div>
        <div>
            <label class="feld-label" for="ort">Ort</label>
            <input type="text" id="ort" name="ort" class="feld"
                value="{{ old('ort', $k?->ort) }}" placeholder="Baar">
        </div>
        <div>
            <label class="feld-label" for="region_id">Kanton <span style="color:var(--cs-fehler);">*</span></label>
            <select id="region_id" name="region_id" class="feld">
                <option value="">—</option>
                @foreach($regionen ?? [] as $r)
                    <option value="{{ $r->id }}" {{ old('region_id', $k?->region_id) == $r->id ? 'selected' : '' }}>
                        {{ $r->kuerzel }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-grid-3" style="gap: 0.75rem;">
        <div>
            <label class="feld-label" for="telefon">Telefon</label>
            <input type="text" id="telefon" name="telefon" class="feld"
                value="{{ old('telefon', $k?->telefon) }}" placeholder="041 000 00 00">
        </div>
        <div>
            <label class="feld-label" for="notfallnummer">Notfallnummer *</label>
            <input type="text" id="notfallnummer" name="notfallnummer" class="feld"
                value="{{ old('notfallnummer', $k?->notfallnummer) }}" placeholder="079 000 00 00">
        </div>
        <div>
            <label class="feld-label" for="email">E-Mail</label>
            <input type="email" id="email" name="email" class="feld"
                value="{{ old('email', $k?->email) }}" placeholder="name@beispiel.ch">
        </div>
    </div>
</div>

{{-- Einsatz-Planung --}}
<div class="karte" style="margin-bottom: 1rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Einsatz-Planung</div>

    <div class="form-grid-3" style="gap: 0.75rem;">
        <div>
            <label class="feld-label" for="datum_erstkontakt">Datum Erstkontakt</label>
            <input type="date" id="datum_erstkontakt" name="datum_erstkontakt" class="feld"
                value="{{ old('datum_erstkontakt', $k?->datum_erstkontakt?->format('Y-m-d')) }}">
        </div>
        <div>
            <label class="feld-label" for="einsatz_geplant_von">Einsatz geplant ab</label>
            <input type="date" id="einsatz_geplant_von" name="einsatz_geplant_von" class="feld"
                value="{{ old('einsatz_geplant_von', $k?->einsatz_geplant_von?->format('Y-m-d')) }}">
        </div>
        <div>
            <label class="feld-label" for="einsatz_geplant_bis">Einsatz geplant bis</label>
            <input type="date" id="einsatz_geplant_bis" name="einsatz_geplant_bis" class="feld"
                value="{{ old('einsatz_geplant_bis', $k?->einsatz_geplant_bis?->format('Y-m-d')) }}">
        </div>
    </div>
    <div style="margin-top: 0.75rem;">
        <label class="feld-label" for="zustaendig_id">Zuständig</label>
        <select id="zustaendig_id" name="zustaendig_id" class="feld" style="max-width: 300px;">
            <option value="">— nicht zugewiesen —</option>
            @foreach($mitarbeiter ?? [] as $m)
                <option value="{{ $m->id }}" {{ old('zustaendig_id', $k?->zustaendig_id) == $m->id ? 'selected' : '' }}>
                    {{ $m->nachname }} {{ $m->vorname }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Krankenkasse & AHV --}}
<div class="karte" style="margin-bottom: 1rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Krankenkasse & AHV</div>

    <div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
        <div>
            <label class="feld-label" for="krankenkasse_name">Krankenkasse</label>
            <input type="text" id="krankenkasse_name" name="krankenkasse_name" class="feld"
                value="{{ old('krankenkasse_name', $k?->krankenkasse_name) }}" placeholder="CSS, Helsana, …">
        </div>
        <div>
            <label class="feld-label" for="krankenkasse_nr">Krankenkassen-Nr.</label>
            <input type="text" id="krankenkasse_nr" name="krankenkasse_nr" class="feld"
                value="{{ old('krankenkasse_nr', $k?->krankenkasse_nr) }}">
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 120px; gap: 0.75rem; max-width: 500px;">
        <div>
            <label class="feld-label" for="ahv_nr">AHV-Nummer</label>
            <input type="text" id="ahv_nr" name="ahv_nr" class="feld"
                value="{{ old('ahv_nr', $k?->ahv_nr) }}" placeholder="756.XXXX.XXXX.XX">
        </div>
        <div>
            <label class="feld-label" for="zahlbar_tage">Zahlbar (Tage)</label>
            <input type="number" id="zahlbar_tage" name="zahlbar_tage" class="feld" min="1"
                value="{{ old('zahlbar_tage', $k?->zahlbar_tage ?? 30) }}">
        </div>
    </div>
</div>
