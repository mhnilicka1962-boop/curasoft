<div class="karte">

<div class="ba-sektion">
    <h3>Aufnahme</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Datum der Bedarfsanalyse</label>
            <input type="date" name="datum_analyse" value="{{ old('datum_analyse', $analyse->datum_analyse?->format('Y-m-d')) }}">
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort_analyse" value="{{ old('ort_analyse', $analyse->ort_analyse) }}" placeholder="z.B. Zürich">
        </div>
    </div>
</div>

<div class="ba-sektion">
    <h3>Betreuungsperson</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Anrede</label>
            <select name="anrede">
                <option value="">— bitte wählen —</option>
                @foreach(['Herr','Frau','Divers'] as $opt)
                <option value="{{ $opt }}" @selected(old('anrede', $analyse->anrede) === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div class="feld">
            <label>Vorname</label>
            <input type="text" name="vorname" value="{{ old('vorname', $analyse->vorname) }}">
        </div>
        <div class="feld">
            <label>Nachname</label>
            <input type="text" name="nachname" value="{{ old('nachname', $analyse->nachname) }}">
        </div>
        <div class="feld">
            <label>Geburtsdatum</label>
            <input type="date" name="geburtsdatum" value="{{ old('geburtsdatum', $analyse->geburtsdatum?->format('Y-m-d')) }}">
        </div>
        <div class="feld ba-voll">
            <label>Strasse / Nr.</label>
            <input type="text" name="strasse" value="{{ old('strasse', $analyse->strasse) }}" placeholder="Musterstrasse 12">
        </div>
        <div class="feld">
            <label>PLZ</label>
            <input type="text" name="plz" value="{{ old('plz', $analyse->plz) }}" maxlength="10">
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort" value="{{ old('ort', $analyse->ort) }}">
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="tel" name="telefon" value="{{ old('telefon', $analyse->telefon) }}">
        </div>
        <div class="feld">
            <label>Mobile</label>
            <input type="tel" name="mobile" value="{{ old('mobile', $analyse->mobile) }}">
        </div>
        <div class="feld">
            <label>Heimatort</label>
            <input type="text" name="heimatort" value="{{ old('heimatort', $analyse->heimatort) }}">
        </div>
        <div class="feld">
            <label>Konfession</label>
            <input type="text" name="konfession" value="{{ old('konfession', $analyse->konfession) }}" placeholder="z.B. Reformiert">
        </div>
        <div class="feld">
            <label>Zivilstand</label>
            <select name="zivilstand">
                <option value="">— bitte wählen —</option>
                @foreach(['ledig','verheiratet','verwitwet','geschieden','getrennt','eingetragene Partnerschaft'] as $opt)
                <option value="{{ $opt }}" @selected(old('zivilstand', $analyse->zivilstand) === $opt)>{{ ucfirst($opt) }}</option>
                @endforeach
            </select>
        </div>
        <div class="feld">
            <label>Nationalität</label>
            <input type="text" name="nationalitaet" value="{{ old('nationalitaet', $analyse->nationalitaet) }}" placeholder="z.B. Schweiz">
        </div>
        <div class="feld">
            <label>AHV / SVN-Nr.</label>
            <input type="text" name="ahv_nr" value="{{ old('ahv_nr', $analyse->ahv_nr) }}" placeholder="756.xxxx.xxxx.xx">
        </div>
    </div>
</div>

@foreach([
    ['prefix' => 'ap1', 'titel' => 'Ansprechperson 1', 'beziehungen' => ['Ehepartner','Sohn-Tochter','Verwandschaft']],
    ['prefix' => 'ap2', 'titel' => 'Ansprechperson 2', 'beziehungen' => ['Sohn-Tochter','Verwandschaft','Bekannte']],
] as $ap)
@php $p = $ap['prefix']; @endphp
<div class="ba-sektion">
    <h3>{{ $ap['titel'] }}</h3>
    <div class="ba-form-grid">
        <div class="feld">
            <label>Vorname</label>
            <input type="text" name="{{ $p }}_vorname" value="{{ old("{$p}_vorname", $analyse->{"{$p}_vorname"}) }}">
        </div>
        <div class="feld">
            <label>Name</label>
            <input type="text" name="{{ $p }}_name" value="{{ old("{$p}_name", $analyse->{"{$p}_name"}) }}">
        </div>
        <div class="feld ba-voll">
            <label>Strasse / Nr.</label>
            <input type="text" name="{{ $p }}_strasse" value="{{ old("{$p}_strasse", $analyse->{"{$p}_strasse"}) }}">
        </div>
        <div class="feld">
            <label>PLZ</label>
            <input type="text" name="{{ $p }}_plz" value="{{ old("{$p}_plz", $analyse->{"{$p}_plz"}) }}" maxlength="10">
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="{{ $p }}_ort" value="{{ old("{$p}_ort", $analyse->{"{$p}_ort"}) }}">
        </div>
        <div class="feld">
            <label>Beziehung</label>
            <select name="{{ $p }}_beziehung">
                <option value="">— bitte wählen —</option>
                @foreach($ap['beziehungen'] as $bez)
                <option value="{{ $bez }}" @selected(old("{$p}_beziehung", $analyse->{"{$p}_beziehung"}) === $bez)>{{ $bez }}</option>
                @endforeach
            </select>
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="tel" name="{{ $p }}_telefon" value="{{ old("{$p}_telefon", $analyse->{"{$p}_telefon"}) }}">
        </div>
        <div class="feld">
            <label>Mobile</label>
            <input type="tel" name="{{ $p }}_mobile" value="{{ old("{$p}_mobile", $analyse->{"{$p}_mobile"}) }}">
        </div>
        <div class="feld ba-voll">
            <label>Bemerkung</label>
            <textarea name="{{ $p }}_bemerkung" rows="2">{{ old("{$p}_bemerkung", $analyse->{"{$p}_bemerkung"}) }}</textarea>
        </div>
        <div class="feld ba-voll" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="{{ $p }}_vormund" id="{{ $p }}_vormund" value="1"
                   @checked(old("{$p}_vormund", $analyse->{"{$p}_vormund"}))>
            <label for="{{ $p }}_vormund" style="margin:0; font-weight:500;">Vormund / Beistand</label>
        </div>
        <div class="feld ba-voll">
            <label>Erreichbarkeit</label>
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin-top:0.25rem;">
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="{{ $p }}_erreichbarkeit" value="24h"
                           @checked(old("{$p}_erreichbarkeit", $analyse->{"{$p}_erreichbarkeit"}) === '24h')
                           onchange="toggleErreichbarkeit('{{ $p }}')">
                    24 Stunden
                </label>
                <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400;">
                    <input type="radio" name="{{ $p }}_erreichbarkeit" value="tagsueber"
                           @checked(old("{$p}_erreichbarkeit", $analyse->{"{$p}_erreichbarkeit"}) === 'tagsueber')
                           onchange="toggleErreichbarkeit('{{ $p }}')">
                    Tagsüber
                </label>
            </div>
        </div>
        <div class="feld" id="{{ $p }}_zeiten"
             style="{{ old("{$p}_erreichbarkeit", $analyse->{"{$p}_erreichbarkeit"}) === 'tagsueber' ? '' : 'display:none;' }}">
            <label>Von</label>
            <input type="time" name="{{ $p }}_erreichbarkeit_von"
                   value="{{ old("{$p}_erreichbarkeit_von", $analyse->{"{$p}_erreichbarkeit_von"}) }}">
        </div>
        <div class="feld" id="{{ $p }}_zeiten_bis"
             style="{{ old("{$p}_erreichbarkeit", $analyse->{"{$p}_erreichbarkeit"}) === 'tagsueber' ? '' : 'display:none;' }}">
            <label>Bis</label>
            <input type="time" name="{{ $p }}_erreichbarkeit_bis"
                   value="{{ old("{$p}_erreichbarkeit_bis", $analyse->{"{$p}_erreichbarkeit_bis"}) }}">
        </div>
    </div>
</div>
@endforeach

</div>

<script>
function toggleErreichbarkeit(prefix) {
    const val = document.querySelector(`input[name="${prefix}_erreichbarkeit"]:checked`)?.value;
    const show = val === 'tagsueber';
    document.getElementById(prefix + '_zeiten').style.display = show ? '' : 'none';
    document.getElementById(prefix + '_zeiten_bis').style.display = show ? '' : 'none';
}
</script>
