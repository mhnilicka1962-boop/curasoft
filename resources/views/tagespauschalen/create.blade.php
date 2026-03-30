<x-layouts.app :titel="'Tagespauschale erstellen'">
<div style="max-width: 580px;">
    <a href="{{ $selectedKlientId ? route('klienten.show', $selectedKlientId) : route('tagespauschalen.index') }}"
       class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Zurück</a>

    @if(session('fehler'))
    <div class="flash flash-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    @if($istTiersPayant)
    <div style="background: #fef2f2; border: 2px solid #ef4444; border-radius: var(--cs-radius); padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 1rem; font-weight: 700; color: #b91c1c; margin-bottom: 0.375rem;">⚠ Achtung — Tiers payant</div>
        <div style="font-size: 0.875rem; color: #7f1d1d;">
            Diese Organisation arbeitet mit <strong>Tiers payant</strong>. Tagespauschalen sind nur für Tiers garant vorgesehen.
            Bei Tiers payant werden Leistungsarten immer minutengenau mit der Krankenkasse abgerechnet — eine Tagespauschale ist dort nicht möglich.
        </div>
    </div>
    @endif

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Neue Tagespauschale</div>

        <form method="POST" action="{{ route('tagespauschalen.store') }}">
            @csrf

            {{-- Klient --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Klient</label>
                <select name="klient_id" id="klient_id" class="feld" required>
                    <option value="">— wählen —</option>
                    @foreach($klienten as $k)
                        <option value="{{ $k->id }}" {{ old('klient_id', $selectedKlientId) == $k->id ? 'selected' : '' }}>
                            {{ $k->nachname }} {{ $k->vorname }}
                        </option>
                    @endforeach
                </select>
                @error('klient_id') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            {{-- Automatisch verlängern --}}
            <div style="margin-bottom: 0.875rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem 1rem;">
                <label style="display: flex; align-items: center; gap: 0.625rem; cursor: pointer; font-size: 0.9375rem; font-weight: 600;">
                    <input type="checkbox" name="auto_verlaengern" id="auto_verlaengern" value="1"
                        {{ old('auto_verlaengern', '1') == '1' ? 'checked' : '' }}
                        onchange="toggleDatumBis()">
                    Automatisch verlängern
                </label>
                <div id="hint-auto" class="text-hell" style="font-size: 0.8125rem; margin-top: 0.375rem; padding-left: 1.75rem;">
                    Kein Enddatum — Einsätze werden täglich bis zum Planungshorizont generiert.
                </div>
            </div>

            {{-- Zeitraum --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Gültig von *</label>
                    <input type="date" name="datum_von" id="datum_von" class="feld"
                        value="{{ old('datum_von', date('Y-m-d')) }}" required oninput="aktualisiereVorschau()">
                    @error('datum_von') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label" id="label-datum-bis">Gültig bis</label>
                    <input type="date" name="datum_bis" id="datum_bis" class="feld"
                        value="{{ old('datum_bis') }}"
                        oninput="aktualisiereVorschau()">
                    @error('datum_bis') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            <input type="hidden" name="rechnungstyp" value="kvg">

            {{-- Ansatz --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Ansatz (CHF/Tag)</label>
                <input type="number" name="ansatz" id="ansatz" class="feld"
                    step="0.05" min="0" value="{{ old('ansatz', '0.00') }}" required oninput="aktualisiereVorschau()">
                @error('ansatz') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            {{-- Text für Rechnung --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Text auf Rechnung</label>
                <input type="text" name="text" class="feld" maxlength="500"
                    placeholder="z.B. Tagespauschale Hauspflege"
                    value="{{ old('text') }}">
                @error('text') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            {{-- Vorschau --}}
            <div id="vorschau" style="display:none; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <span class="text-hell"><span id="pv-tage">—</span> Tage × CHF <span id="pv-ansatz">—</span>/Tag</span>
                    <span style="font-weight: 700; font-size: 1rem; color: var(--cs-primaer);">CHF <span id="pv-total">—</span></span>
                </div>
                <div class="text-hell" id="pv-hinweis" style="font-size: 0.75rem;"></div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer" id="btn-erstellen">Tagespauschale erstellen</button>
                <a href="{{ $selectedKlientId ? route('klienten.show', $selectedKlientId) : route('tagespauschalen.index') }}"
                   class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleDatumBis() {
    const auto  = document.getElementById('auto_verlaengern').checked;
    const bis   = document.getElementById('datum_bis');
    const label = document.getElementById('label-datum-bis');
    const hint  = document.getElementById('hint-auto');

    bis.disabled = auto;
    bis.style.opacity = auto ? '0.4' : '1';
    if (auto) bis.value = '';
    label.textContent = auto ? 'Gültig bis (nicht benötigt)' : 'Gültig bis *';
    hint.textContent  = auto
        ? 'Aktiv — der nächtliche Batchjob erstellt täglich neue Einsätze bis zum Planungshorizont. Kein manuelles Eingreifen nötig.'
        : 'Inaktiv — Einsätze werden nur bis zum Enddatum generiert. Danach läuft die Tagespauschale aus.';

    aktualisiereVorschau();
}

function diffTage(von, bis) {
    const d1 = new Date(von), d2 = new Date(bis);
    if (isNaN(d1) || isNaN(d2) || d2 < d1) return null;
    return Math.round((d2 - d1) / 86400000) + 1;
}

function aktualisiereVorschau() {
    const auto   = document.getElementById('auto_verlaengern').checked;
    const von    = document.getElementById('datum_von').value;
    const bis    = document.getElementById('datum_bis').value;
    const ansatz = parseFloat(document.getElementById('ansatz').value) || 0;
    const el     = document.getElementById('vorschau');

    if (auto) {
        if (von && ansatz > 0) {
            el.style.display = 'block';
            document.getElementById('pv-tage').textContent   = '∞';
            document.getElementById('pv-ansatz').textContent = ansatz.toFixed(2);
            document.getElementById('pv-total').textContent  = '∞';
            document.getElementById('pv-hinweis').textContent = 'Läuft unbegrenzt — Einsätze werden bis zum Planungshorizont generiert.';
        } else {
            el.style.display = 'none';
        }
        return;
    }

    const tage = diffTage(von, bis);
    if (!tage || !von || !bis) { el.style.display = 'none'; return; }

    const total = Math.round(tage * ansatz * 100) / 100;
    el.style.display = 'block';
    document.getElementById('pv-tage').textContent    = tage;
    document.getElementById('pv-ansatz').textContent  = ansatz.toFixed(2);
    document.getElementById('pv-total').textContent   = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, "'");
    document.getElementById('pv-hinweis').textContent = `${tage} Tage total — initial bis Planungshorizont generiert, Rest folgt automatisch.`;
}

document.getElementById('auto_verlaengern').addEventListener('change', toggleDatumBis);
['datum_von', 'datum_bis', 'ansatz'].forEach(id =>
    document.getElementById(id)?.addEventListener('input', aktualisiereVorschau)
);
toggleDatumBis();
</script>
@endpush
</x-layouts.app>
