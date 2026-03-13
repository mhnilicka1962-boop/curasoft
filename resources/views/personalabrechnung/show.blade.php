<x-layouts.app title="Personalabrechnung — {{ $benutzer->vorname }} {{ $benutzer->nachname }}">
@php
    function paShowMinToH(int $min): string {
        if ($min <= 0) return '0:00';
        return intdiv($min, 60) . ':' . str_pad($min % 60, 2, '0', STR_PAD_LEFT);
    }
    $totalPlan = $einsaetze->sum('minuten');
    $totalIst  = $einsaetze->whereNotNull('ist_minuten')->sum('ist_minuten');
    $totalDiff = $totalIst - $totalPlan;
    $diffSign  = $totalDiff >= 0 ? '+' : '−';
    $diffAbs   = abs($totalDiff);
@endphp

<div class="seiten-kopf">
    <div>
        <a href="{{ route('personalabrechnung.index', ['monat' => $monat]) }}" class="link-gedaempt">← Personalabrechnung</a>
        <h1 style="margin-top:.25rem;">{{ $benutzer->vorname }} {{ $benutzer->nachname }}</h1>
        <div class="text-klein text-hell">
            <span class="badge badge-grau">{{ ucfirst($benutzer->rolle) }}</span>
            @if($benutzer->anstellungsart && $benutzer->anstellungsart !== 'fachperson')
                <span class="badge badge-info">{{ ucfirst($benutzer->anstellungsart) }}</span>
            @endif
            @if($benutzer->pensum)
                <span class="text-hell"> · Pensum {{ $benutzer->pensum }}%</span>
            @endif
        </div>
    </div>
    <div style="display:flex; gap:.5rem;">
        <a href="{{ route('personalabrechnung.pdf', [$benutzer->id, 'monat' => $monat]) }}"
           class="btn btn-primaer" target="_blank">
            PDF Zeitnachweis
        </a>
        <a href="{{ route('personalabrechnung.export', [$benutzer->id, 'monat' => $monat]) }}"
           class="btn btn-sekundaer">
            CSV Export
        </a>
    </div>
</div>

{{-- Monat-Filter --}}
<form method="GET" action="{{ route('personalabrechnung.show', $benutzer->id) }}" style="margin-bottom:1.5rem;">
    <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
        <select name="monat" class="feld" style="width:auto;" onchange="this.form.submit()">
            @foreach($monate as $m)
                <option value="{{ $m }}" {{ $m === $monat ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->locale('de')->isoFormat('MMMM YYYY') }}
                </option>
            @endforeach
        </select>
        <span class="text-hell text-klein">{{ $von->format('d.m.Y') }} – {{ $bis->format('d.m.Y') }}</span>
    </div>
</form>

{{-- Zusammenfassung --}}
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Einsätze</div>
        <div style="font-size:1.75rem; font-weight:700;">{{ $einsaetze->count() }}</div>
        <div class="text-mini text-hell">{{ $einsaetze->where('status','abgeschlossen')->count() }} abgeschlossen</div>
    </div>
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Geplant</div>
        <div style="font-size:1.75rem; font-weight:700;">{{ paShowMinToH($totalPlan) }}</div>
        <div class="text-mini text-hell">{{ $totalPlan }} Minuten</div>
    </div>
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Geleistet</div>
        <div style="font-size:1.75rem; font-weight:700; color:var(--cs-primaer);">{{ paShowMinToH($totalIst) }}</div>
        <div class="text-mini text-hell">{{ $einsaetze->whereNotNull('ist_minuten')->count() }} mit Check-out</div>
    </div>
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Abweichung</div>
        @php $diffKlasse = $totalDiff >= 0 ? 'var(--cs-primaer)' : 'var(--cs-fehler)'; @endphp
        <div style="font-size:1.75rem; font-weight:700; color:{{ $diffKlasse }};">
            {{ $diffSign }}{{ paShowMinToH($diffAbs) }}
        </div>
        <div class="text-mini text-hell">Ist minus Soll</div>
    </div>
</div>

{{-- Einsatz-Tabelle --}}
@if($einsaetze->isEmpty())
    <div class="info-box">Keine Einsätze in diesem Monat.</div>
@else
<div class="karte-null">
    <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Klient</th>
                    <th class="col-desktop">Leistungsart</th>
                    <th class="text-mitte">Geplant</th>
                    <th class="text-mitte">Ist</th>
                    <th class="text-mitte col-desktop">Differenz</th>
                    <th class="text-mitte col-desktop">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $currentDay = null; @endphp
                @foreach($einsaetze as $e)
                    @php
                        $planMin = (int) ($e->minuten ?? 0);
                        $istMin  = $e->ist_minuten;
                        $diff    = $istMin !== null ? ($istMin - $planMin) : null;
                        $diffKl  = $diff === null ? '' : ($diff >= 0 ? 'text-primaer' : 'badge badge-fehler');
                    @endphp
                    <tr>
                        <td>
                            <div class="text-fett">{{ $e->datum->format('d.m.') }}</div>
                            <div class="text-mini text-hell">{{ $e->datum->locale('de')->isoFormat('ddd') }}</div>
                        </td>
                        <td>
                            <div>{{ $e->klient?->vorname }} {{ $e->klient?->nachname }}</div>
                            <div class="text-mini text-hell mobile-meta">{{ $e->leistungsart?->bezeichnung }}</div>
                        </td>
                        <td class="col-desktop text-klein text-hell">{{ $e->leistungsart?->bezeichnung ?? '—' }}</td>
                        <td class="text-mitte">
                            @if($e->zeit_von)
                                <span class="text-klein">{{ $e->zeit_von }}–{{ $e->zeit_bis }}</span><br>
                            @endif
                            <span class="text-fett">{{ paShowMinToH($planMin) }}</span>
                        </td>
                        <td class="text-mitte">
                            @if($istMin !== null)
                                @if($e->checkin_zeit)
                                    <span class="text-klein text-hell">{{ $e->checkin_zeit->format('H:i') }}–{{ $e->checkout_zeit->format('H:i') }}</span><br>
                                @endif
                                <span class="text-fett">{{ paShowMinToH($istMin) }}</span>
                            @elseif($e->status === 'abgeschlossen')
                                <span class="text-hell text-mini">kein Check-out</span>
                            @else
                                <span class="text-hell">—</span>
                            @endif
                        </td>
                        <td class="text-mitte col-desktop">
                            @if($diff !== null)
                                @php
                                    $dSign = $diff >= 0 ? '+' : '−';
                                    $dAbs  = abs($diff);
                                @endphp
                                <span class="{{ $diffKl }}">{{ $dSign }}{{ paShowMinToH($dAbs) }}</span>
                            @else
                                <span class="text-hell">—</span>
                            @endif
                        </td>
                        <td class="text-mitte col-desktop">
                            <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--cs-border); font-weight:600;">
                    <td colspan="3">Total</td>
                    <td class="text-mitte">{{ paShowMinToH($totalPlan) }}</td>
                    <td class="text-mitte">{{ paShowMinToH($totalIst) }}</td>
                    <td class="text-mitte col-desktop">
                        @php $tDiffSign = $totalDiff >= 0 ? '+' : '−'; @endphp
                        <span style="color:{{ $totalDiff >= 0 ? 'var(--cs-primaer)' : 'var(--cs-fehler)' }}">
                            {{ $tDiffSign }}{{ paShowMinToH(abs($totalDiff)) }}
                        </span>
                    </td>
                    <td class="col-desktop"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

<p class="text-mini text-hell" style="margin-top:1rem;">
    Geleistet = Zeit aus Check-in/Check-out. Geplant = erfasste Einsatz-Minuten.
    Einsätze ohne Check-out werden in der Summe nicht als Ist-Zeit gezählt.
</p>
</x-layouts.app>
