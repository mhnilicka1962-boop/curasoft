<x-layouts.app :titel="'Kanton ' . $region->kuerzel . ' — Leistungsarten'">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('regionen.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Regionen / Kantone</a>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0 0;">
            {{ $region->kuerzel }} — {{ $region->bezeichnung }}
        </h1>
    </div>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif

@php
    $grouped = $tarife->groupBy('leistungsart_id');
    $alleLeistungsarten = \App\Models\Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();
    $fehlendeLa = $alleLeistungsarten->filter(fn($la) => !$grouped->has($la->id));
@endphp

@if($fehlendeLa->isNotEmpty())
<div style="background:#fef3c7; border:1px solid #f59e0b; border-radius:var(--cs-radius); padding:0.625rem 0.875rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
    <span style="font-size:0.875rem; color:#92400e;">
        ⚠ <strong>{{ $fehlendeLa->count() }} Leistungsart(en) ohne Tarif</strong> —
        dieser Kanton erscheint als «nicht konfiguriert» im Rechnungslauf.
    </span>
    <form method="POST" action="{{ route('regionen.initialisieren', $region) }}" style="margin:0;">
        @csrf
        <button type="submit" class="btn btn-sekundaer" style="font-size:0.8125rem; padding:0.25rem 0.75rem;">
            Standard-Tarife anlegen
        </button>
    </form>
</div>
@endif

@foreach($alleLeistungsarten as $la)
@php
    $gruppe = $grouped->get($la->id, collect());
    $aktuell = $gruppe->sortByDesc('gueltig_ab')->first();
@endphp
<div class="karte-null" style="overflow: visible; margin-bottom: 1.25rem;">

    {{-- Header --}}
    <div style="padding: 0.625rem 1rem; background: var(--cs-hintergrund); border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between;">
        <span class="text-fett">{{ $la->bezeichnung }}</span>
        <button type="button"
            onclick="toggleForm('form-{{ $la->id }}')"
            class="btn btn-sekundaer" style="padding: 0.2rem 0.625rem; font-size: 0.8125rem;">
            + Neuer Tarif
        </button>
    </div>

    {{-- Bestehende Einträge --}}
    @if($gruppe->isNotEmpty())
    <div class="tabelle-wrapper">
    <table class="tabelle">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Gültig ab</th>
                <th class="text-rechts">Ansatz</th>
                <th class="text-rechts">KVG</th>
                <th class="text-rechts">Ansatz akut</th>
                <th class="text-rechts">KVG akut</th>
                <th class="text-mitte">Min</th>
                <th class="text-mitte">Std</th>
                <th class="text-mitte">Tag</th>
                <th class="text-mitte">MWST</th>
                <th class="text-hell" style="font-weight: 400;">Mutiert</th>
                <th class="text-hell" style="font-weight: 400;">Erfasst</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($gruppe->sortByDesc('gueltig_ab') as $i => $t)
            @php $istAktuell = $i === 0; @endphp
            <tr style="{{ !$istAktuell ? 'opacity: 0.4;' : '' }}">
                <td class="text-klein text-hell">{{ $t->id }}</td>
                <td style="font-size: 0.875rem;">
                    {{ $t->gueltig_ab?->format('d.m.Y') ?? '—' }}
                    @if($istAktuell)
                        <span class="badge badge-erfolg" style="font-size: 0.65rem; margin-left: 0.25rem;">aktuell</span>
                    @endif
                </td>
                <td class="text-rechts" style="font-weight: {{ $istAktuell ? '600' : '400' }};">{{ number_format($t->ansatz, 2) }}</td>
                <td class="text-rechts text-hell" style="font-size: 0.8125rem;">{{ number_format($t->kkasse, 2) }}</td>
                <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($t->ansatz_akut, 2) }}</td>
                <td class="text-rechts text-hell" style="font-size: 0.8125rem;">{{ number_format($t->kkasse_akut, 2) }}</td>
                <td class="text-mitte">{{ $t->einsatz_minuten ? '✓' : '' }}</td>
                <td class="text-mitte">{{ $t->einsatz_stunden ? '✓' : '' }}</td>
                <td class="text-mitte">{{ $t->einsatz_tage ? '✓' : '' }}</td>
                <td class="text-mitte">{{ $t->mwst ? '✓' : '' }}</td>
                <td class="text-mini text-hell" style="white-space: nowrap;">{{ $t->updated_at?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-mini text-hell" style="white-space: nowrap;">{{ $t->created_at?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-rechts">
                    <a href="{{ route('leistungsarten.tarif.bearbeiten', [$la, $t]) }}"
                        class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✏</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>{{-- tabelle-wrapper --}}
    @else
    <div class="text-klein text-hell" style="padding: 1rem 1.25rem;">Noch kein Tarif erfasst.</div>
    @endif

    {{-- Inline-Formular (aufklappbar) --}}
    <div id="form-{{ $la->id }}" style="display: none; padding: 1rem 1.25rem; border-top: 1px solid var(--cs-border); background: #fafafa;">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">
            Neuer Tarif-Eintrag — {{ $la->bezeichnung }}
        </div>
        <form method="POST" action="{{ route('regionen.tarif.speichern', $region) }}">
            @csrf
            <input type="hidden" name="leistungsart_id" value="{{ $la->id }}">

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                <div style="min-width: 140px;">
                    <label class="feld-label">Gültig ab</label>
                    <input type="date" name="gueltig_ab" class="feld" value="{{ date('Y-m-d') }}" required>
                </div>
                <div style="min-width: 110px;">
                    <label class="feld-label">Ansatz</label>
                    <input type="number" step="0.05" min="0" name="ansatz" class="feld" required
                        value="{{ $aktuell?->ansatz ?? $la->ansatz_default }}">
                </div>
                <div style="min-width: 110px;">
                    <label class="feld-label">KVG</label>
                    <input type="number" step="0.05" min="0" name="kkasse" class="feld" required
                        value="{{ $aktuell?->kkasse ?? $la->kvg_default }}">
                </div>
                <div style="min-width: 110px;">
                    <label class="feld-label">Ansatz akut</label>
                    <input type="number" step="0.05" min="0" name="ansatz_akut" class="feld" required
                        value="{{ $aktuell?->ansatz_akut ?? $la->ansatz_akut_default }}">
                </div>
                <div style="min-width: 110px;">
                    <label class="feld-label">KVG akut</label>
                    <input type="number" step="0.05" min="0" name="kkasse_akut" class="feld" required
                        value="{{ $aktuell?->kkasse_akut ?? $la->kvg_akut_default }}">
                </div>
            </div>

            <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="einsatz_minuten" value="1"
                        {{ $aktuell?->einsatz_minuten ? 'checked' : '' }}
                        style="accent-color: var(--cs-primaer);">
                    Ansatz Minuten
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="einsatz_stunden" value="1"
                        {{ ($aktuell?->einsatz_stunden ?? true) ? 'checked' : '' }}
                        style="accent-color: var(--cs-primaer);">
                    Ansatz Stunden
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="einsatz_tage" value="1"
                        {{ $aktuell?->einsatz_tage ? 'checked' : '' }}
                        style="accent-color: var(--cs-primaer);">
                    Ansatz Tage
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="mwst" value="1"
                        {{ $aktuell?->mwst ? 'checked' : '' }}
                        style="accent-color: var(--cs-primaer);">
                    Inkl. MWST
                </label>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primaer">Speichern</button>
                <button type="button" onclick="toggleForm('form-{{ $la->id }}')" class="btn btn-sekundaer">Abbrechen</button>
            </div>
        </form>
    </div>

</div>
@endforeach

@push('scripts')
<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush

</x-layouts.app>
