<x-layouts.app :titel="'Bedarfsanalysen'">

<div class="seiten-kopf">
    <h1>Bedarfsanalysen</h1>
    <form method="POST" action="{{ route('bedarfsanalysen.store') }}">
        @csrf
        <button type="submit" class="btn btn-primaer">+ Neue Bedarfsanalyse starten</button>
    </form>
</div>

@if($entwuerfe->isNotEmpty())
<div class="karte" style="margin-bottom:1.5rem;">
    <h2 style="margin:0 0 1rem;">Offene Entwürfe</h2>
    <table class="tabelle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Schritt</th>
                <th>Erfasst von</th>
                <th>Zuletzt bearbeitet</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($entwuerfe as $e)
            <tr>
                <td>{{ $e->anzeigeName() }}</td>
                <td>
                    <span class="badge badge-warnung">{{ $e->aktueller_schritt }} / 5</span>
                </td>
                <td>{{ $e->ersteller?->vorname }} {{ $e->ersteller?->nachname }}</td>
                <td>{{ $e->updated_at->format('d.m.Y H:i') }}</td>
                <td>
                    <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $e->id, 'schritt' => $e->aktueller_schritt]) }}"
                       class="btn btn-primaer">Weiterführen</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="karte">
    <h2 style="margin:0 0 1rem;">Abgeschlossene Aufnahmen</h2>
    @if($abgeschlossene->isNotEmpty())
    <table class="tabelle">
        <thead>
            <tr>
                <th>Klient</th>
                <th>Abgeschlossen</th>
                <th>Erfasst von</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($abgeschlossene as $a)
            <tr>
                <td>
                    @if($a->klient)
                        <a href="{{ route('klienten.show', $a->klient) }}">{{ $a->klient->nachname }} {{ $a->klient->vorname }}</a>
                    @else
                        {{ $a->anzeigeName() }}
                    @endif
                </td>
                <td>{{ $a->abgeschlossen_am?->format('d.m.Y H:i') }}</td>
                <td>{{ $a->ersteller?->vorname }} {{ $a->ersteller?->nachname }}</td>
                <td>
                    @if($a->klient)
                    <a href="{{ route('bedarfsanalysen.show', $a->klient) }}" class="btn btn-sekundaer">Anzeigen</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:var(--cs-text-gedaempft);">Noch keine abgeschlossenen Bedarfsanalysen.</p>
    @endif
</div>

</x-layouts.app>
