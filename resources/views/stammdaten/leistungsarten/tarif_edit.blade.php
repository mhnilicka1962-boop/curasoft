<x-layouts.app :titel="'Tarif bearbeiten'">
<div style="max-width: 560px;">

    <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; font-size: 0.875rem;">
        <a href="{{ route('regionen.show', $tarif->region_id) }}" class="link-gedaempt">
            ← {{ $tarif->region?->kuerzel }} (Kanton)
        </a>
        <a href="{{ route('leistungsarten.show', $leistungsart) }}" class="link-gedaempt">
            ← {{ $leistungsart->bezeichnung }} (Leistungsart)
        </a>
    </div>

    @if(session('erfolg'))
        <div class="erfolg-box">
            {{ session('erfolg') }}
        </div>
    @endif

    <div class="karte">
        {{-- Header-Info --}}
        <div class="detail-raster" style="margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--cs-border);">
            <div>
                <div class="detail-label">ID</div>
                <div class="detail-wert">{{ $tarif->id }}</div>
            </div>
            <div>
                <div class="detail-label">Kanton Abrechnung</div>
                <div style="font-weight: 700; font-size: 1rem;">{{ $tarif->region?->kuerzel }}</div>
                <div class="text-hell">{{ $tarif->region?->bezeichnung }}</div>
            </div>
            <div>
                <div class="detail-label">Leistungsart</div>
                <div class="detail-wert">{{ $leistungsart->bezeichnung }}</div>
            </div>
            <div>
                <div class="detail-label">Einheit</div>
                <div>{{ $leistungsart->einheitLabel() }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('leistungsarten.tarif.aktualisieren', [$leistungsart, $tarif]) }}">
            @csrf @method('PUT')

            {{-- Gültig ab --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label">Gültig ab (tt.mm.jjjj)</label>
                <input type="date" name="gueltig_ab" class="feld"
                    value="{{ old('gueltig_ab', $tarif->gueltig_ab?->format('Y-m-d')) }}">
            </div>

            {{-- Ansätze --}}
            <div class="form-grid-2" style="margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Ansatz (obligatorisch)</label>
                    <input type="number" step="0.05" min="0" name="ansatz" class="feld" required
                        value="{{ old('ansatz', $tarif->ansatz) }}">
                    @error('ansatz') <div style="color: var(--cs-fehler); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">KVG</label>
                    <input type="number" step="0.05" min="0" name="kkasse" class="feld" required
                        value="{{ old('kkasse', $tarif->kkasse) }}">
                </div>
                <div>
                    <label class="feld-label">Ansatz-akut (obligatorisch)</label>
                    <input type="number" step="0.05" min="0" name="ansatz_akut" class="feld" required
                        value="{{ old('ansatz_akut', $tarif->ansatz_akut) }}">
                </div>
                <div>
                    <label class="feld-label">KVG-akut</label>
                    <input type="number" step="0.05" min="0" name="kkasse_akut" class="feld" required
                        value="{{ old('kkasse_akut', $tarif->kkasse_akut) }}">
                </div>
            </div>

            {{-- Verrechnung --}}
            <div style="margin-bottom: 1rem;">
                <div class="feld-label" style="margin-bottom: 0.5rem;">Verrechnung</div>
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="einsatz_minuten" value="1"
                            {{ old('einsatz_minuten', $tarif->einsatz_minuten) ? 'checked' : '' }}
                            style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                        Ansatz Minuten
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="einsatz_stunden" value="1"
                            {{ old('einsatz_stunden', $tarif->einsatz_stunden) ? 'checked' : '' }}
                            style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                        Ansatz Stunden
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="einsatz_tage" value="1"
                            {{ old('einsatz_tage', $tarif->einsatz_tage) ? 'checked' : '' }}
                            style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                        Ansatz Tage
                    </label>
                </div>
            </div>

            {{-- MWST --}}
            <div style="margin-bottom: 1.25rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="mwst" value="1"
                        {{ old('mwst', $tarif->mwst) ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Rechnung inklusive MWST
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <a href="{{ route('leistungsarten.show', $leistungsart) }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </form>

        {{-- Mutation / Erfassung --}}
        <div class="abschnitt-trenn" style="margin-top: 1.5rem; padding-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem; color: var(--cs-text-hell);">
            <div>
                <div class="text-fett" style="margin-bottom: 0.2rem;">Mutationsdatum / durch</div>
                <div>{{ $tarif->updated_at?->format('d.m.Y H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="text-fett" style="margin-bottom: 0.2rem;">Erfassungsdatum / durch</div>
                <div>{{ $tarif->created_at?->format('d.m.Y H:i') ?? '—' }}</div>
            </div>
        </div>
    </div>

</div>
</x-layouts.app>
