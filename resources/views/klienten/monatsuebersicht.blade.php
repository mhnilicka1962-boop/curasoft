@php
    $monatNamen = ['','Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
    $vormonat = \Carbon\Carbon::create($jahr, $monat, 1)->subMonth();
    $nachmonat = \Carbon\Carbon::create($jahr, $monat, 1)->addMonth();
@endphp

<div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
    <a href="{{ route('klienten.monatsuebersicht', [$klient, 'monat' => $vormonat->month, 'jahr' => $vormonat->year]) }}"
       hx-get="{{ route('klienten.monatsuebersicht', [$klient, 'monat' => $vormonat->month, 'jahr' => $vormonat->year]) }}"
       onclick="ladeMonate({{ $vormonat->month }}, {{ $vormonat->year }}); return false;"
       class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem;">←</a>

    <strong style="font-size: 0.9375rem; min-width: 130px; text-align: center;">
        {{ $monatNamen[$monat] }} {{ $jahr }}
    </strong>

    <a href="{{ route('klienten.monatsuebersicht', [$klient, 'monat' => $nachmonat->month, 'jahr' => $nachmonat->year]) }}"
       onclick="ladeMonate({{ $nachmonat->month }}, {{ $nachmonat->year }}); return false;"
       class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem;">→</a>

    <span style="font-size: 0.75rem; color: var(--cs-text-hell); margin-left: 0.5rem;">
        <span style="display: inline-block; width: 10px; height: 10px; background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 2px; margin-right: 3px;"></span>Geleistet
        <span style="display: inline-block; width: 10px; height: 10px; background: #fed7aa; border: 1px solid #fb923c; border-radius: 2px; margin: 0 3px 0 8px;"></span>Nicht geleistet
        <span style="display: inline-block; width: 10px; height: 10px; background: #dbeafe; border: 1px solid #93c5fd; border-radius: 2px; margin: 0 3px 0 8px;"></span>Geplant
    </span>
</div>

@if(empty($masterGefiltert))
    <p class="text-klein text-hell" style="padding: 0.5rem 0;">Keine Einsätze für {{ $monatNamen[$monat] }} {{ $jahr }}.</p>
@else
@if($fallback)
    <div class="info-box" style="margin-bottom: 0.75rem; font-size: 0.8125rem;">
        Keine Aktivitäten-Details erfasst — Ansicht zeigt Gesamtminuten pro Einsatz (Leistungsart + Zeit).
    </div>
@endif
<div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table style="border-collapse: collapse; font-size: 0.75rem; white-space: nowrap; min-width: 100%;">
        {{-- Header: Tage --}}
        <thead>
            <tr>
                <th style="text-align: left; padding: 0.25rem 0.5rem; background: var(--cs-hintergrund); position: sticky; left: 0; z-index: 2; min-width: 160px; font-size: 0.7rem; border-bottom: 2px solid var(--cs-border); border-right: 2px solid var(--cs-border);">
                    Leistungsart / Aktivität
                </th>
                @for($t = 1; $t <= $tage; $t++)
                    @php
                        $status = $tagStatus[$t] ?? null;
                        $bg = match($status) {
                            'abgeschlossen'   => '#d1fae5',
                            'offen'           => '#fed7aa',
                            'geplant_zukunft' => '#dbeafe',
                            default           => 'transparent',
                        };
                        $border = match($status) {
                            'abgeschlossen'   => '#6ee7b7',
                            'offen'           => '#fb923c',
                            'geplant_zukunft' => '#93c5fd',
                            default           => 'var(--cs-border)',
                        };
                        $wochentag = \Carbon\Carbon::create($jahr, $monat, $t)->isoFormat('dd');
                        $istWE = \Carbon\Carbon::create($jahr, $monat, $t)->isWeekend();
                    @endphp
                    <th style="text-align: center; padding: 0.2rem 0.3rem; background: {{ $bg }}; border-bottom: 2px solid {{ $border }}; border-right: 1px solid var(--cs-border); min-width: 32px; font-weight: 600; color: {{ $istWE ? 'var(--cs-primaer)' : 'var(--cs-text)' }};">
                        <div style="font-size: 0.65rem; font-weight: 400; color: var(--cs-text-hell);">{{ $wochentag }}</div>
                        {{ $t }}
                    </th>
                @endfor
                <th style="text-align: center; padding: 0.25rem 0.5rem; background: var(--cs-hintergrund); border-bottom: 2px solid var(--cs-border); border-left: 2px solid var(--cs-border); font-size: 0.7rem; min-width: 50px;">
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($masterGefiltert as $kategorie => $aktivitaeten)
                {{-- Kategorie-Header --}}
                <tr>
                    <td colspan="{{ $tage + 2 }}"
                        style="padding: 0.3rem 0.5rem; background: var(--cs-hintergrund); font-size: 0.7rem; font-weight: 700; color: var(--cs-text-hell); text-transform: uppercase; letter-spacing: 0.05em; border-top: 2px solid var(--cs-border); position: sticky; left: 0;">
                        {{ $kategorie }}
                    </td>
                </tr>

                @foreach($aktivitaeten as $aktivitaet)
                    @php
                        $hatDaten = isset($grid[$kategorie][$aktivitaet]) && array_sum($grid[$kategorie][$aktivitaet]) > 0;
                        $totalAkt = $hatDaten ? array_sum($grid[$kategorie][$aktivitaet]) : 0;
                    @endphp
                    @if($hatDaten)
                    <tr style="background: white;">
                        <td style="padding: 0.2rem 0.5rem; border-bottom: 1px solid var(--cs-border); border-right: 2px solid var(--cs-border); position: sticky; left: 0; background: white; z-index: 1; font-size: 0.75rem;">
                            {{ $aktivitaet }}
                        </td>
                        @for($t = 1; $t <= $tage; $t++)
                            @php $min = $grid[$kategorie][$aktivitaet][$t] ?? null; @endphp
                            <td style="text-align: center; padding: 0.2rem 0.3rem; border-bottom: 1px solid var(--cs-border); border-right: 1px solid var(--cs-border); color: {{ $min ? 'var(--cs-text)' : 'var(--cs-border)' }}; font-variant-numeric: tabular-nums;">
                                {{ $min ?: '' }}
                            </td>
                        @endfor
                        <td style="text-align: center; padding: 0.2rem 0.5rem; border-bottom: 1px solid var(--cs-border); border-left: 2px solid var(--cs-border); font-weight: 600; color: var(--cs-text);">
                            {{ $totalAkt }}
                        </td>
                    </tr>
                    @endif
                @endforeach
            @endforeach

            {{-- Total-Zeile pro Tag --}}
            <tr style="background: var(--cs-hintergrund); font-weight: 700; border-top: 2px solid var(--cs-border);">
                <td style="padding: 0.3rem 0.5rem; border-right: 2px solid var(--cs-border); position: sticky; left: 0; background: var(--cs-hintergrund); z-index: 1; font-size: 0.75rem;">
                    Total Min./Tag
                </td>
                @php $gesamtTotal = 0; @endphp
                @for($t = 1; $t <= $tage; $t++)
                    @php
                        $tagesTotal = 0;
                        foreach ($masterGefiltert as $kat => $akte) {
                            foreach ($akte as $name) {
                                $tagesTotal += $grid[$kat][$name][$t] ?? 0;
                            }
                        }
                        $gesamtTotal += $tagesTotal;
                    @endphp
                    <td style="text-align: center; padding: 0.2rem 0.3rem; border-right: 1px solid var(--cs-border); font-variant-numeric: tabular-nums; font-size: 0.75rem; color: {{ $tagesTotal ? 'var(--cs-text)' : 'var(--cs-text-hell)' }};">
                        {{ $tagesTotal ?: '' }}
                    </td>
                @endfor
                <td style="text-align: center; padding: 0.2rem 0.5rem; border-left: 2px solid var(--cs-border); font-size: 0.75rem;">
                    {{ $gesamtTotal }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

@php
    $offeneTage = collect($tagStatus)->filter(fn($s) => $s === 'offen')->count();
    $geleistetTage = collect($tagStatus)->filter(fn($s) => $s === 'abgeschlossen')->count();
    $geplanTage = collect($tagStatus)->filter(fn($s) => $s === 'geplant_zukunft')->count();
@endphp
@if($offeneTage > 0 || $geleistetTage > 0)
<div style="margin-top: 0.75rem; display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.8125rem;">
    @if($geleistetTage > 0)
        <span style="color: #065f46;">✓ {{ $geleistetTage }} {{ $geleistetTage === 1 ? 'Tag geleistet' : 'Tage geleistet' }}</span>
    @endif
    @if($offeneTage > 0)
        <span style="color: #c2410c;">⚠ {{ $offeneTage }} {{ $offeneTage === 1 ? 'Tag nicht geleistet' : 'Tage nicht geleistet' }}</span>
    @endif
    @if($geplanTage > 0)
        <span style="color: #1d4ed8;">◷ {{ $geplanTage }} {{ $geplanTage === 1 ? 'Tag geplant' : 'Tage geplant' }}</span>
    @endif
</div>
@endif
@endif
