<x-layouts.app :titel="$rechnung->rechnungsnummer">
<div style="max-width: 820px;">

    <div class="seiten-kopf">
        @if($rechnung->rechnungslauf_id)
            <a href="{{ route('rechnungslauf.show', $rechnung->rechnungslauf_id) }}" class="link-gedaempt" style="font-size: 0.875rem;">← Rechnungslauf #{{ $rechnung->rechnungslauf_id }}</a>
        @else
            <a href="{{ route('rechnungen.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Alle Rechnungen</a>
        @endif
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {!! $rechnung->typBadge() !!}
            {!! $rechnung->statusBadge() !!}
            {{-- Status-Aktionen --}}
            @if($rechnung->status === 'entwurf')
                <form method="POST" action="{{ route('rechnungen.status', $rechnung) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="gesendet">
                    <button type="submit" class="btn btn-primaer">📨 Gesendet</button>
                </form>
            @endif
            @if($rechnung->status === 'gesendet')
                <form method="POST" action="{{ route('rechnungen.status', $rechnung) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="bezahlt">
                    <button type="submit" class="btn btn-primaer" style="background-color: var(--cs-erfolg);">✓ Bezahlt</button>
                </form>
            @endif
            @if(in_array($rechnung->status, ['entwurf', 'gesendet']))
                <form method="POST" action="{{ route('rechnungen.stornieren', $rechnung) }}" style="display:inline;"
                    onsubmit="return confirm('Rechnung {{ $rechnung->rechnungsnummer }} stornieren?\n\nDie Einsätze werden wieder als «unverrechnet» gesetzt.\nDie Rechnungsnummer bleibt erhalten.')">
                    @csrf
                    <button type="submit" class="btn btn-gefahr">✕ Stornieren</button>
                </form>
            @endif
            {{-- Gemeinde-Email erneut senden (nur Tiers payant) --}}
            @if(($rechnung->lauf?->abrechnungslogik ?? 'tiers_garant') === 'tiers_payant' && $rechnung->klient->gemeinde_email)
            <form method="POST" action="{{ route('rechnungen.gemeinde-email', $rechnung) }}" style="display:inline;"
                onsubmit="return confirm('Gemeinde-Email erneut senden an {{ $rechnung->klient->gemeinde_email }}?')">
                @csrf
                <button type="submit" class="btn btn-aktion">📧 Gemeinde-Email</button>
            </form>
            @endif
            {{-- XML 450.100 Export --}}
            <a href="{{ route('rechnungen.xml', $rechnung) }}" class="btn btn-sekundaer" title="XML 450.100 exportieren">📋 XML</a>
            {{-- PDF Export --}}
            <a href="{{ route('rechnungen.pdf', $rechnung) }}" class="btn btn-sekundaer" title="PDF herunterladen">📄 PDF</a>
            {{-- Bexio Sync + Status --}}
            @if(auth()->user()->organisation->bexio_api_key)
            <form method="POST" action="{{ route('rechnungen.bexio.sync', $rechnung) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sekundaer" title="{{ $rechnung->bexio_rechnung_id ? 'Bexio-Rechnung aktualisieren (ID: '.$rechnung->bexio_rechnung_id.')' : 'Rechnung in Bexio anlegen' }}">
                    {{ $rechnung->bexio_rechnung_id ? '↻ Bexio' : '→ Bexio' }}
                </button>
            </form>
            @if($rechnung->bexio_rechnung_id && $rechnung->status !== 'bezahlt')
            <form method="POST" action="{{ route('rechnungen.bexio.status', $rechnung) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sekundaer" title="Zahlungsstatus von Bexio abrufen">
                    ✓ Bexio bezahlt?
                </button>
            </form>
            @endif
            @if($rechnung->bexio_bezahlt_am)
            <span class="badge badge-erfolg" title="Von Bexio als bezahlt gemeldet am {{ $rechnung->bexio_bezahlt_am->format('d.m.Y H:i') }}">
                Bexio ✓ {{ $rechnung->bexio_bezahlt_am->format('d.m.Y') }}
            </span>
            @endif
            @endif
        </div>
    </div>

    {{-- Header --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="form-grid-3" style="flex-wrap: wrap;">
            <div>
                <div class="detail-label">Rechnungsnummer</div>
                <div style="font-size: 1.125rem; font-weight: 700; font-family: monospace;">{{ $rechnung->rechnungsnummer }}</div>
            </div>
            <div>
                <div class="detail-label">Klient</div>
                <div class="text-fett">
                    <a href="{{ route('klienten.show', $rechnung->klient) }}" class="link-primaer">
                        {{ $rechnung->klient->vorname }} {{ $rechnung->klient->nachname }}
                    </a>
                </div>
                <div class="text-hell" style="font-size: 0.8125rem;">{{ $rechnung->klient->adresse }}, {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}</div>
            </div>
            <div>
                <div class="detail-label">Periode / Datum</div>
                <div class="text-mittel">{{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}</div>
                <div class="text-hell" style="font-size: 0.8125rem;">Ausgestellt: {{ $rechnung->rechnungsdatum->format('d.m.Y') }}</div>
            </div>
            <div>
                <div class="detail-label">Zahlungseingang</div>
                @if($rechnung->status !== 'storniert')
                <form method="POST" action="{{ route('rechnungen.bezahlt-am', $rechnung) }}" style="display:flex; gap:0.375rem; align-items:center;">
                    @csrf @method('PATCH')
                    <input type="date" name="bezahlt_am" class="feld" style="width:145px; font-size:0.8125rem; padding:0.25rem 0.5rem;"
                        value="{{ $rechnung->bezahlt_am?->format('Y-m-d') ?? '' }}">
                    <button type="submit" class="btn btn-sekundaer" style="padding:0.25rem 0.5rem; font-size:0.8125rem;">✓</button>
                </form>
                @else
                    <span class="text-hell">—</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Positionen --}}
    <div class="karte-null" style="margin-bottom: 1rem;">
        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);" class="abschnitt-label">
            Positionen
        </div>
        @php
            $nurKK         = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['kvg']);
            $nurPatient    = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['klient', 'gemeinde']);
            $beide         = !$nurKK && !$nurPatient;
            $kkLabel       = $rechnung->rechnungstyp === 'gemeinde' ? 'Gemeinde' : 'KK';
            $tiersPayant   = ($abrechLogik ?? 'tiers_garant') === 'tiers_payant' && !$nurKK;
        @endphp
        <table class="tabelle" style="font-size: 0.8125rem;">
            @if($tiersPayant)
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Leistung</th>
                        <th class="text-rechts">Min.</th>
                        <th class="text-rechts">Ansatz CHF/h</th>
                        <th class="text-rechts">Vollkosten CHF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rechnung->positionen->filter(fn($p) => $p->menge > 0) as $pos)
                    @php
                        $vollkostenAnsatz = (float)$pos->tarif_patient + (float)$pos->tarif_kk;
                        $vollkostenBetrag = (float)$pos->betrag_patient + (float)$pos->betrag_kk;
                        $bezeichnung      = $pos->einsatzLeistungsart?->leistungsart?->bezeichnung
                                          ?? $pos->leistungstyp?->bezeichnung
                                          ?? $pos->beschreibung
                                          ?? '—';
                    @endphp
                    <tr>
                        <td style="white-space:nowrap;">{{ $pos->datum->format('d.m.Y') }}</td>
                        <td class="text-hell">{{ $bezeichnung }}</td>
                        <td class="text-rechts">{{ $pos->menge }}</td>
                        <td class="text-rechts">{{ number_format($vollkostenAnsatz, 2) }}</td>
                        <td class="text-rechts">{{ number_format($vollkostenBetrag, 2, '.', "'") }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $vollkosten = (float)$rechnung->betrag_kk + (float)$rechnung->betrag_patient + (float)$rechnung->betrag_gemeinde;
                    @endphp
                    <tr style="background-color:var(--cs-hintergrund); font-weight:600;">
                        <td colspan="4" style="padding:0.5rem 0.75rem;">Summe Vollkosten</td>
                        <td class="text-rechts" style="padding:0.5rem 0.75rem;">CHF {{ number_format($vollkosten, 2, '.', "'") }}</td>
                    </tr>
                    @if($rechnung->betrag_kk > 0)
                    <tr>
                        <td colspan="4" style="padding:0.5rem 0.75rem;" class="text-hell">Krankenkasse-Anteil (direkt abgerechnet)</td>
                        <td class="text-rechts" style="padding:0.5rem 0.75rem;">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
                    </tr>
                    @endif
                    @if($rechnung->betrag_gemeinde > 0)
                    <tr>
                        <td colspan="4" style="padding:0.5rem 0.75rem;" class="text-hell">Gemeinde-Restfinanzierung (direkt abgerechnet)</td>
                        <td class="text-rechts" style="padding:0.5rem 0.75rem;">CHF {{ number_format($rechnung->betrag_gemeinde, 2, '.', "'") }}</td>
                    </tr>
                    @endif
                    <tr style="background-color:var(--cs-primaer-hell);">
                        <td colspan="4" style="padding:0.5rem 0.75rem; font-weight:700;">Ihr Anteil</td>
                        <td class="text-rechts" style="padding:0.5rem 0.75rem; font-weight:700; color:var(--cs-primaer);">
                            CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}
                        </td>
                    </tr>
                </tfoot>
            @else
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Leistung</th>
                        <th class="text-rechts">Min.</th>
                        @if($beide || $nurPatient)
                        <th class="text-rechts">Tarif Pat.</th>
                        @endif
                        @if($beide || $nurKK)
                        <th class="text-rechts">Tarif {{ $kkLabel }}</th>
                        @endif
                        @if($beide || $nurPatient)
                        <th class="text-rechts">Betrag Pat.</th>
                        @endif
                        @if($beide || $nurKK)
                        <th class="text-rechts">Betrag {{ $kkLabel }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($rechnung->positionen->filter(fn($p) => $p->menge > 0) as $pos)
                    <tr>
                        <td style="white-space:nowrap;">{{ $pos->datum->format('d.m.Y') }}</td>
                        <td class="text-hell">
                            {{ $pos->einsatzLeistungsart?->leistungsart?->bezeichnung ?? $pos->leistungstyp?->bezeichnung ?? $pos->beschreibung ?? '—' }}
                        </td>
                        <td class="text-rechts">{{ $pos->menge }}</td>
                        @if($beide || $nurPatient)
                        <td class="text-rechts">{{ number_format($pos->tarif_patient, 2) }}</td>
                        @endif
                        @if($beide || $nurKK)
                        <td class="text-rechts">{{ number_format($pos->tarif_kk, 2) }}</td>
                        @endif
                        @if($beide || $nurPatient)
                        <td class="text-rechts">{{ number_format($pos->betrag_patient, 2, '.', "'") }}</td>
                        @endif
                        @if($beide || $nurKK)
                        <td class="text-rechts">{{ number_format($pos->betrag_kk, 2, '.', "'") }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color:var(--cs-hintergrund); font-weight:600;">
                        <td colspan="3" style="padding:0.5rem 0.75rem;">Total</td>
                        @if($beide || $nurPatient) <td></td> @endif
                        @if($beide || $nurKK) <td></td> @endif
                        @if($beide || $nurPatient)
                        <td class="text-rechts" style="padding:0.5rem 0.75rem;">CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}</td>
                        @endif
                        @if($beide || $nurKK)
                        <td class="text-rechts" style="padding:0.5rem 0.75rem;">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
                        @endif
                    </tr>
                    <tr style="background-color:var(--cs-primaer-hell);">
                        <td colspan="{{ $beide ? 5 : 4 }}" style="padding:0.5rem 0.75rem; font-weight:700;">Gesamttotal</td>
                        <td colspan="2" class="text-rechts" style="padding:0.5rem 0.75rem; font-weight:700; color:var(--cs-primaer);">
                            CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if(!empty($rapportDaten))
    {{-- Tages-Aufschlüsselung der Patientenbeteiligung (Tiers payant) --}}
    <div class="karte-null" style="margin-bottom: 1rem;">
        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);" class="abschnitt-label">
            Berechnung Ihres Anteils
        </div>
        <div style="padding: 0.875rem 1rem; font-size: 0.875rem; line-height: 1.5;">
            Die Patientenbeteiligung an KVG-Pflege wird <strong>pro Tag</strong> berechnet:
            <ul style="margin: 0.375rem 0 0 1.5rem;">
                <li>Patient zahlt max. <strong>{{ number_format($rapportDaten['beitrag']['limit_prozent'], 0) }}%</strong> des Netto-Anteils (Vollkosten − Krankenkasse)</li>
                <li>Gedeckelt auf <strong>CHF {{ number_format($rapportDaten['beitrag']['ansatz_kunde'], 2) }} pro Tag</strong></li>
                <li>Den Rest übernimmt die Gemeinde (Restfinanzierung)</li>
            </ul>
            <div class="text-hell" style="font-size: 0.75rem; margin-top: 0.5rem;">
                Hinweis: Nicht-KVG-Leistungen (z.B. Hauswirtschaft) werden voll vom Patient getragen und sind unten nicht aufgeführt.
            </div>
        </div>

        <table class="tabelle" style="font-size: 0.8125rem;">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th class="text-rechts">Vollkosten</th>
                    <th class="text-rechts">KK</th>
                    <th class="text-rechts">Netto</th>
                    <th class="text-rechts">Cap</th>
                    <th class="text-rechts">Patient</th>
                    <th class="text-rechts">Gemeinde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rapportDaten['tage'] as $tag)
                @php
                    $voll = (float)$tag['taxe_abkl'] + (float)$tag['taxe_unt'] + (float)$tag['taxe_gp'];
                    $kk   = (float)$tag['kvg_abkl']  + (float)$tag['kvg_unt']  + (float)$tag['kvg_gp'];
                @endphp
                @if($voll > 0)
                <tr>
                    <td style="white-space:nowrap;">{{ $tag['datum']->format('d.m.Y') }}</td>
                    <td class="text-rechts">{{ number_format($voll, 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($kk, 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($tag['netto'], 2, '.', "'") }}</td>
                    <td class="text-rechts text-hell" style="font-size:0.75rem;">
                        {{ $tag['pat_limit']
                            ? number_format($rapportDaten['beitrag']['limit_prozent'], 0).'% Limit'
                            : 'CHF '.number_format($rapportDaten['beitrag']['ansatz_kunde'], 2).' max' }}
                    </td>
                    <td class="text-rechts text-fett">{{ number_format($tag['pat'], 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($tag['gemeinde'], 2, '.', "'") }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $sVoll = $rapportDaten['summen']['taxe_abkl'] + $rapportDaten['summen']['taxe_unt'] + $rapportDaten['summen']['taxe_gp'];
                    $sKk   = $rapportDaten['summen']['kvg_abkl']  + $rapportDaten['summen']['kvg_unt']  + $rapportDaten['summen']['kvg_gp'];
                @endphp
                <tr style="background-color:var(--cs-hintergrund); font-weight:600;">
                    <td style="padding:0.5rem 0.75rem;">Total KVG</td>
                    <td class="text-rechts" style="padding:0.5rem 0.75rem;">{{ number_format($sVoll, 2, '.', "'") }}</td>
                    <td class="text-rechts" style="padding:0.5rem 0.75rem;">{{ number_format($sKk, 2, '.', "'") }}</td>
                    <td class="text-rechts" style="padding:0.5rem 0.75rem;">{{ number_format($rapportDaten['summen']['netto'], 2, '.', "'") }}</td>
                    <td></td>
                    <td class="text-rechts" style="padding:0.5rem 0.75rem;">{{ number_format($rapportDaten['summen']['pat'], 2, '.', "'") }}</td>
                    <td class="text-rechts" style="padding:0.5rem 0.75rem;">{{ number_format($rapportDaten['summen']['gemeinde'], 2, '.', "'") }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

</div>
</x-layouts.app>
