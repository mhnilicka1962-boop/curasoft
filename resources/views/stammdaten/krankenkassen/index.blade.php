<x-layouts.app titel="Krankenkassen">
<div style="max-width: 860px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Krankenkassen</h1>
        <a href="{{ route('krankenkassen.create') }}" class="btn btn-primaer">+ Neue Krankenkasse</a>
    </div>

    <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <input type="text" name="suche" class="feld" placeholder="Name, Kürzel, EAN …"
            value="{{ request('suche') }}" style="min-width: 220px; flex: 1;">
        <select name="status" class="feld" style="width: 140px;">
            <option value="">Alle</option>
            <option value="aktiv" {{ request('status') === 'aktiv' ? 'selected' : '' }}>Aktiv</option>
            <option value="inaktiv" {{ request('status') === 'inaktiv' ? 'selected' : '' }}>Inaktiv</option>
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if(request('suche') || request('status'))
            <a href="{{ route('krankenkassen.index') }}" class="btn btn-sekundaer">×</a>
        @endif
    </form>

    <div class="karte-null">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Kürzel</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">EAN / BAG</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Kontakt</th>
                    <th style="padding: 0.625rem 0.875rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($krankenkassen as $kk)
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <td style="padding: 0.625rem 0.875rem;">
                        <div class="text-fett">{{ $kk->name }}</div>
                        @if(!$kk->aktiv)
                            <span class="badge badge-grau" style="font-size: 0.7rem;">Inaktiv</span>
                        @endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-hell">{{ $kk->kuerzel ?? '—' }}</td>
                    <td style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" class="text-hell">
                        @if($kk->ean_nr)<div>EAN: {{ $kk->ean_nr }}</div>@endif
                        @if($kk->bag_nr)<div>BAG: {{ $kk->bag_nr }}</div>@endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-klein text-hell">
                        @if($kk->telefon)<div>{{ $kk->telefon }}</div>@endif
                        @if($kk->ort)<div>{{ $kk->plz }} {{ $kk->ort }}</div>@endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-rechts">
                        <a href="{{ route('krankenkassen.edit', $kk) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">Bearbeiten</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center;" class="text-hell">
                        Noch keine Krankenkassen erfasst.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($krankenkassen->hasPages())
        <div style="margin-top: 1rem;">{{ $krankenkassen->links() }}</div>
    @endif

</div>
</x-layouts.app>
