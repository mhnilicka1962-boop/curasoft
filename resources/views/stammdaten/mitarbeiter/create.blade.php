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

<div class="karte" style="margin-bottom:1.25rem;">
    <div class="abschnitt-label">Stammdaten</div>
    <form method="POST" action="{{ route('mitarbeiter.store') }}">
        @csrf

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
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
                <label class="feld-label">Name *</label>
                <input type="text" name="nachname" class="feld" required value="{{ old('nachname') }}">
                @error('nachname')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="feld-label">Geburtsdatum</label>
                <input type="date" name="geburtsdatum" class="feld" value="{{ old('geburtsdatum') }}">
            </div>
            <div>
                <label class="feld-label">Nationalität</label>
                <input type="text" name="nationalitaet" class="feld" value="{{ old('nationalitaet') }}" placeholder="CH">
            </div>
            <div>
                <label class="feld-label">Zivilstand</label>
                <select name="zivilstand" class="feld">
                    <option value="">—</option>
                    @foreach(['Ledig','Verheiratet','Geschieden','Verwitwet','Eingetragene Partnerschaft'] as $zs)
                        <option value="{{ $zs }}" {{ old('zivilstand') === $zs ? 'selected' : '' }}>{{ $zs }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
            <div style="grid-column:span 2;">
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
            <div>
                <label class="feld-label">Telefon</label>
                <input type="text" name="telefon" class="feld" value="{{ old('telefon') }}">
            </div>
            <div>
                <label class="feld-label">Telefax</label>
                <input type="text" name="telefax" class="feld" value="{{ old('telefax') }}">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <label class="feld-label">E-Mail (Login) *</label>
                <input type="email" name="email" class="feld" required value="{{ old('email') }}">
                @error('email')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="feld-label">E-Mail privat</label>
                <input type="email" name="email_privat" class="feld" value="{{ old('email_privat') }}">
            </div>
            <div>
                <label class="feld-label">AHV-Nr.</label>
                <input type="text" name="ahv_nr" class="feld" value="{{ old('ahv_nr') }}" placeholder="756.XXXX.XXXX.XX">
            </div>
            <div>
                <label class="feld-label">GLN (NAREG, 13-stellig)</label>
                <input type="text" name="gln" class="feld" value="{{ old('gln') }}" placeholder="7601003XXXXXX" maxlength="13">
                <div class="text-mini text-hell" style="margin-top:0.2rem;">Aus NAREG-Register — Pflicht für XML-Abrechnung</div>
            </div>
            <div>
                <label class="feld-label">NAREG-Nr.</label>
                <input type="text" name="nareg_nr" class="feld" value="{{ old('nareg_nr') }}" placeholder="80012345">
                <div class="text-mini text-hell" style="margin-top:0.2rem;">Registernummer auf nareg.admin.ch</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <label class="feld-label">Bank</label>
                <input type="text" name="bank" class="feld" value="{{ old('bank') }}">
            </div>
            <div>
                <label class="feld-label">IBAN</label>
                <input type="text" name="iban" class="feld" value="{{ old('iban') }}" placeholder="CH00 0000 0000 0000 0000 0">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <label class="feld-label">Pensum %</label>
                <input type="number" name="pensum" class="feld" min="0" max="100" value="{{ old('pensum', 100) }}">
            </div>
            <div>
                <label class="feld-label">Eintritt</label>
                <input type="date" name="eintrittsdatum" class="feld" value="{{ old('eintrittsdatum') }}">
            </div>
            <div>
                <label class="feld-label">Austritt</label>
                <input type="date" name="austrittsdatum" class="feld" value="{{ old('austrittsdatum') }}">
            </div>
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
        </div>

        <div style="margin-bottom:0.75rem;">
            <label class="feld-label">Notizen</label>
            <textarea name="notizen" class="feld" rows="3" style="resize:vertical;">{{ old('notizen') }}</textarea>
        </div>

        <div style="display:flex; gap:0.75rem; margin-top:1rem;">
            <button type="submit" class="btn btn-primaer">Speichern & Einladen</button>
            <a href="{{ route('mitarbeiter.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>
</div>

</x-layouts.app>
