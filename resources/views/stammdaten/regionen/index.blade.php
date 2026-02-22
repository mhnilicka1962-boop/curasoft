<x-layouts.app :titel="'Regionen / Kantone'">
<div style="max-width: 700px;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
        <div>
            <a href="{{ route('leistungsarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Leistungsarten</a>
            <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0 0;">Regionen / Kantone</h1>
        </div>
    </div>

    @if($errors->has('fehler'))
    <div style="background: var(--cs-fehler-hell, #fff0f0); border: 1px solid var(--cs-fehler); border-radius: var(--cs-radius); padding: 0.75rem 1rem; margin-bottom: 1rem; color: var(--cs-fehler); font-size: 0.875rem;">
        {{ $errors->first('fehler') }}
    </div>
    @endif

    {{-- Bestehende Regionen --}}
    <div class="karte-null" style="margin-bottom: 1.5rem;">
        <table class="tabelle">
            <thead>
                <tr>
                    <th style="width: 80px;">Kürzel</th>
                    <th>Bezeichnung</th>
                    <th class="text-rechts">Tarife</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($regionen as $region)
                <tr>
                    <td>
                        <a href="{{ route('regionen.show', $region) }}"
                            class="link-primaer" style="font-weight: 700; font-family: monospace; font-size: 1rem;">
                            {{ $region->kuerzel }}
                        </a>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('regionen.update', $region) }}" style="display: flex; gap: 0.5rem; align-items: center;">
                            @csrf @method('PUT')
                            <input type="text" name="bezeichnung" value="{{ $region->bezeichnung }}"
                                style="border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.25rem 0.5rem; font-size: 0.875rem; flex: 1; min-width: 180px;">
                            <button type="submit" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Speichern</button>
                        </form>
                    </td>
                    <td class="text-rechts">
                        @if($region->tarife_count > 0)
                            <a href="{{ route('regionen.show', $region) }}"
                                class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;"
                                title="Leistungsarten bearbeiten">
                                ✏ Leistungsarten
                            </a>
                        @else
                            <span class="text-klein text-hell">0</span>
                        @endif
                    </td>
                    <td class="text-rechts">
                        @if($region->tarife_count === 0)
                        <form method="POST" action="{{ route('regionen.destroy', $region) }}" onsubmit="return confirm('Region «{{ $region->kuerzel }}» löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-gefahr" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✕</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-mitte text-hell" style="padding: 2rem;">
                        Noch keine Regionen erfasst.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Neue Region --}}
    <div class="karte">
        <div class="abschnitt-label">Neuer Kanton / Region</div>
        <form method="POST" action="{{ route('regionen.store') }}" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <div>
                <label class="feld-label">Kürzel (max. 4 Zeichen)</label>
                <input type="text" name="kuerzel" class="feld" style="width: 90px; text-transform: uppercase;" maxlength="4" placeholder="AG" value="{{ old('kuerzel') }}" required>
                @error('kuerzel') <div style="color: var(--cs-fehler); font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld" placeholder="Kanton Aargau" value="{{ old('bezeichnung') }}" required>
                @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.75rem;">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primaer">Hinzufügen</button>
        </form>

        {{-- Schweizer Kantone Schnelleingabe --}}
        <div class="abschnitt-trenn" style="margin-top: 1rem; padding-top: 1rem;">
            <div class="text-mini text-hell" style="margin-bottom: 0.5rem;">Schnelleingabe Kantone:</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
                @foreach(['AG' => 'Aargau', 'AI' => 'Appenzell Innerrhoden', 'AR' => 'Appenzell Ausserrhoden', 'BE' => 'Bern', 'BL' => 'Basel-Landschaft', 'BS' => 'Basel-Stadt', 'FR' => 'Freiburg', 'GE' => 'Genf', 'GL' => 'Glarus', 'GR' => 'Graubünden', 'JU' => 'Jura', 'LU' => 'Luzern', 'NE' => 'Neuenburg', 'NW' => 'Nidwalden', 'OW' => 'Obwalden', 'SG' => 'St. Gallen', 'SH' => 'Schaffhausen', 'SO' => 'Solothurn', 'SZ' => 'Schwyz', 'TG' => 'Thurgau', 'TI' => 'Tessin', 'UR' => 'Uri', 'VD' => 'Waadt', 'VS' => 'Wallis', 'ZG' => 'Zug', 'ZH' => 'Zürich'] as $kuerzel => $name)
                @php $existiert = $regionen->contains('kuerzel', $kuerzel); @endphp
                @if(!$existiert)
                <form method="POST" action="{{ route('regionen.store') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="kuerzel" value="{{ $kuerzel }}">
                    <input type="hidden" name="bezeichnung" value="{{ $name }}">
                    <button type="submit" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">+ {{ $kuerzel }}</button>
                </form>
                @else
                <span style="padding: 0.2rem 0.5rem; font-size: 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); color: var(--cs-text-hell);">✓ {{ $kuerzel }}</span>
                @endif
                @endforeach
            </div>
        </div>
    </div>

</div>
</x-layouts.app>
