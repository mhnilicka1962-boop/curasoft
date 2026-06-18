<div class="karte">

<div class="ba-sektion">
    <h3>Diagnosen</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll">
            <label>Diagnosen</label>
            <textarea name="diagnosen_text" rows="4" placeholder="Alle relevanten Diagnosen…">{{ old('diagnosen_text', $analyse->diagnosen_text) }}</textarea>
        </div>
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="medikamente_liste" id="medikamente_liste" value="1"
                   @checked(old('medikamente_liste', $analyse->medikamente_liste))>
            <label for="medikamente_liste" style="margin:0; font-weight:500;">Separate Medikamentenliste vorhanden (gem. Hausarzt)</label>
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Fähigkeiten & Interessen</h3>
    <div class="ba-form-grid">
        <div class="feld ba-voll">
            <label>Mobilität</label>
            <textarea name="mobilitaet" rows="3" placeholder="Beschreibung der Mobilität, Einschränkungen…">{{ old('mobilitaet', $analyse->mobilitaet) }}</textarea>
        </div>
        <div class="feld ba-voll">
            <label>Hilfsmittel</label>
            <textarea name="hilfsmittel" rows="2" placeholder="z.B. Rollator, Rollstuhl, Hörgerät…">{{ old('hilfsmittel', $analyse->hilfsmittel) }}</textarea>
        </div>
        <div class="feld ba-voll">
            <label>Hobbies und Interessen</label>
            <textarea name="hobbies" rows="2" placeholder="z.B. Lesen, Gartenarbeit, Musik…">{{ old('hobbies', $analyse->hobbies) }}</textarea>
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Einschätzung der Pflegestufe</h3>
    <div class="ba-radio-gruppe">
        <label class="ba-radio-option">
            <input type="radio" name="pflegestufe" value="selbstaendig"
                   @checked(old('pflegestufe', $analyse->pflegestufe) === 'selbstaendig')>
            <span class="ba-radio-label">
                <strong>Selbstständig</strong>
                Kein oder minimaler Unterstützungsbedarf.
            </span>
        </label>
        <label class="ba-radio-option">
            <input type="radio" name="pflegestufe" value="erheblich"
                   @checked(old('pflegestufe', $analyse->pflegestufe) === 'erheblich')>
            <span class="ba-radio-label">
                <strong>Erheblich pflegebedürftig</strong>
                Hilfe beim Waschen Intimbereich, Hosen/Strümpfe anziehen schwierig, einmal wöchentlich Baden/Duschen.
            </span>
        </label>
        <label class="ba-radio-option">
            <input type="radio" name="pflegestufe" value="schwer"
                   @checked(old('pflegestufe', $analyse->pflegestufe) === 'schwer')>
            <span class="ba-radio-label">
                <strong>Schwerpflegebedürftig</strong>
                Anleitung beim Waschen Gesicht/Hände/Intimbereich, teils vollständige Übernahme, Zahnpflege/Kämmen/Ankleiden, Essen mundgerecht vorbereitet, Trinken auffordern, Hilfe beim Duschen/Haare waschen.
            </span>
        </label>
        <label class="ba-radio-option">
            <input type="radio" name="pflegestufe" value="schwerst"
                   @checked(old('pflegestufe', $analyse->pflegestufe) === 'schwerst')>
            <span class="ba-radio-label">
                <strong>Schwerstpflegebedürftig</strong>
                Vollständige Übernahme aller Pflegehandlungen, mehrmals täglich Toilette, nachts 1–2×, Intimpflege, Dekubitusprophylaxe nachts, Begleitung bei allen Gängen in der Wohnung.
            </span>
        </label>
    </div>
</div>

</div>
