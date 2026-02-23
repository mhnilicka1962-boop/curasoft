<x-layouts.app :titel="'Klienten'">

{{-- Toolbar --}}
<div class="seiten-kopf">
    <form method="GET" action="{{ route('klienten.index') }}" style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex: 1; min-width: 0;">
        <input type="text" name="suche" class="feld" style="flex: 1; min-width: 140px; max-width: 260px;"
            placeholder="Name oder Ort…" value="{{ request('suche') }}">
        <select name="status" class="feld" style="width: 120px;">
            <option value="alle"   {{ request('status') === 'alle'   ? 'selected' : '' }}>Alle</option>
            <option value="aktiv"  {{ request('status', 'aktiv') === 'aktiv'  ? 'selected' : '' }}>Aktiv</option>
            <option value="inaktiv"{{ request('status') === 'inaktiv' ? 'selected' : '' }}>Inaktiv</option>
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if(request('suche') || request('status'))
            <a href="{{ route('klienten.index') }}" class="btn btn-sekundaer">✕</a>
        @endif
    </form>
    <a href="{{ route('schnellerfassung') }}" class="btn btn-primaer" style="white-space: nowrap;">+ Neuer Patient</a>
    <a href="{{ route('klienten.create') }}" class="btn btn-sekundaer" style="white-space: nowrap; font-size: 0.8125rem;">Detailformular</a>
</div>

{{-- Liste --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Name</th>
                <th class="col-desktop">Geburtsdatum</th>
                <th class="col-desktop">Ort</th>
                <th class="col-desktop">Telefon</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($klienten as $klient)
            <tr style="{{ !$klient->aktiv ? 'opacity: 0.55;' : '' }}">
                <td>
                    <a href="{{ route('klienten.show', $klient) }}"
                        class="text-mittel link-primaer">
                        {{ $klient->nachname }} {{ $klient->vorname }}
                    </a>
                    <span class="mobile-meta">
                        {{ $klient->ort ?? '' }}{{ $klient->geburtsdatum ? ' · ' . $klient->geburtsdatum->format('d.m.Y') : '' }}
                    </span>
                </td>
                <td class="col-desktop text-klein text-hell">
                    {{ $klient->geburtsdatum?->format('d.m.Y') ?? '—' }}
                </td>
                <td class="col-desktop" style="font-size: 0.8125rem;">{{ $klient->ort ?? '—' }}</td>
                <td class="col-desktop" style="font-size: 0.8125rem;">{{ $klient->telefon ?? '—' }}</td>
                <td>
                    @if($klient->aktiv)
                        <span class="badge badge-erfolg">Aktiv</span>
                    @else
                        <span class="badge badge-grau">Inaktiv</span>
                    @endif
                </td>
                <td class="text-rechts" style="white-space: nowrap;">
                    <a href="{{ route('klienten.edit', $klient) }}" class="btn btn-sekundaer"
                        style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Bearbeiten</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Keine Klienten gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($klienten->hasPages())
<div style="margin-top: 1rem;">{{ $klienten->links() }}</div>
@endif

</x-layouts.app>
