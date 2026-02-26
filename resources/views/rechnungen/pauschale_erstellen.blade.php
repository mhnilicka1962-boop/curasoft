<x-layouts.app :titel="'Pauschalrechnung erstellen'">
<div style="max-width: 560px;">
    <a href="{{ $selectedKlientId ? route('klienten.show', $selectedKlientId) : route('rechnungen.index') }}"
       class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    @if(session('fehler'))
    <div class="flash flash-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Pauschalrechnung erstellen</div>

        <form method="POST" action="{{ route('rechnungen.pauschale.store') }}">
            @csrf

            {{-- Klient --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Klient</label>
                <select name="klient_id" id="klient_id" class="feld" required>
                    <option value="">— wählen —</option>
                    @foreach($klienten as $k)
                        <option value="{{ $k->id }}"
                            {{ (old('klient_id', $selectedKlientId) == $k->id) ? 'selected' : '' }}>
                            {{ $k->nachname }} {{ $k->vorname }}
                        </option>
                    @endforeach
                </select>
                @error('klient_id') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            {{-- Periode --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Periode von</label>
                    <input type="date" name="periode_von" id="periode_von" class="feld"
                        value="{{ old('periode_von', date('Y-m-01')) }}" required>
                    @error('periode_von') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">Periode bis</label>
                    <input type="date" name="periode_bis" id="periode_bis" class="feld"
                        value="{{ old('periode_bis', date('Y-m-t')) }}" required>
                    @error('periode_bis') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Rechnungstyp + Ansatz --}}
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="feld-label">Rechnungstyp</label>
                    <select name="rechnungstyp" id="rechnungstyp" class="feld" required>
                        <option value="kvg"      {{ old('rechnungstyp', 'kvg') === 'kvg'      ? 'selected' : '' }}>KVG → Krankenkasse</option>
                        <option value="klient"   {{ old('rechnungstyp') === 'klient'   ? 'selected' : '' }}>Klient (Selbstbehalt)</option>
                        <option value="gemeinde" {{ old('rechnungstyp') === 'gemeinde' ? 'selected' : '' }}>Gemeinde / Kanton</option>
                    </select>
                    @error('rechnungstyp') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">Ansatz (CHF/Tag)</label>
                    <input type="number" name="ansatz" id="ansatz" class="feld"
                        step="0.05" min="0"
                        value="{{ old('ansatz', '0.00') }}" required>
                    @error('ansatz') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Vorschau (live) --}}
            <div id="vorschau" style="display: none; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-hell">Tagespauschale · <span id="pv-tage">—</span> Tage × CHF <span id="pv-ansatz">—</span></span>
                    <span style="font-weight: 700; font-size: 1rem; color: var(--cs-primaer);">CHF <span id="pv-total">—</span></span>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Pauschalrechnung erstellen</button>
                <a href="{{ $selectedKlientId ? route('klienten.show', $selectedKlientId) : route('rechnungen.index') }}"
                   class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const beitraege = @json($klientenBeitraege);

function diffTage(von, bis) {
    const d1 = new Date(von), d2 = new Date(bis);
    if (isNaN(d1) || isNaN(d2) || d2 < d1) return null;
    return Math.round((d2 - d1) / 86400000) + 1;
}

function vorbelegen() {
    const klientId = document.getElementById('klient_id').value;
    const b = beitraege[klientId];
    if (!b) return;
    const total = b.ansatz_kunde + b.ansatz_spitex;
    document.getElementById('ansatz').value = total.toFixed(2);
    aktualisiereVorschau();
}

function aktualisiereVorschau() {
    const von     = document.getElementById('periode_von').value;
    const bis     = document.getElementById('periode_bis').value;
    const ansatz  = parseFloat(document.getElementById('ansatz').value) || 0;
    const vorschau = document.getElementById('vorschau');

    const tage = diffTage(von, bis);
    if (!tage || !von || !bis) { vorschau.style.display = 'none'; return; }

    const total = Math.round(tage * ansatz * 100) / 100;
    vorschau.style.display = 'block';
    document.getElementById('pv-tage').textContent   = tage;
    document.getElementById('pv-ansatz').textContent = ansatz.toFixed(2);
    document.getElementById('pv-total').textContent  = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, "'");
}

document.getElementById('klient_id').addEventListener('change', vorbelegen);
['periode_von', 'periode_bis', 'ansatz'].forEach(id =>
    document.getElementById(id)?.addEventListener('input', aktualisiereVorschau)
);

if (document.getElementById('klient_id').value) vorbelegen();
aktualisiereVorschau();
</script>
@endpush
</x-layouts.app>
