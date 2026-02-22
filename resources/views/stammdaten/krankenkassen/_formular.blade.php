<div style="display: grid; grid-template-columns: 1fr 160px; gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">Name *</label>
        <input type="text" name="name" class="feld" required value="{{ old('name', $krankenkasse->name ?? '') }}">
        @error('name')<div class="feld-fehler">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="feld-label">Kürzel</label>
        <input type="text" name="kuerzel" class="feld" placeholder="z.B. CSS" value="{{ old('kuerzel', $krankenkasse->kuerzel ?? '') }}">
    </div>
</div>

<div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">EAN-Nummer</label>
        <input type="text" name="ean_nr" class="feld" placeholder="7610000000000" value="{{ old('ean_nr', $krankenkasse->ean_nr ?? '') }}">
        <div class="text-mini text-hell" style="margin-top: 0.25rem;">Für XML-Abrechnung</div>
    </div>
    <div>
        <label class="feld-label">BAG-Nummer</label>
        <input type="text" name="bag_nr" class="feld" value="{{ old('bag_nr', $krankenkasse->bag_nr ?? '') }}">
        <div class="text-mini text-hell" style="margin-top: 0.25rem;">Bundesamt für Gesundheit</div>
    </div>
</div>

<div style="margin-bottom: 0.75rem;">
    <label class="feld-label">Adresse</label>
    <input type="text" name="adresse" class="feld" value="{{ old('adresse', $krankenkasse->adresse ?? '') }}">
</div>

<div style="display: grid; grid-template-columns: 100px 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">PLZ</label>
        <input type="text" name="plz" class="feld" value="{{ old('plz', $krankenkasse->plz ?? '') }}">
    </div>
    <div>
        <label class="feld-label">Ort</label>
        <input type="text" name="ort" class="feld" value="{{ old('ort', $krankenkasse->ort ?? '') }}">
    </div>
</div>

<div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
    <div>
        <label class="feld-label">Telefon</label>
        <input type="text" name="telefon" class="feld" value="{{ old('telefon', $krankenkasse->telefon ?? '') }}">
    </div>
    <div>
        <label class="feld-label">E-Mail</label>
        <input type="email" name="email" class="feld" value="{{ old('email', $krankenkasse->email ?? '') }}">
    </div>
</div>

@isset($krankenkasse)
<div style="margin-bottom: 0.75rem;">
    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
        <input type="hidden" name="aktiv" value="0">
        <input type="checkbox" name="aktiv" value="1" {{ old('aktiv', $krankenkasse->aktiv) ? 'checked' : '' }}>
        Aktiv
    </label>
</div>
@endisset
