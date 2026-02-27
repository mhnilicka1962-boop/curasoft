<x-layouts.app :titel="'Rechnungslauf #' . $lauf->id">

<div class="seiten-kopf" style="margin-bottom: 1.5rem;">
    <div>
        <a href="{{ route('rechnungslauf.index') }}" class="link-primaer" style="font-size: 0.875rem;">← Rechnungsläufe</a>
        <h2 style="margin: 0.25rem 0 0;">
            Rechnungslauf #{{ $lauf->id }} —
            {{ $lauf->periode_von->format('d.m.Y') }} bis {{ $lauf->periode_bis->format('d.m.Y') }}
        </h2>
        <div class="text-hell" style="font-size: 0.875rem; margin-top: 0.25rem;">
            Erstellt: {{ $lauf->created_at->format('d.m.Y H:i') }} von {{ $lauf->ersteller->nachname ?? '—' }}
        </div>
    </div>
</div>

@if(session('erfolg'))
    <div class="meldung meldung-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
@endif
@if(session('fehler'))
    <div class="meldung meldung-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
@endif

{{-- Statistik-Kacheln --}}
<div class="form-grid" style="margin-bottom: 1.5rem;">
    <div class="karte" style="padding: 0.875rem;">
        <div class="abschnitt-label">Erstellt</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--erfolg, #15803d);">{{ $lauf->anzahl_erstellt }}</div>
        <div class="text-hell" style="font-size: 0.8125rem;">Rechnungen</div>
    </div>
    <div class="karte" style="padding: 0.875rem;">
        <div class="abschnitt-label">Übersprungen</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-hell, #6b7280);">{{ $lauf->anzahl_uebersprungen }}</div>
        <div class="text-hell" style="font-size: 0.8125rem;">keine Einsätze</div>
    </div>
    <div class="karte" style="padding: 0.875rem;">
        <div class="abschnitt-label">Total CHF</div>
        <div style="font-size: 1.75rem; font-weight: 700;">{{ number_format($lauf->rechnungen->sum('betrag_total'), 2, '.', "'") }}</div>
        <div class="text-hell" style="font-size: 0.8125rem;">alle Rechnungen</div>
    </div>
    <div class="karte" style="padding: 0.875rem;">
        <div class="abschnitt-label">Versand</div>
        <div style="font-size: 0.9375rem; font-weight: 600;">{{ $emailAnzahl }}× Email</div>
        <div class="text-hell" style="font-size: 0.8125rem;">{{ $postAnzahl }}× Post/Manuell · {{ $kvgAnzahl }}× KVG</div>
    </div>
</div>

{{-- Aktionsleiste --}}
<div class="karte" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
    @if($emailAnzahl > 0)
    <form method="POST" action="{{ route('rechnungslauf.email', $lauf) }}"
        onsubmit="return confirm('{{ $emailAnzahl }} Email(s) mit PDF-Anhang versenden?')">
        @csrf
        <button type="submit" class="btn btn-primaer">
            Email versenden ({{ $emailAnzahl }})
        </button>
    </form>
    @endif

    @if($postAnzahl > 0)
    <a href="{{ route('rechnungslauf.sammel-pdf', $lauf) }}" class="btn btn-primaer" target="_blank">
        Sammel-PDF drucken ({{ $postAnzahl }})
    </a>
    <a href="{{ route('rechnungslauf.pdf-zip', $lauf) }}" class="btn btn-sekundaer">
        PDF-ZIP ({{ $postAnzahl }})
    </a>
    @if($postEntwurfAnzahl > 0)
    <form method="POST" action="{{ route('rechnungslauf.post-abschliessen', $lauf) }}"
        onsubmit="return confirm('{{ $postEntwurfAnzahl }} Post/Manuell-Rechnung(en) als versendet markieren?')">
        @csrf
        <button type="submit" class="btn btn-sekundaer" style="color: #15803d; border-color: #86efac;">
            ✓ Post/Manuell versendet ({{ $postEntwurfAnzahl }})
        </button>
    </form>
    @endif
    @endif

    @if($kvgAnzahl > 0)
    <a href="{{ route('rechnungslauf.xml-zip', $lauf) }}" class="btn btn-sekundaer">
        XML-ZIP KVG ({{ $kvgAnzahl }})
    </a>
    @if($xmlEntwurfAnzahl > 0)
    <form method="POST" action="{{ route('rechnungslauf.xml-abschliessen', $lauf) }}"
        onsubmit="return confirm('{{ $xmlEntwurfAnzahl }} KVG/XML-Rechnung(en) als versendet markieren?')">
        @csrf
        <button type="submit" class="btn btn-sekundaer" style="color: #15803d; border-color: #86efac;">
            ✓ XML versendet ({{ $xmlEntwurfAnzahl }})
        </button>
    </form>
    @endif
    @endif

    @if(auth()->user()->organisation->bexio_api_key)
    <form method="POST" action="{{ route('rechnungslauf.bexio-abgleich', $lauf) }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn btn-sekundaer" title="Zahlungsstatus aller Rechnungen dieses Laufs von Bexio abrufen">
            ✓ Bexio Zahlungsabgleich
        </button>
    </form>
    @endif

    <a href="{{ route('rechnungen.index') }}" class="btn btn-sekundaer" style="margin-left: auto;">
        Alle Rechnungen ansehen
    </a>

    <form method="POST" action="{{ route('rechnungslauf.destroy', $lauf) }}" style="margin: 0; margin-left: 0.5rem;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sekundaer" style="color: #b91c1c; border-color: #fca5a5;"
            onclick="return confirm('Lauf #{{ $lauf->id }} stornieren?\n\nAlle {{ $lauf->anzahl_erstellt }} Rechnungen werden gelöscht und die Einsätze wieder auf «unverrechnet» gesetzt.\n\nNur möglich wenn noch keine Rechnungen versendet/bezahlt wurden.')">
            Lauf stornieren
        </button>
    </form>
</div>

{{-- Tabelle aller Rechnungen --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Nummer</th>
                <th>Klient</th>
                <th class="text-rechts">Total CHF</th>
                <th>Versandart</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($lauf->rechnungen as $r)
            <tr>
                <td style="font-family: monospace; font-size: 0.8125rem; font-weight: 500;">
                    <a href="{{ route('rechnungen.show', $r) }}" class="link-primaer">{{ $r->rechnungsnummer }}</a>
                </td>
                <td>{{ $r->klient->nachname }} {{ $r->klient->vorname }}</td>
                <td class="text-rechts text-fett">{{ number_format($r->betrag_total, 2, '.', "'") }}</td>
                <td>
                    @php
                        $va = $r->klient->versandart_patient ?? 'post';
                        $vaLabel = match($va) { 'email' => 'Email', 'manuell' => 'Manuell', default => 'Post' };
                        $vaBadge = match($va) { 'email' => 'badge-info', 'manuell' => 'badge-warnung', default => 'badge-grau' };
                    @endphp
                    <span class="badge {{ $vaBadge }}">{{ $vaLabel }}</span>
                </td>
                <td>
                    {!! $r->statusBadge() !!}
                    @if($r->email_versand_datum)
                        <div style="font-size:0.7rem; color:#6b7280; margin-top:0.15rem;">
                            {{ $r->email_versand_datum->format('d.m.Y H:i') }}
                            → {{ $r->email_versand_an }}
                        </div>
                    @elseif($r->email_fehler)
                        <div style="font-size:0.7rem; color:#dc2626; margin-top:0.15rem; max-width:260px;"
                             title="{{ $r->email_fehler }}">
                            ✗ {{ Str::limit($r->email_fehler, 80) }}
                        </div>
                    @endif
                </td>
                <td class="text-rechts">
                    <a href="{{ route('rechnungen.show', $r) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Keine Rechnungen in diesem Lauf.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
