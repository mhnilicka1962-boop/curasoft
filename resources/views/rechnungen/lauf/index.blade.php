<x-layouts.app :titel="'Rechnungsläufe'">

<div class="seiten-kopf" style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Rechnungsläufe</h2>
    <a href="{{ route('rechnungslauf.create') }}" class="btn btn-primaer">+ Neuer Rechnungslauf</a>
</div>

@if(session('erfolg'))
    <div class="meldung meldung-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
@endif

{{-- Filter --}}
<form method="GET" action="{{ route('rechnungslauf.index') }}" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; align-items: center;">
    <select name="jahr" class="feld" style="width: 100px;" onchange="this.form.submit()">
        <option value="">Alle Jahre</option>
        @foreach($jahre as $j)
            <option value="{{ $j }}" @selected($j == $jahr)>{{ $j }}</option>
        @endforeach
    </select>
    <select name="monat" class="feld" style="width: 120px;" onchange="this.form.submit()">
        <option value="">Alle Monate</option>
        @foreach(['1'=>'Januar','2'=>'Februar','3'=>'März','4'=>'April','5'=>'Mai','6'=>'Juni','7'=>'Juli','8'=>'August','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Dezember'] as $m => $name)
            <option value="{{ $m }}" @selected($m == $monat)>{{ $name }}</option>
        @endforeach
    </select>
    @if($jahr || $monat)
        <a href="{{ route('rechnungslauf.index') }}" class="btn btn-sekundaer">× Zurücksetzen</a>
    @endif
</form>

<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>#</th>
                <th>Datum</th>
                <th>Periode</th>
                <th>Typ</th>
                <th class="text-rechts">Erstellt</th>
                <th class="text-rechts">Übersprungen</th>
                <th class="text-rechts">Total CHF</th>
                <th>Erstellt von</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($laeufe as $lauf)
            <tr>
                <td style="font-family: monospace; font-size: 0.8125rem;">{{ $lauf->id }}</td>
                <td style="font-size: 0.8125rem;">{{ $lauf->created_at->format('d.m.Y H:i') }}</td>
                <td style="font-size: 0.8125rem;">
                    {{ $lauf->periode_von->format('d.m.Y') }} – {{ $lauf->periode_bis->format('d.m.Y') }}
                </td>
                <td>
                    @php
                        $typen = ['kombiniert' => 'Kombiniert', 'kvg' => 'KVG', 'klient' => 'Klient', 'gemeinde' => 'Gemeinde'];
                        echo $typen[$lauf->rechnungstyp] ?? $lauf->rechnungstyp;
                    @endphp
                </td>
                <td class="text-rechts text-fett">{{ $lauf->anzahl_erstellt }}</td>
                <td class="text-rechts text-hell">{{ $lauf->anzahl_uebersprungen }}</td>
                <td class="text-rechts text-fett">CHF {{ number_format($lauf->rechnungen_sum_betrag_total ?? 0, 2, '.', "'") }}</td>
                <td class="text-hell" style="font-size: 0.8125rem;">{{ $lauf->ersteller->nachname ?? '—' }}</td>
                <td class="text-rechts">
                    <a href="{{ route('rechnungslauf.show', $lauf) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Noch keine Rechnungsläufe vorhanden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($laeufe->hasPages())
<div style="margin-top: 1rem;">{{ $laeufe->links() }}</div>
@endif

</x-layouts.app>
