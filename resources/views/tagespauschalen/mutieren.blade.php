<x-layouts.app :titel="'Tagespauschale mutieren'">
<div style="max-width: 560px;">
    <a href="{{ route('tagespauschalen.show', $tagespauschale) }}"
       class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    @if(session('fehler'))
    <div class="flash flash-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Aktuelle Tagespauschale</div>
        <div style="font-size: 0.875rem; color: var(--cs-text-hell);">
            {{ $tagespauschale->klient->nachname }} {{ $tagespauschale->klient->vorname }}
            · {{ $tagespauschale->datum_von->format('d.m.Y') }} – {{ $tagespauschale->datum_bis->format('d.m.Y') }}
            · CHF {{ number_format($tagespauschale->ansatz, 2, '.', "'") }}/Tag
        </div>
    </div>

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Änderung ab Datum</div>

        <form method="POST" action="{{ route('tagespauschalen.mutieren', $tagespauschale) }}">
            @csrf

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Änderung gültig ab</label>
                <input type="date" name="gueltig_ab" id="gueltig_ab" class="feld"
                    min="{{ $tagespauschale->datum_von->addDay()->format('Y-m-d') }}"
                    max="{{ $tagespauschale->datum_bis->format('Y-m-d') }}"
                    value="{{ old('gueltig_ab') }}" required>
                <div class="text-hell" style="font-size: 0.75rem; margin-top: 0.25rem;">
                    Unverrechnete Einsätze ab diesem Datum werden gelöscht und neu generiert.
                </div>
                @error('gueltig_ab') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Neue Gültigkeit bis</label>
                <input type="date" name="datum_bis" class="feld"
                    value="{{ old('datum_bis', $tagespauschale->datum_bis->format('Y-m-d')) }}" required>
                @error('datum_bis') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Rechnungstyp</label>
                    <select name="rechnungstyp" class="feld" required>
                        <option value="kvg"      {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'kvg'      ? 'selected' : '' }}>KVG → Krankenkasse</option>
                        <option value="klient"   {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'klient'   ? 'selected' : '' }}>Klient (Selbstbehalt)</option>
                        <option value="gemeinde" {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'gemeinde' ? 'selected' : '' }}>Gemeinde / Kanton</option>
                    </select>
                    @error('rechnungstyp') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">Neuer Ansatz (CHF/Tag)</label>
                    <input type="number" name="ansatz" class="feld"
                        step="0.05" min="0"
                        value="{{ old('ansatz', $tagespauschale->ansatz) }}" required>
                    @error('ansatz') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Text auf Rechnung</label>
                <input type="text" name="text" class="feld" maxlength="500"
                    value="{{ old('text', $tagespauschale->text) }}">
                @error('text') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer"
                    onclick="return confirm('Tagespauschale ab diesem Datum mutieren?\n\nUnverrechnete Einsätze ab dem gewählten Datum werden gelöscht und neu generiert.')">
                    Mutation speichern
                </button>
                <a href="{{ route('tagespauschalen.show', $tagespauschale) }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>
