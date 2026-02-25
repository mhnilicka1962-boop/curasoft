<x-layouts.app :titel="'Leistungsarten — Grundset'">

<div class="seiten-kopf">
    <div>
        <div class="text-mini text-hell">Stammdaten</div>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Leistungsarten — Grundset</h1>
    </div>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif

{{-- Tabelle --}}
<div class="karte-null" style="margin-bottom: 1.5rem;">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Bezeichnung</th>
                <th>Einheit</th>
                <th class="text-rechts">Ansatz</th>
                <th class="text-rechts">KVG</th>
                <th class="text-rechts">Ansatz akut</th>
                <th class="text-rechts">KVG akut</th>
                <th>Gültig ab</th>
                <th>Gültig bis</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($leistungsarten as $la)
            <tr style="{{ !$la->aktiv ? 'opacity: 0.5;' : '' }}">
                <td class="text-fett">{{ $la->bezeichnung }}</td>
                <td class="text-klein text-hell">{{ $la->einheitLabel() }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($la->ansatz_default, 2) }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($la->kvg_default, 2) }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($la->ansatz_akut_default, 2) }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($la->kvg_akut_default, 2) }}</td>
                <td class="text-klein text-hell">{{ $la->gueltig_ab?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-klein text-hell">{{ $la->gueltig_bis?->format('d.m.Y') ?? '—' }}</td>
                <td>
                    @if($la->aktiv)
                        <span class="badge badge-erfolg">Aktiv</span>
                    @else
                        <span class="badge badge-fehler">Inaktiv</span>
                    @endif
                </td>
                <td class="text-rechts" style="white-space: nowrap;">
                    <a href="{{ route('leistungsarten.show', $la) }}" class="btn btn-primaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">
                        Kantone ({{ $la->tarife_count }})
                    </a>
                    <a href="{{ route('leistungsarten.edit', $la) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">✏</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Noch keine Leistungsarten erfasst.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Neue Leistungsart --}}
<div class="karte">
    <div class="abschnitt-label">Neue Leistungsart</div>

    <form method="POST" action="{{ route('leistungsarten.store') }}">
        @csrf

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
            <div style="flex: 2; min-width: 200px;">
                <label class="feld-label">Bezeichnung</label>
                <input type="text" name="bezeichnung" class="feld" value="{{ old('bezeichnung') }}" placeholder="z.B. Grundpflege" required>
                @error('bezeichnung') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label class="feld-label">Einheit</label>
                <select name="einheit" class="feld" required>
                    <option value="stunden" {{ old('einheit', 'stunden') === 'stunden' ? 'selected' : '' }}>Stunden</option>
                    <option value="minuten" {{ old('einheit') === 'minuten' ? 'selected' : '' }}>Minuten</option>
                    <option value="tage"    {{ old('einheit') === 'tage'    ? 'selected' : '' }}>Tage (Pauschale)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label class="feld-label">Gültig ab</label>
                <input type="date" name="gueltig_ab" class="feld" value="{{ old('gueltig_ab') }}">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label class="feld-label">Gültig bis</label>
                <input type="date" name="gueltig_bis" class="feld" value="{{ old('gueltig_bis') }}">
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
            <div style="flex: 1; min-width: 120px;">
                <label class="feld-label">Ansatz</label>
                <input type="number" step="0.01" min="0" name="ansatz_default" class="feld" value="{{ old('ansatz_default', '0.00') }}">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <label class="feld-label">KVG</label>
                <input type="number" step="0.01" min="0" name="kvg_default" class="feld" value="{{ old('kvg_default', '0.00') }}">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <label class="feld-label">Ansatz akut</label>
                <input type="number" step="0.01" min="0" name="ansatz_akut_default" class="feld" value="{{ old('ansatz_akut_default', '0.00') }}">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <label class="feld-label">KVG akut</label>
                <input type="number" step="0.01" min="0" name="kvg_akut_default" class="feld" value="{{ old('kvg_akut_default', '0.00') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primaer">+ Hinzufügen</button>
    </form>
</div>

<div class="text-klein text-hell" style="margin-top: 0.75rem;">
    Diese Ansätze werden als Startwerte beim Anlegen eines neuen Kantons automatisch kopiert und können dort angepasst werden.
</div>

</x-layouts.app>
