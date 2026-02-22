<x-layouts.app :titel="'Neue Leistungsart'">
<div style="max-width: 560px;">
    <a href="{{ route('leistungsarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    <div class="karte">
        <div class="abschnitt-label">Neue Leistungsart</div>

        <form method="POST" action="{{ route('leistungsarten.store') }}">
            @csrf

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld" value="{{ old('bezeichnung') }}" required placeholder="z.B. Grundpflege">
                @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Einheit</label>
                <select name="einheit" class="feld" required>
                    <option value="minuten" {{ old('einheit') === 'minuten' ? 'selected' : '' }}>Minuten (Abrechnung pro Minute)</option>
                    <option value="stunden" {{ old('einheit') === 'stunden' ? 'selected' : '' }}>Stunden (Abrechnung pro Stunde)</option>
                    <option value="tage"    {{ old('einheit') === 'tage'    ? 'selected' : '' }}>Tage (Tagespauschale)</option>
                </select>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="kassenpflichtig" value="1" {{ old('kassenpflichtig', '1') ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Kassenpflichtig (KVG)
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Erstellen</button>
                <a href="{{ route('leistungsarten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>
