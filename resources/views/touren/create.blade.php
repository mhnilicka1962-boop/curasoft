<x-layouts.app titel="Neue Tour">
<div style="max-width: 600px;">

    <a href="{{ route('touren.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Touren</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">Neue Tour</h1>

    <div class="karte">
        <form method="POST" action="{{ route('touren.store') }}">
            @csrf

            <div style="margin-bottom: 0.75rem;">
                <label class="feld-label">Bezeichnung *</label>
                <input type="text" name="bezeichnung" class="feld" required
                    placeholder="z.B. Morgenrunde, Nachmittag West"
                    value="{{ old('bezeichnung') }}">
                @error('bezeichnung')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>

            <div class="form-grid-2" style="margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label">Mitarbeiter *</label>
                    <select name="benutzer_id" class="feld" required>
                        <option value="">— wählen —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>{{ $m->vorname }} {{ $m->nachname }}</option>
                        @endforeach
                    </select>
                    @error('benutzer_id')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Datum *</label>
                    <input type="date" name="datum" class="feld" required value="{{ old('datum', date('Y-m-d')) }}">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="feld-label">Startzeit (geplant)</label>
                <input type="time" name="start_zeit" class="feld" style="max-width: 160px;" value="{{ old('start_zeit') }}">
            </div>

            <div class="abschnitt-trenn" style="margin-top: 0; padding-top: 1rem;">
                <button type="submit" class="btn btn-primaer">Tour erstellen</button>
                <a href="{{ route('touren.index') }}" class="btn btn-sekundaer" style="margin-left: 0.5rem;">Abbrechen</a>
            </div>
        </form>
    </div>

</div>
</x-layouts.app>
