<div class="karte">

<div class="ba-sektion">
    <h3>Verpflegung</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="wunschkost" id="wunschkost" value="1"
                   @checked(old('wunschkost', $analyse->wunschkost))
                   onchange="document.getElementById('wunschkost_detail').style.display = this.checked ? '' : 'none'">
            <label for="wunschkost" style="margin:0; font-weight:500;">Wunschkost / besondere Ernährung</label>
        </div>
        <div class="feld ba-voll" id="wunschkost_detail"
             style="{{ old('wunschkost', $analyse->wunschkost) ? '' : 'display:none;' }}">
            <label>Was?</label>
            <input type="text" name="wunschkost_details" value="{{ old('wunschkost_details', $analyse->wunschkost_details) }}"
                   placeholder="z.B. vegetarisch, Diabeteskost, püriert…">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Ambulanter Pflegedienst</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="pflegedienst_aktuell" id="pflegedienst_aktuell" value="1"
                   @checked(old('pflegedienst_aktuell', $analyse->pflegedienst_aktuell))
                   onchange="document.getElementById('pflegedienst_detail').style.display = this.checked ? '' : 'none'">
            <label for="pflegedienst_aktuell" style="margin:0; font-weight:500;">Ambulanter Pflegedienst aktuell tätig</label>
        </div>
        <div id="pflegedienst_detail" style="{{ old('pflegedienst_aktuell', $analyse->pflegedienst_aktuell) ? '' : 'display:none;' }}" class="ba-voll">
            <div class="ba-form-grid">
                <div class="feld">
                    <label>Welcher Dienst?</label>
                    <input type="text" name="pflegedienst_name" value="{{ old('pflegedienst_name', $analyse->pflegedienst_name) }}">
                </div>
                <div class="feld">
                    <label>Wie oft?</label>
                    <input type="text" name="pflegedienst_frequenz" value="{{ old('pflegedienst_frequenz', $analyse->pflegedienst_frequenz) }}"
                           placeholder="z.B. 3× wöchentlich">
                </div>
                <div class="feld ba-voll">
                    <label>Welche Aufgaben?</label>
                    <textarea name="pflegedienst_aufgaben" rows="2">{{ old('pflegedienst_aufgaben', $analyse->pflegedienst_aufgaben) }}</textarea>
                </div>
            </div>
        </div>
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="pflegedienst_abbestellen" id="pflegedienst_abbestellen" value="1"
                   @checked(old('pflegedienst_abbestellen', $analyse->pflegedienst_abbestellen))>
            <label for="pflegedienst_abbestellen" style="margin:0; font-weight:500;">Pflegedienst soll nach Möglichkeit abbestellt werden</label>
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Weitere Angaben</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll">
            <label>Raucher / Nichtraucher</label>
            <div style="display:flex; gap:1.5rem; margin-top:0.25rem;">
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="raucher" value="0"
                           @checked(old('raucher', $analyse->raucher) === false || old('raucher', $analyse->raucher) === '0')>
                    Nichtraucher
                </label>
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="raucher" value="1"
                           @checked(old('raucher', $analyse->raucher) === true || old('raucher', $analyse->raucher) === '1')>
                    Raucher
                </label>
            </div>
        </div>
    </div>
</div>

</div>
