<x-layouts.app :titel="'Leistungsart bearbeiten'">
<div style="max-width: 640px;">
    <a href="{{ route('leistungsarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    <div class="karte">
        <div class="abschnitt-label">Leistungsart bearbeiten</div>

        <form method="POST" action="{{ route('leistungsarten.update', $leistungsart) }}">
            @csrf @method('PUT')

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
                <div style="flex: 2; min-width: 200px;">
                    <label class="feld-label">Bezeichnung</label>
                    <input type="text" name="bezeichnung" class="feld" value="{{ old('bezeichnung', $leistungsart->bezeichnung) }}" required>
                    @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="feld-label">Einheit</label>
                    <select name="einheit" class="feld" required>
                        <option value="stunden" {{ old('einheit', $leistungsart->einheit) === 'stunden' ? 'selected' : '' }}>Stunden</option>
                        <option value="minuten" {{ old('einheit', $leistungsart->einheit) === 'minuten' ? 'selected' : '' }}>Minuten</option>
                        <option value="tage"    {{ old('einheit', $leistungsart->einheit) === 'tage'    ? 'selected' : '' }}>Tage (Pauschale)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">Gültig ab</label>
                    <input type="date" name="gueltig_ab" class="feld" value="{{ old('gueltig_ab', $leistungsart->gueltig_ab?->format('Y-m-d')) }}">
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">Gültig bis</label>
                    <input type="date" name="gueltig_bis" class="feld" value="{{ old('gueltig_bis', $leistungsart->gueltig_bis?->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Default-Ansätze --}}
            <div class="abschnitt-label" style="margin: 1.25rem 0 0.75rem;">
                Startwerte (für neue Kantone)
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">Ansatz</label>
                    <input type="number" step="0.01" min="0" name="ansatz_default" class="feld"
                        value="{{ old('ansatz_default', $leistungsart->ansatz_default) }}">
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">KVG</label>
                    <input type="number" step="0.01" min="0" name="kvg_default" class="feld"
                        value="{{ old('kvg_default', $leistungsart->kvg_default) }}">
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">Ansatz akut</label>
                    <input type="number" step="0.01" min="0" name="ansatz_akut_default" class="feld"
                        value="{{ old('ansatz_akut_default', $leistungsart->ansatz_akut_default) }}">
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label class="feld-label">KVG akut</label>
                    <input type="number" step="0.01" min="0" name="kvg_akut_default" class="feld"
                        value="{{ old('kvg_akut_default', $leistungsart->kvg_akut_default) }}">
                </div>
            </div>

            {{-- TARMED-Code für XML 450.100 --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">TARMED-Code <span style="font-size: 0.75rem; font-weight: 400; color: var(--cs-text-hell);">(Tarif 311, XML 450.100)</span></label>
                <input type="text" name="tarmed_code" class="feld" maxlength="20"
                    style="max-width: 180px;"
                    placeholder="z.B. 00.0010"
                    value="{{ old('tarmed_code', $leistungsart->tarmed_code) }}">
                @error('tarmed_code') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            {{-- Status + KVG --}}
            <div style="display: flex; gap: 1.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="kassenpflichtig" value="1"
                        {{ old('kassenpflichtig', $leistungsart->kassenpflichtig) ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Kassenpflichtig (KVG)
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="aktiv" value="1"
                        {{ old('aktiv', $leistungsart->aktiv) ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Aktiv
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('leistungsarten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>
