<x-layouts.app :titel="'Einsatzart bearbeiten'">
<div style="max-width: 560px;">
    <a href="{{ route('einsatzarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Einsatzart bearbeiten</div>

        <form method="POST" action="{{ route('einsatzarten.update', $einsatzart) }}">
            @csrf @method('PUT')

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld"
                    value="{{ old('bezeichnung', $einsatzart->bezeichnung) }}" required>
                @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Leistungsart</label>
                <select name="leistungsart_id" class="feld" required>
                    @foreach($leistungsarten as $la)
                        <option value="{{ $la->id }}" {{ old('leistungsart_id', $einsatzart->leistungsart_id) == $la->id ? 'selected' : '' }}>
                            {{ $la->bezeichnung }}
                        </option>
                    @endforeach
                </select>
                @error('leistungsart_id') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div style="flex: 1;">
                    <label class="feld-label">Gültig ab</label>
                    <input type="date" name="gueltig_ab" class="feld"
                        value="{{ old('gueltig_ab', $einsatzart->gueltig_ab?->format('Y-m-d')) }}">
                </div>
                <div style="flex: 1;">
                    <label class="feld-label">Gültig bis</label>
                    <input type="date" name="gueltig_bis" class="feld"
                        value="{{ old('gueltig_bis', $einsatzart->gueltig_bis?->format('Y-m-d')) }}">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="aktiv" value="1"
                        {{ old('aktiv', $einsatzart->aktiv) ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Aktiv
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('einsatzarten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>
