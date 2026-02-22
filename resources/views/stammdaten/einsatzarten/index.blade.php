<x-layouts.app :titel="'Einsatzarten'">

<div class="seiten-kopf">
    <div>
        <div class="text-mini text-hell">Stammdaten</div>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Einsatzarten</h1>
    </div>
    <a href="{{ route('leistungsarten.index') }}" class="btn btn-sekundaer" style="font-size: 0.8125rem;">← Leistungsarten</a>
</div>

@if(session('erfolg'))
    <div class="erfolg-box" style="margin-bottom: 1rem; font-size: 0.875rem;">
        {{ session('erfolg') }}
    </div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('einsatzarten.index') }}" style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
    <select name="leistungsart_id" class="feld" style="width: 220px;">
        <option value="">Alle Leistungsarten</option>
        @foreach($leistungsarten as $la)
            <option value="{{ $la->id }}" {{ request('leistungsart_id') == $la->id ? 'selected' : '' }}>
                {{ $la->bezeichnung }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sekundaer">Filtern</button>
    @if(request('leistungsart_id'))
        <a href="{{ route('einsatzarten.index') }}" class="btn btn-sekundaer">✕</a>
    @endif
    <span class="text-klein text-hell" style="margin-left: auto; align-self: center;">
        {{ $einsatzarten->count() }} Einträge
    </span>
</form>

{{-- Tabelle --}}
<div class="karte-null" style="margin-bottom: 1.5rem;">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Einsatzart</th>
                <th>Leistungsart</th>
                <th>Gültig ab</th>
                <th>Gültig bis</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($einsatzarten as $ea)
            <tr style="{{ !$ea->aktiv ? 'opacity: 0.5;' : '' }}">
                <td class="text-mittel">{{ $ea->bezeichnung }}</td>
                <td>
                    <span class="badge badge-grau" style="font-size: 0.75rem;">{{ $ea->leistungsart?->bezeichnung ?? '—' }}</span>
                </td>
                <td class="text-klein text-hell">{{ $ea->gueltig_ab?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-klein text-hell">{{ $ea->gueltig_bis?->format('d.m.Y') ?? '—' }}</td>
                <td>
                    @if($ea->aktiv)
                        <span class="badge badge-erfolg">Aktiv</span>
                    @else
                        <span class="badge badge-fehler">Inaktiv</span>
                    @endif
                </td>
                <td class="text-rechts" style="white-space: nowrap;">
                    <a href="{{ route('einsatzarten.edit', $ea) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Bearbeiten</a>
                    <form method="POST" action="{{ route('einsatzarten.destroy', $ea) }}" style="display:inline;"
                        onsubmit="return confirm('«{{ $ea->bezeichnung }}» wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem; color: var(--cs-fehler);">Löschen</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-hell" style="text-align: center; padding: 2.5rem;">
                    Keine Einsatzarten gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Neue Einsatzart --}}
<div class="karte">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Neue Einsatzart</div>

    <form method="POST" action="{{ route('einsatzarten.store') }}">
        @csrf
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld" value="{{ old('bezeichnung') }}"
                    placeholder="z.B. Blutzucker" required>
                @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label class="feld-label">Leistungsart</label>
                <select name="leistungsart_id" class="feld" required>
                    <option value="">— auswählen —</option>
                    @foreach($leistungsarten as $la)
                        <option value="{{ $la->id }}" {{ old('leistungsart_id') == $la->id ? 'selected' : '' }}>
                            {{ $la->bezeichnung }}
                        </option>
                    @endforeach
                </select>
                @error('leistungsart_id') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label class="feld-label">Gültig ab</label>
                <input type="date" name="gueltig_ab" class="feld" value="{{ old('gueltig_ab') }}">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label class="feld-label">Gültig bis</label>
                <input type="date" name="gueltig_bis" class="feld" value="{{ old('gueltig_bis') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primaer">+ Hinzufügen</button>
            </div>
        </div>
    </form>
</div>

</x-layouts.app>
