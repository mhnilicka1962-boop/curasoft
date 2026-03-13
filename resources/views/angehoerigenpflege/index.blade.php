<x-layouts.app title="Angehörigenpflege">
@php
    function apMinH(int $min): string {
        if ($min <= 0) return '—';
        return intdiv($min, 60) . ':' . str_pad($min % 60, 2, '0', STR_PAD_LEFT) . ' h';
    }
    $aktive   = $verhaeltnisse->where('aktiv', true);
    $inaktive = $verhaeltnisse->where('aktiv', false);
    $monat    = now()->locale('de')->isoFormat('MMMM YYYY');
@endphp

<div class="seiten-kopf">
    <h1>Angehörigenpflege</h1>
    <a href="{{ route('mitarbeiter.create') }}?anstellungsart=angehoerig" class="btn btn-primaer">
        + Neuer Angehöriger
    </a>
</div>

{{-- Erklärungs-Box --}}
<div style="background:var(--cs-hintergrund); border:1px solid var(--cs-border); border-left:4px solid var(--cs-primaer); border-radius:var(--cs-radius); padding:1rem 1.25rem; margin-bottom:1.5rem;">
    <div class="text-fett" style="margin-bottom:.4rem;">Was ist Angehörigenpflege?</div>
    <div class="text-klein" style="color:var(--cs-text-hell); line-height:1.6;">
        Pflegende Angehörige werden bei Ihnen als Mitarbeitende angestellt und erbringen Pflegeleistungen für ihre Familienmitglieder.
        Die Spitex übernimmt die fachliche Aufsicht, Erstbeurteilung und Lohnabrechnung.
        Vergütung: ca. <strong>CHF 37.90/h</strong> inkl. Sozialversicherungen · Nur Grundpflege &amp; Hauswirtschaft erlaubt (KLV) ·
        Alle 6 Monate Reassessment.
    </div>
    <div class="text-mini" style="margin-top:.5rem; color:#aaa;">
        Angehörige loggen sich mit ihrer E-Mail-Adresse ein (Rolle: Pflege) — sie sehen nur ihre eigenen Einsätze und ihre Arbeitszeit.
    </div>
</div>

{{-- Statistik-Chips --}}
@php $totalMin = $monatsStats->sum('plan_min'); @endphp
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Aktive Verhältnisse</div>
        <div style="font-size:1.75rem; font-weight:700; color:var(--cs-primaer);">{{ $aktive->count() }}</div>
    </div>
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Einsätze {{ $monat }}</div>
        <div style="font-size:1.75rem; font-weight:700;">{{ $monatsStats->sum('anzahl') }}</div>
    </div>
    <div class="karte" style="flex:1; min-width:130px; text-align:center;">
        <div class="text-mini text-hell" style="text-transform:uppercase; letter-spacing:.05em;">Stunden {{ $monat }}</div>
        <div style="font-size:1.75rem; font-weight:700; color:var(--cs-primaer);">{{ apMinH($totalMin) }}</div>
    </div>
</div>

{{-- Aktive Verhältnisse --}}
@if($aktive->isEmpty())
    <div class="info-box">
        Noch keine aktiven Angehörigenpflege-Verhältnisse.<br>
        <strong>So geht's:</strong>
        <ol style="margin-top:.5rem; padding-left:1.25rem; line-height:1.8;">
            <li>Angehörigen unter <a href="{{ route('mitarbeiter.create') }}" class="link-primaer">Mitarbeitende → + Neu</a> erfassen — Anstellungsart: «Pflegender Angehöriger»</li>
            <li>Unter <em>Mitarbeiter-Detail → Zugewiesene Klienten</em> den betreuten Klienten zuweisen (Beziehung: «Pflegend tätig»)</li>
            <li>Einsätze für den Klienten erstellen — Mitarbeiter = Angehöriger</li>
        </ol>
    </div>
@else
<div class="karte-null" style="margin-bottom:1.5rem;">
    <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Angehöriger / Angehörige</th>
                    <th>Betreuter Klient</th>
                    <th class="text-mitte col-desktop">Kanton</th>
                    <th class="text-mitte">Einsätze {{ now()->format('M') }}</th>
                    <th class="text-mitte">Stunden {{ now()->format('M') }}</th>
                    <th class="text-mitte col-desktop">Letzter Einsatz</th>
                    <th class="col-desktop">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aktive as $v)
                @php
                    $stats       = $monatsStats->get($v->benutzer_id);
                    $letzter     = $letzteEinsaetze->get($v->benutzer_id);
                    $planMin     = (int)($stats?->plan_min ?? 0);
                    $anzahl      = (int)($stats?->anzahl ?? 0);
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('mitarbeiter.show', $v->benutzer_id) }}" class="link-primaer text-fett">
                            {{ $v->benutzer?->vorname }} {{ $v->benutzer?->nachname }}
                        </a>
                        <div class="text-mini text-hell mobile-meta">→ {{ $v->klient?->vorname }} {{ $v->klient?->nachname }}</div>
                        @if($v->benutzer?->email)
                            <div class="text-mini text-hell col-desktop">{{ $v->benutzer->email }}</div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('klienten.show', $v->klient_id) }}" class="link-primaer">
                            {{ $v->klient?->vorname }} {{ $v->klient?->nachname }}
                        </a>
                        @if($v->rolle)
                            <div class="text-mini">
                                <span class="badge badge-grau">{{ ucfirst($v->rolle) }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="text-mitte col-desktop">
                        @if($v->klient?->region)
                            <span class="badge badge-info">{{ $v->klient->region->kuerzel }}</span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($anzahl > 0)
                            <span class="text-fett">{{ $anzahl }}</span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($planMin > 0)
                            <span class="text-fett">{{ apMinH($planMin) }}</span>
                        @else
                            <span class="text-hell">—</span>
                        @endif
                    </td>
                    <td class="text-mitte col-desktop">
                        @if($letzter)
                            <span class="text-klein">{{ $letzter->datum->format('d.m.Y') }}</span>
                        @else
                            <span class="text-hell text-mini">Noch kein Einsatz</span>
                        @endif
                    </td>
                    <td class="col-desktop">
                        <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
                            <a href="{{ route('einsaetze.create', ['klient_id' => $v->klient_id, 'benutzer_id' => $v->benutzer_id]) }}"
                               class="btn btn-primaer" style="font-size:.75rem; padding:.2rem .5rem;" title="Neuer Einsatz">
                                + Einsatz
                            </a>
                            <a href="{{ route('personalabrechnung.show', [$v->benutzer_id, 'monat' => now()->format('Y-m')]) }}"
                               class="btn btn-sekundaer" style="font-size:.75rem; padding:.2rem .5rem;" title="Arbeitszeit / Lohnabrechnung">
                                Arbeitszeit
                            </a>
                            <a href="{{ route('einsaetze.index', ['benutzer_id' => $v->benutzer_id]) }}"
                               class="btn btn-sekundaer" style="font-size:.75rem; padding:.2rem .5rem;">
                                Einsätze
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Inaktive --}}
@if($inaktive->count())
<details style="margin-top:1rem;">
    <summary class="text-klein text-hell" style="cursor:pointer;">{{ $inaktive->count() }} inaktive Verhältnisse anzeigen</summary>
    <div class="karte-null" style="margin-top:.5rem;">
        <div class="tabelle-wrapper">
            <table class="tabelle">
                <thead>
                    <tr>
                        <th>Angehöriger</th>
                        <th>Klient</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inaktive as $v)
                    <tr style="opacity:.6;">
                        <td>{{ $v->benutzer?->vorname }} {{ $v->benutzer?->nachname }}</td>
                        <td>{{ $v->klient?->vorname }} {{ $v->klient?->nachname }}</td>
                        <td>
                            <a href="{{ route('mitarbeiter.show', $v->benutzer_id) }}" class="link-gedaempt">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</details>
@endif

{{-- Anleitung --}}
<div class="karte" style="margin-top:2rem; border-left:4px solid var(--cs-warnung);">
    <div class="abschnitt-label" style="margin-bottom:.75rem;">Checkliste — Neues Pflegeverhältnis einrichten</div>
    <div style="display:grid; grid-template-columns:1.5rem 1fr; gap:.4rem .75rem; font-size:.875rem; line-height:1.5;">
        <span style="color:var(--cs-primaer); font-weight:700;">1.</span>
        <span><a href="{{ route('mitarbeiter.create') }}" class="link-primaer">Angehörigen als Mitarbeitenden erfassen</a> — Anstellungsart: «Pflegender Angehöriger», Rolle: «Pflege», E-Mail-Adresse setzen</span>
        <span style="color:var(--cs-primaer); font-weight:700;">2.</span>
        <span>Mitarbeiter-Detail öffnen → <strong>Zugewiesene Klienten</strong> → betreuten Klienten hinzufügen, Beziehung: «Pflegend tätig»</span>
        <span style="color:var(--cs-primaer); font-weight:700;">3.</span>
        <span>Angehörigen per <strong>Einladungs-E-Mail</strong> einladen (Button im Mitarbeiter-Detail) — er/sie loggt sich dann wie normale Pflege ein</span>
        <span style="color:var(--cs-primaer); font-weight:700;">4.</span>
        <span>Einsätze planen: Klient wählen, Mitarbeiter = Angehöriger — System setzt Leistungserbringer-Typ automatisch auf «Pflegender Angehöriger»</span>
        <span style="color:var(--cs-primaer); font-weight:700;">5.</span>
        <span><strong>Alle 6 Monate</strong>: Pflegebedarf neu beurteilen, Rapport erstellen, Beitrag aktualisieren</span>
        <span style="color:var(--cs-primaer); font-weight:700;">6.</span>
        <span>Monatliche Lohnabrechnung: <a href="{{ route('personalabrechnung.index') }}" class="link-primaer">Personalabrechnung</a> → Mitarbeiter wählen → PDF Zeitnachweis</span>
    </div>
</div>

</x-layouts.app>
