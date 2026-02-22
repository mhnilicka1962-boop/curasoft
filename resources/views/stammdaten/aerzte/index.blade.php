<x-layouts.app titel="Ärzte">
<div style="max-width: 900px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Ärzte</h1>
        <a href="{{ route('aerzte.create') }}" class="btn btn-primaer">+ Neuer Arzt</a>
    </div>

    <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <input type="text" name="suche" class="feld" placeholder="Name, Praxis, ZSR …"
            value="{{ request('suche') }}" style="min-width: 220px; flex: 1;">
        <select name="status" class="feld" style="width: 140px;">
            <option value="">Alle</option>
            <option value="aktiv" {{ request('status') === 'aktiv' ? 'selected' : '' }}>Aktiv</option>
            <option value="inaktiv" {{ request('status') === 'inaktiv' ? 'selected' : '' }}>Inaktiv</option>
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        @if(request('suche') || request('status'))
            <a href="{{ route('aerzte.index') }}" class="btn btn-sekundaer">×</a>
        @endif
    </form>

    <div class="karte-null">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Name / Praxis</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Fachrichtung</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Kontakt</th>
                    <th style="padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem; color: var(--cs-text-hell); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">ZSR / GLN</th>
                    <th style="padding: 0.625rem 0.875rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($aerzte as $arzt)
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <td style="padding: 0.625rem 0.875rem;">
                        <div class="text-fett">{{ $arzt->vollname() }}</div>
                        @if($arzt->praxis_name)
                            <div style="font-size: 0.8rem;" class="text-hell">{{ $arzt->praxis_name }}</div>
                        @endif
                        @if(!$arzt->aktiv)
                            <span class="badge badge-grau" style="font-size: 0.7rem;">Inaktiv</span>
                        @endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-hell">
                        {{ $arzt->fachrichtung ?? '—' }}
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-klein text-hell">
                        @if($arzt->telefon)<div>{{ $arzt->telefon }}</div>@endif
                        @if($arzt->ort)<div>{{ $arzt->plz }} {{ $arzt->ort }}</div>@endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" class="text-hell">
                        @if($arzt->zsr_nr)<div>ZSR: {{ $arzt->zsr_nr }}</div>@endif
                        @if($arzt->gln_nr)<div>GLN: {{ $arzt->gln_nr }}</div>@endif
                    </td>
                    <td style="padding: 0.625rem 0.875rem;" class="text-rechts">
                        <a href="{{ route('aerzte.edit', $arzt) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">Bearbeiten</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center;" class="text-hell">
                        Noch keine Ärzte erfasst.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($aerzte->hasPages())
        <div style="margin-top: 1rem;">{{ $aerzte->links() }}</div>
    @endif

</div>
</x-layouts.app>
