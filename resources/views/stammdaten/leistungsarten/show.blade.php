<x-layouts.app :titel="$leistungsart->bezeichnung . ' — Tarife'">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('leistungsarten.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Leistungsarten</a>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0 0;">{{ $leistungsart->bezeichnung }}</h1>
        <div class="text-klein text-hell">
            {{ $leistungsart->einheitLabel() }} &nbsp;·&nbsp;
            @if($leistungsart->kassenpflichtig)
                <span class="badge badge-erfolg">KVG</span>
            @else
                <span class="badge badge-grau">Privat</span>
            @endif
        </div>
    </div>
    <a href="{{ route('leistungsarten.edit', $leistungsart) }}" class="btn btn-sekundaer">Grundset bearbeiten</a>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif

@php $grouped = $tarife->groupBy('region_id'); @endphp

@foreach($grouped as $regionId => $gruppe)
@php $region = $gruppe->first()->region; @endphp
<div class="karte-null" style="overflow-x: auto; margin-bottom: 1.25rem;">
    <div style="padding: 0.625rem 1rem; background: var(--cs-hintergrund); border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between;">
        <span style="font-weight: 700;">{{ $region?->kuerzel }}
            <span class="text-hell" style="font-weight: 400; font-size: 0.875rem;">{{ $region?->bezeichnung }}</span>
        </span>
        <span class="text-mini text-hell">{{ $gruppe->count() }} {{ $gruppe->count() === 1 ? 'Eintrag' : 'Einträge' }}</span>
    </div>
    <table class="tabelle" style="min-width: 640px;">
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
            @foreach($gruppe->sortByDesc('gueltig_ab') as $loop_i => $t)
            @php $istAktuell = $loop_i === 0; @endphp
            <tr style="{{ !$istAktuell ? 'opacity: 0.45;' : '' }}">
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
                <td class="text-mitte" style="font-size: 0.9rem;">{{ $t->einsatz_minuten ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.9rem;">{{ $t->einsatz_stunden ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.9rem;">{{ $t->einsatz_tage ? '✓' : '' }}</td>
                <td class="text-mitte" style="font-size: 0.9rem;">{{ $t->mwst ? '✓' : '' }}</td>
                <td class="text-mini text-hell" style="white-space: nowrap;">{{ $t->updated_at?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-mini text-hell" style="white-space: nowrap;">{{ $t->created_at?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-rechts">
                    <a href="{{ route('leistungsarten.tarif.bearbeiten', [$leistungsart, $t]) }}"
                        class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✏</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

@if($tarife->isEmpty())
<div class="karte text-mitte text-hell" style="padding: 2.5rem;">
    Noch keine Kantone. Unter <a href="{{ route('regionen.index') }}">Regionen</a> einen Kanton anlegen.
</div>
@endif

</x-layouts.app>
