<x-layouts.app title="Personalabrechnung">
@php
    function paMinToH(int $min): string {
        if ($min <= 0) return '0:00';
        return intdiv($min, 60) . ':' . str_pad($min % 60, 2, '0', STR_PAD_LEFT);
    }
    function paDiff(int $plan, int $ist): string {
        $d = $ist - $plan;
        $sign = $d >= 0 ? '+' : '−';
        $abs  = abs($d);
        return $sign . intdiv($abs, 60) . ':' . str_pad($abs % 60, 2, '0', STR_PAD_LEFT);
    }
    function paDiffKlasse(int $plan, int $ist): string {
        $d = $ist - $plan;
        if ($d > 30)  return 'text-primaer';
        if ($d < -30) return 'badge badge-fehler';
        return 'text-hell';
    }
@endphp

<div class="seiten-kopf">
    <h1>Personalabrechnung</h1>
</div>

{{-- Monat-Filter --}}
<form method="GET" action="{{ route('personalabrechnung.index') }}" style="margin-bottom: 1.5rem;">
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
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
@php
    $totalPlan = $mitarbeiter->sum('stat_plan_min');
    $totalIst  = $mitarbeiter->sum('stat_ist_min');
    $totalEins = $mitarbeiter->sum('stat_anzahl');
    $totalAbg  = $mitarbeiter->sum('stat_abgeschlossen');
@endphp
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
    <div class="karte" style="flex:1; min-width:140px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Mitarbeitende</div>
        <div style="font-size:1.75rem; font-weight:700; color:var(--cs-primaer);">{{ $mitarbeiter->where('stat_anzahl', '>', 0)->count() }}</div>
        <div class="text-mini text-hell">mit Einsätzen</div>
    </div>
    <div class="karte" style="flex:1; min-width:140px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Einsätze</div>
        <div style="font-size:1.75rem; font-weight:700;">{{ $totalEins }}</div>
        <div class="text-mini text-hell">{{ $totalAbg }} abgeschlossen</div>
    </div>
    <div class="karte" style="flex:1; min-width:140px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Geplant</div>
        <div style="font-size:1.75rem; font-weight:700;">{{ paMinToH($totalPlan) }}</div>
        <div class="text-mini text-hell">Stunden</div>
    </div>
    <div class="karte" style="flex:1; min-width:140px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Geleistet</div>
        <div style="font-size:1.75rem; font-weight:700; color:var(--cs-primaer);">{{ paMinToH($totalIst) }}</div>
        <div class="text-mini text-hell">aus Check-in/out</div>
    </div>
</div>

{{-- Mitarbeitertabelle --}}
<div class="karte-null">
    <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Mitarbeiterin / Mitarbeiter</th>
                    <th class="text-mitte col-desktop">Rolle</th>
                    <th class="text-mitte">Einsätze</th>
                    <th class="text-mitte col-desktop">Abgeschl.</th>
                    <th class="text-mitte">Geplant</th>
                    <th class="text-mitte">Geleistet</th>
                    <th class="text-mitte col-desktop">Abweichung</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($mitarbeiter as $ma)
                <tr style="{{ $ma->stat_anzahl === 0 ? 'opacity:.5;' : '' }}">
                    <td>
                        <div class="text-fett">{{ $ma->vorname }} {{ $ma->nachname }}</div>
                        <div class="text-mini text-hell mobile-meta">
                            {{ ucfirst($ma->rolle) }}
                            @if($ma->pensum) · {{ $ma->pensum }}% @endif
                        </div>
                    </td>
                    <td class="text-mitte col-desktop">
                        <span class="badge badge-grau">{{ ucfirst($ma->rolle) }}</span>
                        @if($ma->anstellungsart && $ma->anstellungsart !== 'fachperson')
                            <span class="badge badge-info">{{ ucfirst($ma->anstellungsart) }}</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($ma->stat_anzahl > 0)
                            <span class="text-fett">{{ $ma->stat_anzahl }}</span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte col-desktop">
                        @if($ma->stat_abgeschlossen > 0)
                            {{ $ma->stat_abgeschlossen }}
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte">{{ paMinToH($ma->stat_plan_min) }}</td>
                    <td class="text-mitte">
                        @if($ma->stat_ist_min > 0)
                            <span class="text-fett">{{ paMinToH($ma->stat_ist_min) }}</span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte col-desktop">
                        @if($ma->stat_ist_min > 0 && $ma->stat_plan_min > 0)
                            <span class="{{ paDiffKlasse($ma->stat_plan_min, $ma->stat_ist_min) }}">
                                {{ paDiff($ma->stat_plan_min, $ma->stat_ist_min) }}
                            </span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('personalabrechnung.show', [$ma->id, 'monat' => $monat]) }}"
                           class="btn btn-sekundaer" style="font-size:.8rem; padding:.25rem .6rem;">
                            Detail →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--cs-border); font-weight:600;">
                    <td>Total</td>
                    <td class="col-desktop"></td>
                    <td class="text-mitte">{{ $totalEins }}</td>
                    <td class="text-mitte col-desktop">{{ $totalAbg }}</td>
                    <td class="text-mitte">{{ paMinToH($totalPlan) }}</td>
                    <td class="text-mitte">{{ paMinToH($totalIst) }}</td>
                    <td class="text-mitte col-desktop">
                        @if($totalIst > 0)
                            <span class="{{ paDiffKlasse($totalPlan, $totalIst) }}">
                                {{ paDiff($totalPlan, $totalIst) }}
                            </span>
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p class="text-mini text-hell" style="margin-top:1rem;">
    Geleistet = Zeit aus Check-in/Check-out (nur abgeschlossene Einsätze mit beiden Zeitstempeln). Geplant = erfasste Einsatz-Minuten.
</p>
</x-layouts.app>
