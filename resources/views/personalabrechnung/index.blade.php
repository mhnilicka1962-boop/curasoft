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

<div class="seiten-kopf" style="margin-bottom: 1rem;">
    <h1>Personalabrechnung</h1>
</div>

{{-- Filter + Export --}}
<div class="seiten-kopf" style="margin-bottom: 1.5rem;">
    <form id="filter-form" method="GET" action="{{ route('personalabrechnung.index') }}" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
        <input type="text" name="suche" class="feld" style="width:180px;" placeholder="Name…" value="{{ $suche }}">
        <select name="jahr" class="feld" style="width:90px;" onchange="this.form.submit()">
            @foreach($jahre as $j)
                <option value="{{ $j }}" {{ $j === $jahr ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
        </select>
        <select name="monat" class="feld" style="width:120px;" onchange="this.form.submit()">
            @foreach(['1'=>'Januar','2'=>'Februar','3'=>'März','4'=>'April','5'=>'Mai','6'=>'Juni','7'=>'Juli','8'=>'August','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Dezember'] as $m => $name)
                <option value="{{ $m }}" {{ (int)$m === $monat ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        <a href="{{ route('personalabrechnung.index') }}" class="btn btn-sekundaer">Reset</a>
        <a href="{{ route('personalabrechnung.sammel-csv', ['jahr' => $jahr, 'monat' => $monat, 'suche' => $suche]) }}" class="btn btn-sekundaer">↓ Alle CSV</a>
        <a href="{{ route('personalabrechnung.sammel-pdf', ['jahr' => $jahr, 'monat' => $monat, 'suche' => $suche]) }}" class="btn btn-sekundaer">↓ Alle PDF</a>
    </form>
    <form method="POST" action="{{ route('personalabrechnung.sammel-mail') }}" style="display:inline;">
        @csrf
        <input type="hidden" name="jahr" value="{{ $jahr }}">
        <input type="hidden" name="monat" value="{{ $monat }}">
        <input type="hidden" name="suche" value="{{ $suche }}">
        <button type="submit" class="btn btn-sekundaer" onclick="return confirm('Zeitnachweis an alle Mitarbeitenden mit hinterlegter privater E-Mail senden?')">✉ Alle mailen</button>
    </form>
    <span class="text-hell text-klein">{{ $von->format('d.m.Y') }} – {{ $bis->format('d.m.Y') }}</span>
</div>

{{-- Flash --}}
@if(session('erfolg'))
    <div class="badge badge-erfolg" style="display:block; padding:.6rem 1rem; margin-bottom:1rem;">{{ session('erfolg') }}</div>
@endif
@if(session('fehler'))
    <div class="badge badge-fehler" style="display:block; padding:.6rem 1rem; margin-bottom:1rem;">{{ session('fehler') }}</div>
@endif

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
                @foreach($mitarbeiter->filter(fn($ma) => $ma->stat_plan_min > 0 || $ma->stat_ist_min > 0) as $ma)
                <tr>
                    <td>
                        <a href="{{ route('mitarbeiter.show', $ma->id) }}" class="link-gedaempt text-fett">{{ $ma->vorname }} {{ $ma->nachname }}</a>
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
                    <td style="white-space:nowrap;">
                        <a href="{{ route('personalabrechnung.show', [$ma->id, 'jahr' => $jahr, 'monat' => $monat]) }}"
                           class="btn btn-sekundaer" style="font-size:.8rem; padding:.25rem .6rem;">
                            Detail →
                        </a>
                        @if($ma->email_privat)
                            <form method="POST" action="{{ route('personalabrechnung.mail', $ma->id) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="jahr" value="{{ $jahr }}">
                                <input type="hidden" name="monat" value="{{ $monat }}">
                                <button type="submit" class="btn btn-sekundaer" style="font-size:.8rem; padding:.25rem .6rem;" title="{{ $ma->email_privat }}">✉</button>
                            </form>
                        @else
                            <span class="text-hell" style="font-size:.8rem;" title="Keine private E-Mail hinterlegt">✉ —</span>
                        @endif
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
