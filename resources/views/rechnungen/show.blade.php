<x-layouts.app :titel="$rechnung->rechnungsnummer">
<div style="max-width: 820px;">

    <div class="seiten-kopf">
        <a href="{{ route('rechnungen.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Alle Rechnungen</a>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {!! $rechnung->typBadge() !!}
            {!! $rechnung->statusBadge() !!}
            {{-- XML 450.100 Export --}}
            <a href="{{ route('rechnungen.xml', $rechnung) }}" class="btn btn-sekundaer" title="XML 450.100 exportieren">📋 XML</a>
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
            {{-- PDF Export --}}
            <a href="{{ route('rechnungen.pdf', $rechnung) }}" class="btn btn-sekundaer" title="PDF herunterladen">📄 PDF</a>
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
        </div>
    </div>

    {{-- Positionen --}}
    <div class="karte-null" style="margin-bottom: 1rem;">
        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);" class="abschnitt-label">
            Positionen
        </div>
        @php
            $nurKK      = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['kvg']);
            $nurPatient = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['klient', 'gemeinde']);
            $beide      = !$nurKK && !$nurPatient;
            $kkLabel    = $rechnung->rechnungstyp === 'gemeinde' ? 'Gemeinde' : 'KK';
        @endphp
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th class="text-rechts">Minuten</th>
                    @if($beide || $nurPatient)
                    <th class="text-rechts">Tarif Patient/Std.</th>
                    @endif
                    @if($beide || $nurKK)
                    <th class="text-rechts">Tarif {{ $kkLabel }}/Std.</th>
                    @endif
                    @if($beide || $nurPatient)
                    <th class="text-rechts">Betrag Patient</th>
                    @endif
                    @if($beide || $nurKK)
                    <th class="text-rechts">Betrag {{ $kkLabel }}</th>
                    @endif
                    @if($rechnung->status === 'entwurf') <th></th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rechnung->positionen as $pos)
                <tr>
                    <td style="font-size: 0.8125rem;">{{ $pos->datum->format('d.m.Y') }}</td>
                    <td class="text-rechts">{{ $pos->menge }}</td>
                    @if($beide || $nurPatient)
                    <td class="text-rechts">
                        @if($rechnung->status === 'entwurf')
                        <form method="POST" action="{{ route('rechnungen.position.update', $pos) }}" id="form-pos-{{ $pos->id }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="number" name="tarif_patient" value="{{ $pos->tarif_patient }}" step="0.05" min="0"
                                style="width: 70px; text-align: right; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.2rem 0.375rem; font-size: 0.8125rem;"
                                onchange="document.getElementById('form-pos-{{ $pos->id }}').submit()">
                        @else
                            {{ number_format($pos->tarif_patient, 2) }}
                        @endif
                    </td>
                    @endif
                    @if($beide || $nurKK)
                    <td class="text-rechts">
                        @if($rechnung->status === 'entwurf')
                            @if(!isset($formOpened)) <form method="POST" action="{{ route('rechnungen.position.update', $pos) }}" id="form-pos-kk-{{ $pos->id }}" style="display:inline;"> @csrf @method('PATCH') @endif
                            <input type="number" name="tarif_kk" value="{{ $pos->tarif_kk }}" step="0.05" min="0"
                                style="width: 70px; text-align: right; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.2rem 0.375rem; font-size: 0.8125rem;"
                                onchange="document.getElementById('form-pos-{{ $nurKK ? 'kk-' : '' }}{{ $pos->id }}').submit()">
                            @if($nurKK) </form> @endif
                        @else
                            {{ number_format($pos->tarif_kk, 2) }}
                        @endif
                    </td>
                    @endif
                    @if($beide || $nurPatient)
                    <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($pos->betrag_patient, 2, '.', "'") }}</td>
                    @endif
                    @if($beide || $nurKK)
                    <td class="text-rechts" style="font-size: 0.8125rem;">{{ number_format($pos->betrag_kk, 2, '.', "'") }}</td>
                    @endif
                    @if($rechnung->status === 'entwurf')
                        @if($beide) </form> @endif
                        <td></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: var(--cs-hintergrund); font-weight: 600;">
                    <td colspan="2" style="padding: 0.75rem;">Total</td>
                    @if($beide || $nurPatient)
                    <td style="padding: 0.75rem;"></td>
                    @endif
                    @if($beide || $nurKK)
                    <td style="padding: 0.75rem;"></td>
                    @endif
                    @if($beide || $nurPatient)
                    <td class="text-rechts" style="padding: 0.75rem;">CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}</td>
                    @endif
                    @if($beide || $nurKK)
                    <td class="text-rechts" style="padding: 0.75rem;">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
                    @endif
                    @if($rechnung->status === 'entwurf') <td></td> @endif
                </tr>
                <tr style="background-color: var(--cs-primaer-hell);">
                    <td colspan="{{ $rechnung->status === 'entwurf' ? 5 : 4 }}" style="padding: 0.75rem; font-weight: 700; font-size: 1rem;">Gesamttotal</td>
                    <td colspan="2" class="text-rechts" style="padding: 0.75rem; font-weight: 700; font-size: 1rem; color: var(--cs-primaer);">
                        CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Status-Workflow --}}
    @if($rechnung->status !== 'storniert')
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Status ändern</div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @if($rechnung->status === 'entwurf')
                <form method="POST" action="{{ route('rechnungen.status', $rechnung) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="gesendet">
                    <button type="submit" class="btn btn-primaer">📨 Als gesendet markieren</button>
                </form>
            @endif
            @if($rechnung->status === 'gesendet')
                <form method="POST" action="{{ route('rechnungen.status', $rechnung) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="bezahlt">
                    <button type="submit" class="btn btn-primaer" style="background-color: var(--cs-erfolg);">✓ Als bezahlt markieren</button>
                </form>
            @endif
            @if(in_array($rechnung->status, ['entwurf', 'gesendet']))
                <form method="POST" action="{{ route('rechnungen.status', $rechnung) }}" onsubmit="return confirm('Rechnung wirklich stornieren?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="storniert">
                    <button type="submit" class="btn btn-gefahr">✕ Stornieren</button>
                </form>
            @endif
        </div>
    </div>
    @endif

</div>
</x-layouts.app>
