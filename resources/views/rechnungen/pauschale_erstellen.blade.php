<x-layouts.app :titel="'Pauschalrechnung erstellen'">
<div style="max-width: 680px;">
    <a href="{{ route('rechnungen.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    @if(session('fehler'))
    <div class="flash flash-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Pauschalrechnung erstellen</div>

        <form method="POST" action="{{ route('rechnungen.pauschale.store') }}" id="pauschale-form">
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
                @error('klient_id')
                    <div class="feld-fehler">{{ $message }}</div>
                @enderror
            </div>

            {{-- Periode --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Periode von</label>
                    <input type="date" name="periode_von" id="periode_von" class="feld"
                        value="{{ old('periode_von', date('Y-m-01')) }}" required>
                    @error('periode_von')
                        <div class="feld-fehler">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="feld-label">Periode bis</label>
                    <input type="date" name="periode_bis" id="periode_bis" class="feld"
                        value="{{ old('periode_bis', date('Y-m-t')) }}" required>
                    @error('periode_bis')
                        <div class="feld-fehler">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Rechnungstyp --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Rechnungstyp</label>
                <select name="rechnungstyp" id="rechnungstyp" class="feld" required>
                    <option value="kombiniert" {{ old('rechnungstyp', 'kombiniert') === 'kombiniert' ? 'selected' : '' }}>Kombiniert (KK + Patient)</option>
                    <option value="kvg"        {{ old('rechnungstyp') === 'kvg'        ? 'selected' : '' }}>KVG → Krankenkasse</option>
                    <option value="klient"     {{ old('rechnungstyp') === 'klient'     ? 'selected' : '' }}>Klient (Selbstbehalt)</option>
                    <option value="gemeinde"   {{ old('rechnungstyp') === 'gemeinde'   ? 'selected' : '' }}>Gemeinde / Kanton</option>
                </select>
                @error('rechnungstyp')
                    <div class="feld-fehler">{{ $message }}</div>
                @enderror
            </div>

            {{-- Vorschau --}}
            <div id="vorschau" style="display: none; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.875rem 1rem; margin-bottom: 1.25rem;">
                <div class="abschnitt-label" style="font-size: 0.75rem; margin-bottom: 0.625rem;">Vorschau</div>
                <table style="width: 100%; font-size: 0.875rem; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--cs-border); color: var(--cs-text-hell); font-size: 0.8rem;">
                            <th style="text-align: left; padding: 0.25rem 0.375rem;">Leistung</th>
                            <th style="text-align: right; padding: 0.25rem 0.375rem;">Tage</th>
                            <th style="text-align: right; padding: 0.25rem 0.375rem;">Ansatz Patient</th>
                            <th style="text-align: right; padding: 0.25rem 0.375rem;">Ansatz KK</th>
                            <th style="text-align: right; padding: 0.25rem 0.375rem;">Total Patient</th>
                            <th style="text-align: right; padding: 0.25rem 0.375rem;">Total KK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.375rem 0.375rem;">Tagespauschale</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem;" id="pv-tage">—</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem;" id="pv-ansatz-pat">—</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem;" id="pv-ansatz-kk">—</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem; font-weight: 600;" id="pv-total-pat">—</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem; font-weight: 600;" id="pv-total-kk">—</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--cs-border); font-weight: 700; font-size: 0.9rem;">
                            <td colspan="4" style="padding: 0.375rem 0.375rem; text-align: right;">Gesamt</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem;" id="pv-gesamt-pat">—</td>
                            <td style="text-align: right; padding: 0.375rem 0.375rem; color: var(--cs-primaer);" id="pv-gesamt-kk">—</td>
                        </tr>
                    </tfoot>
                </table>
                <div id="pv-kein-beitrag" style="display: none; color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.5rem;">
                    Kein Beitrag für diesen Klienten erfasst — Vorschau nicht möglich.
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Pauschalrechnung erstellen</button>
                <a href="{{ route('rechnungen.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const beitraege = @json($klientenBeitraege);

function chf(val) {
    return 'CHF ' + parseFloat(val).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, "'");
}

function diffTage(von, bis) {
    const d1 = new Date(von);
    const d2 = new Date(bis);
    if (isNaN(d1) || isNaN(d2) || d2 < d1) return null;
    return Math.round((d2 - d1) / 86400000) + 1;
}

function aktualisiereVorschau() {
    const klientId  = document.getElementById('klient_id').value;
    const von       = document.getElementById('periode_von').value;
    const bis       = document.getElementById('periode_bis').value;
    const typ       = document.getElementById('rechnungstyp').value;
    const vorschau  = document.getElementById('vorschau');
    const keinBeit  = document.getElementById('pv-kein-beitrag');

    if (!klientId || !von || !bis) {
        vorschau.style.display = 'none';
        return;
    }

    vorschau.style.display = 'block';
    const b = beitraege[klientId];

    if (!b) {
        keinBeit.style.display = 'block';
        ['pv-tage','pv-ansatz-pat','pv-ansatz-kk','pv-total-pat','pv-total-kk','pv-gesamt-pat','pv-gesamt-kk']
            .forEach(id => document.getElementById(id).textContent = '—');
        return;
    }

    keinBeit.style.display = 'none';
    const tage = diffTage(von, bis);
    if (!tage) {
        vorschau.style.display = 'none';
        return;
    }

    let tarifPat, tarifKk;
    if (typ === 'kvg') {
        tarifPat = 0;
        tarifKk  = b.ansatz_kunde + b.ansatz_spitex;
    } else if (typ === 'klient' || typ === 'gemeinde') {
        tarifPat = b.ansatz_kunde + b.ansatz_spitex;
        tarifKk  = 0;
    } else {
        tarifPat = b.ansatz_kunde;
        tarifKk  = b.ansatz_spitex;
    }

    const totalPat = Math.round(tage * tarifPat * 100) / 100;
    const totalKk  = Math.round(tage * tarifKk  * 100) / 100;

    document.getElementById('pv-tage').textContent       = tage;
    document.getElementById('pv-ansatz-pat').textContent = chf(tarifPat);
    document.getElementById('pv-ansatz-kk').textContent  = chf(tarifKk);
    document.getElementById('pv-total-pat').textContent  = chf(totalPat);
    document.getElementById('pv-total-kk').textContent   = chf(totalKk);
    document.getElementById('pv-gesamt-pat').textContent = chf(totalPat);
    document.getElementById('pv-gesamt-kk').textContent  = chf(totalKk);
}

['klient_id', 'periode_von', 'periode_bis', 'rechnungstyp'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', aktualisiereVorschau);
});

// Initiale Vorschau falls klient_id vorbelegt
aktualisiereVorschau();
</script>
@endpush
</x-layouts.app>
