<div class="karte">

<div class="ba-sektion">
    <h3>Krankenkasse KVG</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Krankenkasse</label>
            <input type="text" name="kvg_krankenkasse" value="{{ old('kvg_krankenkasse', $analyse->kvg_krankenkasse) }}" placeholder="z.B. Helsana">
        </div>
        <div class="feld">
            <label>Anschrift</label>
            <input type="text" name="kvg_anschrift" value="{{ old('kvg_anschrift', $analyse->kvg_anschrift) }}" placeholder="Adresse der Krankenkasse">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>VVG / Zusatzversicherung</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="vvg_vorhanden" id="vvg_vorhanden" value="1"
                   @checked(old('vvg_vorhanden', $analyse->vvg_vorhanden))
                   onchange="document.getElementById('vvg_detail').style.display = this.checked ? '' : 'none'">
            <label for="vvg_vorhanden" style="margin:0; font-weight:500;">VVG Zusatzversicherung vorhanden</label>
        </div>
        <div class="feld ba-voll" id="vvg_detail" style="{{ old('vvg_vorhanden', $analyse->vvg_vorhanden) ? '' : 'display:none;' }}">
            <label>Deckungstyp</label>
            <div style="display:flex; gap:1.5rem; margin-top:0.25rem;">
                @foreach(['halbprivat' => 'Halbprivat', 'privat' => 'Privat'] as $val => $label)
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="vvg_deckungstyp" value="{{ $val }}"
                           @checked(old('vvg_deckungstyp', $analyse->vvg_deckungstyp) === $val)>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Pflegeversicherung</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="pflegeversicherung" id="pflegeversicherung" value="1"
                   @checked(old('pflegeversicherung', $analyse->pflegeversicherung))
                   onchange="document.getElementById('pv_name').style.display = this.checked ? '' : 'none'">
            <label for="pflegeversicherung" style="margin:0; font-weight:500;">Pflegeversicherung vorhanden</label>
        </div>
        <div class="feld" id="pv_name" style="{{ old('pflegeversicherung', $analyse->pflegeversicherung) ? '' : 'display:none;' }}">
            <label>Welche?</label>
            <input type="text" name="pflegeversicherung_name" value="{{ old('pflegeversicherung_name', $analyse->pflegeversicherung_name) }}">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Zweite Krankenkasse</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Zweite Krankenkasse</label>
            <input type="text" name="zweite_krankenkasse" value="{{ old('zweite_krankenkasse', $analyse->zweite_krankenkasse) }}">
        </div>
        <div class="feld">
            <label>Anschrift</label>
            <input type="text" name="zweite_krankenkasse_anschrift" value="{{ old('zweite_krankenkasse_anschrift', $analyse->zweite_krankenkasse_anschrift) }}">
        </div>
        <div class="feld ba-voll">
            <label>Haushaltshilfe</label>
            <textarea name="haushaltshilfe" rows="2">{{ old('haushaltshilfe', $analyse->haushaltshilfe) }}</textarea>
        </div>
        <div class="feld ba-voll">
            <label>Bemerkungen Versicherung</label>
            <textarea name="versicherung_bemerkungen" rows="2">{{ old('versicherung_bemerkungen', $analyse->versicherung_bemerkungen) }}</textarea>
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Aufnahme & Einstufung</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Aufnahmegrund</label>
            <div style="display:flex; gap:1.5rem; margin-top:0.25rem;">
                @foreach(['krankheit' => 'Krankheit', 'unfall' => 'Unfall'] as $val => $label)
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="aufnahmegrund" value="{{ $val }}"
                           @checked(old('aufnahmegrund', $analyse->aufnahmegrund) === $val)>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="feld">
            <label>Hilflosenentschädigung</label>
            <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:0.25rem;">
                @foreach(['' => 'Keine', 'leicht' => 'Leicht', 'mittel' => 'Mittel', 'schwer' => 'Schwer'] as $val => $label)
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="hilflosenentschaedigung" value="{{ $val }}"
                           @checked(old('hilflosenentschaedigung', $analyse->hilflosenentschaedigung ?? '') === $val)>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="feld ba-voll">
            <label>Rechnungsadresse</label>
            <textarea name="rechnungsadresse" rows="2" placeholder="Falls abweichend von Wohnadresse">{{ old('rechnungsadresse', $analyse->rechnungsadresse) }}</textarea>
        </div>
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="vorauszahlung" id="vorauszahlung" value="1"
                   @checked(old('vorauszahlung', $analyse->vorauszahlung))>
            <label for="vorauszahlung" style="margin:0; font-weight:500;">Vorauszahlung per Anfang Monat</label>
        </div>
        <div class="feld">
            <label>Zuständiger Arzt</label>
            <input type="text" name="zustaendiger_arzt" value="{{ old('zustaendiger_arzt', $analyse->zustaendiger_arzt) }}" placeholder="Name des Hausarztes">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Haushalt & Person</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Anzahl Personen im Haushalt</label>
            <input type="number" name="personen_haushalt" value="{{ old('personen_haushalt', $analyse->personen_haushalt) }}" min="1" max="20">
        </div>
        <div class="feld">
            <label>Davon betreuungsbedürftig</label>
            <input type="number" name="personen_betreuungsbed" value="{{ old('personen_betreuungsbed', $analyse->personen_betreuungsbed) }}" min="0" max="20">
        </div>
        <div class="feld">
            <label>Gewicht (kg)</label>
            <input type="number" name="gewicht_kg" step="0.1" value="{{ old('gewicht_kg', $analyse->gewicht_kg) }}" min="20" max="300">
        </div>
    </div>
</div>

</div>
