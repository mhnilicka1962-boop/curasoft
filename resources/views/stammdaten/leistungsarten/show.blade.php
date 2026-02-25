<x-layouts.app :titel="$leistungsart->bezeichnung . ' — Kantone'">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('leistungsarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Leistungsarten</a>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0 0;">{{ $leistungsart->bezeichnung }}</h1>
        <div class="text-klein text-hell" style="margin-top: 0.2rem;">
            {{ $leistungsart->einheitLabel() }} &nbsp;·&nbsp;
            @if($leistungsart->kassenpflichtig)
                <span class="badge badge-erfolg">KVG-pflichtig</span>
            @else
                <span class="badge badge-grau">Privat</span>
            @endif
            @if($leistungsart->tarmed_code)
                &nbsp;·&nbsp; <span style="font-family: monospace;">T311: {{ $leistungsart->tarmed_code }}</span>
            @endif
        </div>
    </div>
    <a href="{{ route('leistungsarten.edit', $leistungsart) }}" class="btn btn-sekundaer">Grundset bearbeiten</a>
</div>

@if(session('erfolg'))
    <div class="meldung meldung-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
@endif
@if(session('fehler'))
    <div class="meldung meldung-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
@endif

{{-- Info-Kachel Defaultwerte --}}
<div class="karte" style="margin-bottom: 1.5rem; background: var(--hintergrund-alt, #f8f9fa);">
    <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Grundset (Standardwerte für neue Kantone)</div>
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.875rem;">
        <span>Ansatz: <strong>{{ number_format($leistungsart->ansatz_default, 2) }}</strong></span>
        <span>KVG: <strong>{{ number_format($leistungsart->kvg_default, 2) }}</strong></span>
        <span>Ansatz akut: <strong>{{ number_format($leistungsart->ansatz_akut_default, 2) }}</strong></span>
        <span>KVG akut: <strong>{{ number_format($leistungsart->kvg_akut_default, 2) }}</strong></span>
        @if($leistungsart->gueltig_ab)
            <span class="text-hell">Gültig ab: {{ $leistungsart->gueltig_ab->format('d.m.Y') }}</span>
        @endif
    </div>
</div>

{{-- Kantons-Tarife --}}
@php $grouped = $tarife->groupBy('region_id'); @endphp

@if($grouped->isEmpty())
<div class="karte text-mitte text-hell" style="padding: 2.5rem; margin-bottom: 1.5rem;">
    Noch keine Kantone erfasst. Unten einen Kanton hinzufügen.
</div>
@else

@foreach($grouped as $regionId => $gruppe)
@php
    $region   = $gruppe->first()->region;
    $aktuell  = $gruppe->sortByDesc('gueltig_ab')->first();
@endphp
<div class="karte-null" style="overflow-x: auto; margin-bottom: 1.25rem;">
    {{-- Kanton-Header --}}
    <div style="padding: 0.625rem 1rem; background: var(--cs-hintergrund, #f1f5f9); border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-weight: 700; font-size: 0.9375rem;">{{ $region?->kuerzel }}</span>
            <span class="text-hell" style="font-size: 0.875rem; margin-left: 0.5rem;">{{ $region?->bezeichnung }}</span>
            <span class="badge badge-grau" style="margin-left: 0.5rem; font-size: 0.7rem;">{{ $gruppe->count() }} {{ $gruppe->count() === 1 ? 'Eintrag' : 'Einträge' }}</span>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            {{-- Aktuellen Tarif direkt anzeigen --}}
            <span class="text-hell" style="font-size: 0.8125rem;">
                Ansatz <strong>{{ number_format($aktuell->ansatz, 2) }}</strong> ·
                KVG <strong>{{ number_format($aktuell->kkasse, 2) }}</strong>
                @if($aktuell->gueltig_ab)
                    · ab {{ $aktuell->gueltig_ab->format('d.m.Y') }}
                @endif
            </span>
            <form method="POST" action="{{ route('leistungsarten.tarif.loeschen', [$leistungsart, $region]) }}"
                onsubmit="return confirm('Alle Tarife für {{ $region?->kuerzel }} löschen?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; color: var(--cs-fehler);">Löschen</button>
            </form>
        </div>
    </div>

    <table class="tabelle" style="min-width: 700px;">
        <thead>
            <tr>
                <th>Gültig ab</th>
                <th class="text-rechts">Ansatz</th>
                <th class="text-rechts">KVG (KK-Anteil)</th>
                <th class="text-rechts">Pat.-Anteil</th>
                <th class="text-rechts">Ansatz akut</th>
                <th class="text-rechts">KVG akut</th>
                <th class="text-mitte">Min</th>
                <th class="text-mitte">Std</th>
                <th class="text-mitte">Tag</th>
                <th class="text-mitte">MWST</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($gruppe->sortByDesc('gueltig_ab') as $i => $t)
            @php $istAktuell = $i === 0; @endphp
            <tr style="{{ !$istAktuell ? 'opacity: 0.45;' : '' }}">
                <td style="font-size: 0.875rem;">
                    {{ $t->gueltig_ab?->format('d.m.Y') ?? '—' }}
                    @if($istAktuell)
                        <span class="badge badge-erfolg" style="font-size: 0.65rem; margin-left: 0.25rem;">aktuell</span>
                    @endif
                </td>
                <td class="text-rechts" style="font-weight: {{ $istAktuell ? '700' : '400' }};">
                    {{ number_format($t->ansatz, 2) }}
                </td>
                <td class="text-rechts text-hell">{{ number_format($t->kkasse, 2) }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem; color: var(--cs-text);">
                    {{ number_format(max(0, $t->ansatz - $t->kkasse), 2) }}
                </td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($t->ansatz_akut, 2) }}</td>
                <td class="text-rechts text-hell" style="font-size: 0.8125rem;">{{ number_format($t->kkasse_akut, 2) }}</td>
                <td class="text-mitte" style="font-size: 0.875rem;">{{ $t->einsatz_minuten ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.875rem;">{{ $t->einsatz_stunden ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.875rem;">{{ $t->einsatz_tage ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.875rem;">{{ $t->mwst ? '✓' : '' }}</td>
                <td class="text-rechts">
                    <a href="{{ route('leistungsarten.tarif.bearbeiten', [$leistungsart, $t]) }}"
                        class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✏</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach
@endif

{{-- Formular: Neuer Kanton-Tarif --}}
<div class="karte" style="margin-top: 0.5rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Tarif für Kanton hinzufügen</div>

    @if($errors->any())
        <div class="meldung meldung-fehler" style="margin-bottom: 0.75rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('leistungsarten.tarif.speichern', $leistungsart) }}">
        @csrf
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
            <div style="min-width: 160px;">
                <label class="form-label">Kanton</label>
                <select name="region_id" class="feld" required>
                    <option value="">— wählen —</option>
                    @foreach($regionen as $region)
                        <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->kuerzel }} — {{ $region->bezeichnung }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 130px;">
                <label class="form-label">Gültig ab</label>
                <input type="date" name="gueltig_ab" class="feld" value="{{ old('gueltig_ab', date('Y-01-01')) }}">
            </div>
            <div style="min-width: 130px;">
                <label class="form-label">Ansatz (CHF/h)</label>
                <input type="number" step="0.05" min="0" name="ansatz" class="feld" required
                    value="{{ old('ansatz', number_format($leistungsart->ansatz_default, 2, '.', '')) }}"
                    placeholder="{{ $leistungsart->ansatz_default }}">
            </div>
            <div style="min-width: 130px;">
                <label class="form-label">KVG-Anteil (CHF/h)</label>
                <input type="number" step="0.05" min="0" name="kkasse" class="feld" required
                    value="{{ old('kkasse', number_format($leistungsart->kvg_default, 2, '.', '')) }}"
                    placeholder="{{ $leistungsart->kvg_default }}">
                <div class="text-hell" style="font-size: 0.75rem; margin-top: 0.2rem;">
                    Patient zahlt: Ansatz − KVG
                </div>
            </div>
            <div style="min-width: 130px;">
                <label class="form-label">Ansatz akut</label>
                <input type="number" step="0.05" min="0" name="ansatz_akut" class="feld"
                    value="{{ old('ansatz_akut', number_format($leistungsart->ansatz_akut_default, 2, '.', '')) }}">
            </div>
            <div style="min-width: 130px;">
                <label class="form-label">KVG akut</label>
                <input type="number" step="0.05" min="0" name="kkasse_akut" class="feld"
                    value="{{ old('kkasse_akut', number_format($leistungsart->kvg_akut_default, 2, '.', '')) }}">
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                <input type="checkbox" name="kassenpflichtig" value="1"
                    {{ old('kassenpflichtig', $leistungsart->kassenpflichtig) ? 'checked' : '' }}>
                KVG-pflichtig
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                <input type="checkbox" name="einsatz_stunden" value="1"
                    {{ old('einsatz_stunden', true) ? 'checked' : '' }}>
                Verrechnung nach Stunden
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                <input type="checkbox" name="einsatz_minuten" value="1"
                    {{ old('einsatz_minuten') ? 'checked' : '' }}>
                Verrechnung nach Minuten
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                <input type="checkbox" name="mwst" value="1"
                    {{ old('mwst') ? 'checked' : '' }}>
                MWST
            </label>
        </div>

        <button type="submit" class="btn btn-primaer">Kanton hinzufügen</button>
    </form>
</div>

<div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.625rem;">
    Mehrere Einträge pro Kanton möglich (Tarifhistorie). Der aktuellste Eintrag (höchstes Datum) wird für die Abrechnung verwendet.
</div>

</x-layouts.app>
