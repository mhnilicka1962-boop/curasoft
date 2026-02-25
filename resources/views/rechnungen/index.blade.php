<x-layouts.app :titel="'Rechnungen'">

{{-- Übersicht-Kacheln --}}
<div class="form-grid" style="margin-bottom: 1.5rem;">
    @foreach(['entwurf' => ['Entwürfe','badge-grau'], 'gesendet' => ['Gesendet','badge-info'], 'bezahlt' => ['Bezahlt','badge-erfolg'], 'storniert' => ['Storniert','badge-fehler']] as $s => [$label, $badge])
    <div class="karte" style="padding: 0.875rem; cursor: pointer;" onclick="document.querySelector('[name=status]').value='{{ $s }}'; document.getElementById('filter-form').submit()">
        <div class="abschnitt-label" style="margin-bottom: 0.375rem;">{{ $label }}</div>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ $totale[$s]->anzahl ?? 0 }}</div>
        <div class="text-hell" style="font-size: 0.8125rem;">CHF {{ number_format($totale[$s]->summe ?? 0, 2, '.', "'") }}</div>
    </div>
    @endforeach
</div>

{{-- Filter + Neu --}}
<div class="seiten-kopf" style="margin-bottom: 1rem;">
    <form id="filter-form" method="GET" action="{{ route('rechnungen.index') }}" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <input type="text" name="suche" class="feld" style="width: 200px;" placeholder="Nr. oder Name…" value="{{ request('suche') }}">
        <select name="status" class="feld" style="width: 140px;" onchange="this.form.submit()">
            <option value="">Alle Status</option>
            @foreach(['entwurf' => 'Entwurf', 'gesendet' => 'Gesendet', 'bezahlt' => 'Bezahlt', 'storniert' => 'Storniert'] as $val => $lab)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if(request('suche') || request('status'))
            <a href="{{ route('rechnungen.index') }}" class="btn btn-sekundaer">✕</a>
        @endif
    </form>
    <a href="{{ route('rechnungen.create') }}" class="btn btn-primaer">+ Neue Rechnung</a>
</div>

{{-- Tabelle --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Nummer</th>
                <th>Typ</th>
                <th>Klient</th>
                <th>Periode</th>
                <th>Datum</th>
                <th class="text-rechts">Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rechnungen as $r)
            <tr>
                <td style="font-family: monospace; font-size: 0.8125rem; font-weight: 500;">
                    <a href="{{ route('rechnungen.show', $r) }}" class="link-primaer">
                        {{ $r->rechnungsnummer }}
                    </a>
                </td>
                <td>{!! $r->typBadge() !!}</td>
                <td>{{ $r->klient->nachname }} {{ $r->klient->vorname }}</td>
                <td class="text-hell" style="font-size: 0.8125rem;">
                    {{ $r->periode_von->format('d.m.Y') }} – {{ $r->periode_bis->format('d.m.Y') }}
                </td>
                <td style="font-size: 0.8125rem;">{{ $r->rechnungsdatum->format('d.m.Y') }}</td>
                <td class="text-rechts text-fett">CHF {{ number_format($r->betrag_total, 2, '.', "'") }}</td>
                <td>{!! $r->statusBadge() !!}</td>
                <td class="text-rechts">
                    <a href="{{ route('rechnungen.show', $r) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Keine Rechnungen gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($rechnungen->hasPages())
<div style="margin-top: 1rem;">{{ $rechnungen->links() }}</div>
@endif

</x-layouts.app>
