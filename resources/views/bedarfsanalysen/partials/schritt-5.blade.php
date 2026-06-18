<div class="karte">

<div class="ba-sektion">
    <h3>Wohnverhältnisse</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Wohntyp</label>
            <div style="display:flex; gap:1.5rem; margin-top:0.25rem;">
                @foreach(['haus' => 'Haus', 'wohnung' => 'Wohnung'] as $val => $label)
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="wohntyp" value="{{ $val }}"
                           @checked(old('wohntyp', $analyse->wohntyp) === $val)>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="feld">
            <label>Anzahl Zimmer</label>
            <input type="number" name="anzahl_zimmer" value="{{ old('anzahl_zimmer', $analyse->anzahl_zimmer) }}" min="1" max="20">
        </div>
        <div class="feld" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="lift" id="lift" value="1"
                   @checked(old('lift', $analyse->lift))>
            <label for="lift" style="margin:0; font-weight:500;">Lift vorhanden</label>
        </div>
        <div class="feld" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="treppe" id="treppe" value="1"
                   @checked(old('treppe', $analyse->treppe))
                   onchange="document.getElementById('treppe_stufen_feld').style.display = this.checked ? '' : 'none'">
            <label for="treppe" style="margin:0; font-weight:500;">Treppe vorhanden</label>
        </div>
        <div class="feld" id="treppe_stufen_feld"
             style="{{ old('treppe', $analyse->treppe) ? '' : 'display:none;' }}">
            <label>Anzahl Stufen</label>
            <input type="number" name="treppe_stufen" value="{{ old('treppe_stufen', $analyse->treppe_stufen) }}" min="1" max="100">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Administrative Angaben</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll">
            <label>Welche Klinik (bei Krankenhausaufenthalten)</label>
            <input type="text" name="klinik" value="{{ old('klinik', $analyse->klinik) }}" placeholder="z.B. Kantonsspital Zürich">
        </div>
        <div class="feld" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="patientenverfuegung" id="patientenverfuegung" value="1"
                   @checked(old('patientenverfuegung', $analyse->patientenverfuegung))>
            <label for="patientenverfuegung" style="margin:0; font-weight:500;">Patientenverfügung vorhanden</label>
        </div>
        <div class="feld" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="haustiere" id="haustiere" value="1"
                   @checked(old('haustiere', $analyse->haustiere))
                   onchange="document.getElementById('haustiere_detail').style.display = this.checked ? '' : 'none'">
            <label for="haustiere" style="margin:0; font-weight:500;">Haustiere vorhanden</label>
        </div>
        <div class="feld" id="haustiere_detail"
             style="{{ old('haustiere', $analyse->haustiere) ? '' : 'display:none;' }}">
            <label>Welche Haustiere?</label>
            <input type="text" name="haustiere_details" value="{{ old('haustiere_details', $analyse->haustiere_details) }}"
                   placeholder="z.B. Hund, Katze">
        </div>
        <div class="feld">
            <label>Gewünschter Eintrittstermin</label>
            <input type="date" name="eintrittstermin" value="{{ old('eintrittstermin', $analyse->eintrittstermin?->format('Y-m-d')) }}">
        </div>
    </div>
</div>

</div>
