<div style="display: grid; grid-template-columns: 120px 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">Anrede</label>
        <select name="anrede" class="feld">
            <option value="">—</option>
            @foreach(['Dr. med.' => 'Dr. med.', 'Dr. med. dent.' => 'Dr. med. dent.', 'Prof. Dr.' => 'Prof. Dr.', 'Herr' => 'Herr', 'Frau' => 'Frau'] as $val => $lbl)
                <option value="{{ $val }}" {{ old('anrede', $arzt->anrede ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="feld-label">Vorname</label>
        <input type="text" name="vorname" class="feld" value="{{ old('vorname', $arzt->vorname ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Nachname *</label>
        <input type="text" name="nachname" class="feld" required value="{{ old('nachname', $arzt->nachname ?? '') }}">
        @error('nachname')<div class="feld-fehler">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">Praxis / Klinik</label>
        <input type="text" name="praxis_name" class="feld" value="{{ old('praxis_name', $arzt->praxis_name ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Fachrichtung</label>
        <input type="text" name="fachrichtung" class="feld" placeholder="z.B. Innere Medizin" value="{{ old('fachrichtung', $arzt->fachrichtung ?? '') }}">
    </div>
</div>

<div style="margin-bottom: 0.75rem;">
    <label class="feld-label">Strasse & Nr.</label>
    <input type="text" name="adresse" class="feld" value="{{ old('adresse', $arzt->adresse ?? '') }}">
</div>

<div style="display: grid; grid-template-columns: 100px 1fr 100px; gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">PLZ</label>
        <input type="text" name="plz" class="feld" value="{{ old('plz', $arzt->plz ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Ort</label>
        <input type="text" name="ort" class="feld" value="{{ old('ort', $arzt->ort ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Kanton</label>
        <select name="region_id" class="feld">
            <option value="">—</option>
            @foreach($regionen as $r)
                <option value="{{ $r->id }}" {{ old('region_id', $arzt->region_id ?? '') == $r->id ? 'selected' : '' }}>{{ $r->kuerzel }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-grid-3" style="gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">Telefon</label>
        <input type="text" name="telefon" class="feld" value="{{ old('telefon', $arzt->telefon ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Fax</label>
        <input type="text" name="fax" class="feld" value="{{ old('fax', $arzt->fax ?? '') }}">
    </div>
    <div>
        <label class="feld-label">E-Mail</label>
        <input type="email" name="email" class="feld" value="{{ old('email', $arzt->email ?? '') }}">
    </div>
</div>

<div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">ZSR-Nummer</label>
        <input type="text" name="zsr_nr" class="feld" placeholder="A000000" value="{{ old('zsr_nr', $arzt->zsr_nr ?? '') }}">
        <div class="text-mini text-hell" style="margin-top: 0.25rem;">Zahlstellenregister-Nummer</div>
    </div>
    <div>
        <label class="feld-label">GLN-Nummer</label>
        <input type="text" name="gln_nr" class="feld" placeholder="7601000000000" value="{{ old('gln_nr', $arzt->gln_nr ?? '') }}">
        <div class="text-mini text-hell" style="margin-top: 0.25rem;">Global Location Number (EAN)</div>
    </div>
</div>

@isset($arzt)
<div style="margin-bottom: 0.75rem;">
    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
        <input type="hidden" name="aktiv" value="0">
        <input type="checkbox" name="aktiv" value="1" {{ old('aktiv', $arzt->aktiv) ? 'checked' : '' }}>
        Aktiv
    </label>
</div>
@endisset
