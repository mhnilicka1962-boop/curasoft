<x-layouts.app titel="Neuer Mitarbeiter">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('mitarbeiter.index') }}" class="link-gedaempt" style="font-size:0.875rem;">← Mitarbeitende</a>
        <h1 style="font-size:1.25rem; font-weight:700; margin:0.25rem 0 0;">Neuer Mitarbeiter</h1>
    </div>
</div>

@if($errors->any())
<div class="fehler-box" style="margin-bottom:1rem;">
    <ul style="margin:0; padding-left:1.25rem;">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('mitarbeiter.store') }}">
@csrf

<div class="karte" style="margin-bottom:1rem;">
    <div class="abschnitt-label" style="margin-bottom:0.875rem;">Person</div>
    <div class="form-grid">
        <div>
            <label class="feld-label">Anrede</label>
            <select name="anrede" class="feld">
                <option value="">—</option>
                <option value="Herr" {{ old('anrede') === 'Herr' ? 'selected' : '' }}>Herr</option>
                <option value="Frau" {{ old('anrede') === 'Frau' ? 'selected' : '' }}>Frau</option>
            </select>
        </div>
        <div>
            <label class="feld-label">Geschlecht</label>
            <select name="geschlecht" class="feld">
                <option value="">—</option>
                <option value="m" {{ old('geschlecht') === 'm' ? 'selected' : '' }}>Männlich</option>
                <option value="f" {{ old('geschlecht') === 'f' ? 'selected' : '' }}>Weiblich</option>
                <option value="d" {{ old('geschlecht') === 'd' ? 'selected' : '' }}>Divers</option>
            </select>
        </div>
        <div>
            <label class="feld-label">Vorname *</label>
            <input type="text" name="vorname" class="feld" required value="{{ old('vorname') }}">
            @error('vorname')<div class="feld-fehler">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="feld-label">Nachname *</label>
            <input type="text" name="nachname" class="feld" required value="{{ old('nachname') }}">
            @error('nachname')<div class="feld-fehler">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="feld-label">Geburtsdatum</label>
            <input type="date" name="geburtsdatum" class="feld" value="{{ old('geburtsdatum') }}">
        </div>
        <div>
            <label class="feld-label">Telefon</label>
            <input type="text" name="telefon" class="feld" value="{{ old('telefon') }}">
        </div>
        <div>
            <label class="feld-label">E-Mail * <span class="text-hell text-klein">(Login)</span></label>
            <input type="email" name="email" class="feld" required value="{{ old('email') }}">
            @error('email')<div class="feld-fehler">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="karte" style="margin-bottom:1rem;">
    <div class="abschnitt-label" style="margin-bottom:0.875rem;">Adresse</div>
    <div class="form-grid">
        <div style="grid-column: span 2;">
            <label class="feld-label">Strasse</label>
            <input type="text" name="strasse" class="feld" value="{{ old('strasse') }}">
        </div>
        <div>
            <label class="feld-label">PLZ</label>
            <input type="text" name="plz" class="feld" value="{{ old('plz') }}">
        </div>
        <div>
            <label class="feld-label">Ort</label>
            <input type="text" name="ort" class="feld" value="{{ old('ort') }}">
        </div>
    </div>
</div>

<div class="karte" style="margin-bottom:1rem;">
    <div class="abschnitt-label" style="margin-bottom:0.875rem;">Anstellung</div>
    <div class="form-grid">
        <div>
            <label class="feld-label">Rolle *</label>
            <select name="rolle" class="feld" required>
                <option value="pflege"      {{ old('rolle', 'pflege') === 'pflege'      ? 'selected' : '' }}>Pflege</option>
                <option value="buchhaltung" {{ old('rolle') === 'buchhaltung'           ? 'selected' : '' }}>Buchhaltung</option>
                <option value="admin"       {{ old('rolle') === 'admin'                 ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div>
            <label class="feld-label">Anstellungsart</label>
            <select name="anstellungsart" class="feld">
                <option value="fachperson" {{ old('anstellungsart', 'fachperson') === 'fachperson' ? 'selected' : '' }}>Fachperson</option>
                <option value="angehoerig" {{ old('anstellungsart') === 'angehoerig'               ? 'selected' : '' }}>Pflegender Angehöriger</option>
                <option value="freiwillig" {{ old('anstellungsart') === 'freiwillig'               ? 'selected' : '' }}>Freiwillig</option>
                <option value="praktikum"  {{ old('anstellungsart') === 'praktikum'                ? 'selected' : '' }}>Praktikum</option>
            </select>
        </div>
        <div>
            <label class="feld-label">Pensum %</label>
            <input type="number" name="pensum" class="feld" min="0" max="100" value="{{ old('pensum', 100) }}">
        </div>
        <div>
            <label class="feld-label">Eintrittsdatum</label>
            <input type="date" name="eintrittsdatum" class="feld" value="{{ old('eintrittsdatum') }}">
        </div>
        <div>
            <label class="feld-label">AHV-Nummer</label>
            <input type="text" name="ahv_nr" class="feld" value="{{ old('ahv_nr') }}" placeholder="756.XXXX.XXXX.XX">
        </div>
        <div>
            <label class="feld-label">IBAN</label>
            <input type="text" name="iban" class="feld" value="{{ old('iban') }}" placeholder="CH00 0000 0000 0000 0000 0">
        </div>
        <div>
            <label class="feld-label">Bank</label>
            <input type="text" name="bank" class="feld" value="{{ old('bank') }}">
        </div>
    </div>
</div>

<div style="display:flex; gap:0.75rem;">
    <button type="submit" class="btn btn-primaer">Speichern & Einladen</button>
    <a href="{{ route('mitarbeiter.index') }}" class="btn btn-sekundaer">Abbrechen</a>
</div>

</form>

</x-layouts.app>
