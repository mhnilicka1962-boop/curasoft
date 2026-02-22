<x-layouts.app :titel="'Neue Rechnung'">
<div style="max-width: 760px;">
    <a href="{{ route('rechnungen.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    {{-- Schritt 1: Klient + Periode wählen --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">1. Klient & Abrechnungsperiode</div>
        <form method="GET" action="{{ route('rechnungen.create') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 180px;">
                <label class="feld-label">Klient</label>
                <select name="klient_id" class="feld" required>
                    <option value="">— wählen —</option>
                    @foreach($klienten as $k)
                        <option value="{{ $k->id }}" {{ request('klient_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nachname }} {{ $k->vorname }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="feld-label">Von</label>
                <input type="date" name="periode_von" class="feld" value="{{ request('periode_von', date('Y-m-01')) }}" required>
            </div>
            <div>
                <label class="feld-label">Bis</label>
                <input type="date" name="periode_bis" class="feld" value="{{ request('periode_bis', date('Y-m-t')) }}" required>
            </div>
            <button type="submit" class="btn btn-primaer">Einsätze laden</button>
        </form>
    </div>

    {{-- Schritt 2: Einsätze auswählen --}}
    @if($klient && $einsaetze->isNotEmpty())
    <form method="POST" action="{{ route('rechnungen.store') }}">
        @csrf
        <input type="hidden" name="klient_id" value="{{ $klient->id }}">
        <input type="hidden" name="periode_von" value="{{ request('periode_von') }}">
        <input type="hidden" name="periode_bis" value="{{ request('periode_bis') }}">

        <div class="karte-null" style="margin-bottom: 1rem;">
            <div style="padding: 1rem; border-bottom: 1px solid var(--cs-border); display: flex; justify-content: space-between; align-items: center;">
                <div class="abschnitt-label">
                    2. Einsätze auswählen — {{ $klient->vorname }} {{ $klient->nachname }}
                </div>
                <label style="font-size: 0.8125rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem;">
                    <input type="checkbox" id="alle-auswaehlen" checked> Alle auswählen
                </label>
            </div>
            <table class="tabelle">
                <thead>
                    <tr>
                        <th style="width: 2.5rem;"></th>
                        <th>Datum</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th class="text-rechts">Minuten</th>
                        <th>Bemerkung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($einsaetze as $e)
                    <tr>
                        <td>
                            <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}"
                                class="einsatz-checkbox" checked
                                style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                        </td>
                        <td>{{ $e->datum->format('d.m.Y') }}</td>
                        <td style="font-size: 0.8125rem;">{{ $e->checkin_zeit?->format('H:i') ?? '—' }}</td>
                        <td style="font-size: 0.8125rem;">{{ $e->checkout_zeit?->format('H:i') ?? '—' }}</td>
                        <td class="text-rechts text-mittel">{{ $e->minuten ?? '—' }}</td>
                        <td class="text-hell" style="font-size: 0.8125rem;">{{ $e->bemerkung ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: var(--cs-hintergrund);">
                        <td colspan="4" class="text-fett" style="padding: 0.625rem 0.75rem;">Total</td>
                        <td class="text-rechts" style="font-weight: 700; padding: 0.625rem 0.75rem;">
                            {{ $einsaetze->sum('minuten') }} Min.
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primaer">Rechnung erstellen</button>
            <a href="{{ route('rechnungen.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>

    @elseif($klient && $einsaetze->isEmpty())
    <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">
        Keine abgeschlossenen, noch nicht verrechneten Einsätze in diesem Zeitraum für<br>
        <strong>{{ $klient->vorname }} {{ $klient->nachname }}</strong>.
    </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('alle-auswaehlen')?.addEventListener('change', function() {
    document.querySelectorAll('.einsatz-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
</x-layouts.app>
