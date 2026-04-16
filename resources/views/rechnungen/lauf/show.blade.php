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
<div class="karte" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: flex-start;">

        {{-- Gruppe: Patient --}}
        @if($emailAnzahl > 0 || $postAnzahl > 0)
        <div>
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Patient</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                @if($emailAnzahl > 0)
                <form method="POST" action="{{ route('rechnungslauf.email', $lauf) }}"
                    onsubmit="return confirm('{{ $emailAnzahl }} Email(s) mit PDF-Anhang versenden?')">
                    @csrf
                    <button type="submit" class="btn btn-aktion">Email versenden ({{ $emailAnzahl }})</button>
                </form>
                @endif
                @if($postAnzahl > 0)
                <a href="{{ route('rechnungslauf.sammel-pdf', $lauf) }}" class="btn btn-primaer" target="_blank">Sammel-PDF ({{ $postAnzahl }})</a>
                <a href="{{ route('rechnungslauf.pdf-zip', $lauf) }}" class="btn btn-sekundaer">PDF-ZIP</a>
                @if($postEntwurfAnzahl > 0)
                <form method="POST" action="{{ route('rechnungslauf.post-abschliessen', $lauf) }}"
                    onsubmit="return confirm('{{ $postEntwurfAnzahl }} Post/Manuell-Rechnung(en) als versendet markieren?')">
                    @csrf
                    <button type="submit" class="btn btn-aktion">✓ Post-Versand bestätigen ({{ $postEntwurfAnzahl }})</button>
                </form>
                @elseif($postVersendetAnzahl > 0)
                <span class="badge badge-erfolg">✓ {{ $postVersendetAnzahl }}× Post versendet</span>
                <form method="POST" action="{{ route('rechnungslauf.post-zuruecksetzen', $lauf) }}"
                    onsubmit="return confirm('Post-Versand zurücksetzen? {{ $postVersendetAnzahl }} Rechnung(en) gehen zurück auf Entwurf.')">
                    @csrf
                    <button type="submit" class="btn btn-sekundaer" style="font-size:0.8125rem; padding:0.2rem 0.5rem;">↺ zurücksetzen</button>
                </form>
                @endif
                @endif
            </div>
        </div>
        @endif

        {{-- Gruppe: Krankenkasse (nur Tiers payant) --}}
        @if($kvgAnzahl > 0 && $tiersPayant)
        <div>
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Krankenkasse</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                @if($tiersPayant)
                    @if($org->medidata_url)
                    <form method="POST" action="{{ route('rechnungslauf.medidata', $lauf) }}"
                        onsubmit="return confirm('{{ $mediDataAnzahl }} KVG-Rechnung(en) zu MediData übertragen?')">
                        @csrf
                        <button type="submit" class="btn btn-primaer">MediData Upload ({{ $mediDataAnzahl }})</button>
                    </form>
                    @else
                    <a href="{{ route('firma.index') }}#medidata" class="btn btn-sekundaer" style="color:#6b7280; font-size:0.8125rem;">MediData konfigurieren →</a>
                    @endif
                    <a href="{{ route('rechnungslauf.xml-zip', $lauf) }}" class="btn btn-sekundaer">XML-ZIP ({{ $kvgAnzahl }})</a>
                @else
                    <a href="{{ route('rechnungslauf.xml-zip', $lauf) }}" class="btn btn-sekundaer">XML-ZIP ({{ $kvgAnzahl }})</a>
                    @if($xmlEntwurfAnzahl > 0)
                    <form method="POST" action="{{ route('rechnungslauf.xml-abschliessen', $lauf) }}"
                        onsubmit="return confirm('{{ $xmlEntwurfAnzahl }} KVG/XML-Rechnung(en) als versendet markieren?')">
                        @csrf
                        <button type="submit" class="btn btn-sekundaer" style="color: #15803d; border-color: #86efac;">✓ XML-Versand bestätigen ({{ $xmlEntwurfAnzahl }})</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
        @endif

        {{-- Gruppe: Gemeinde (nur Tiers payant) --}}
        @if($tiersPayant)
        <div>
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Gemeinde</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <a href="{{ route('rechnungslauf.gemeinde-sammel-pdf', $lauf) }}" class="btn btn-primaer" target="_blank">Gemeinde Sammel-PDF</a>
                <a href="{{ route('rechnungslauf.gemeinde-zip', $lauf) }}" class="btn btn-sekundaer">Gemeinde-PDF ZIP</a>
                @if($gemeindeAnzahl > 0)
                <form method="POST" action="{{ route('rechnungslauf.gemeinde-email', $lauf) }}"
                    onsubmit="return confirm('{{ $gemeindeAnzahl }} Gemeinde-Email(s) mit Restfinanzierungsrechnung versenden?')">
                    @csrf
                    <button type="submit" class="btn btn-aktion">Gemeinde-Email senden ({{ $gemeindeAnzahl }})</button>
                </form>
                @endif
                @if($gemeindeVersendetAnzahl > 0)
                <span class="badge badge-erfolg">✓ {{ $gemeindeVersendetAnzahl }}× Gemeinde-Email versendet</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Verwaltung --}}
        <div style="margin-left: auto;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Verwaltung</div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                @if(auth()->user()->organisation->bexio_api_key)
                <form method="POST" action="{{ route('rechnungslauf.bexio-abgleich', $lauf) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-sekundaer" title="Zahlungsstatus aller Rechnungen dieses Laufs von Bexio abrufen">Bexio Abgleich</button>
                </form>
                @endif
                @if($kannStornieren)
                <form method="POST" action="{{ route('rechnungslauf.wiederholen', $lauf) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-sekundaer"
                        onclick="return confirm('Lauf #{{ $lauf->id }} stornieren und neu erstellen?\n\nAlle Rechnungen werden gelöscht und die Einsätze wieder auf «unverrechnet» gesetzt.\n\nDanach öffnet sich das Formular mit der gleichen Periode.')">↺ Wiederholen</button>
                </form>
                <form method="POST" action="{{ route('rechnungslauf.destroy', $lauf) }}" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sekundaer" style="color: #b91c1c; border-color: #fca5a5;"
                        onclick="return confirm('Lauf #{{ $lauf->id }} stornieren?\n\nAlle {{ $lauf->anzahl_erstellt }} Rechnungen werden gelöscht und die Einsätze wieder auf «unverrechnet» gesetzt.\n\nNur möglich wenn noch keine Rechnungen versendet/bezahlt wurden.')">Stornieren</button>
                </form>
                @else
                <span class="text-hell" style="font-size:0.8125rem;">Rechnungen bereits versendet</span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Suche --}}
<div style="margin-bottom: 0.75rem;">
    <input type="text" id="lauf-suche" class="feld" style="max-width: 300px;"
        placeholder="Name oder Nr. suchen…" oninput="laufSuche(this.value)" autocomplete="off">
</div>

{{-- Tabelle aller Rechnungen --}}
<div class="karte-null">
    <table class="tabelle" id="lauf-tabelle">
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
                <td>
                    <a href="{{ route('klienten.show', $r->klient) }}" class="link-primaer">{{ $r->klient->nachname }} {{ $r->klient->vorname }}</a>
                    @if($r->hat_pauschale)
                        <span class="badge badge-info" style="font-size:0.7rem; margin-left:0.25rem;">Pauschale</span>
                    @endif
                    @if($r->hat_einzelleistung)
                        <span class="badge badge-warnung" style="font-size:0.7rem; margin-left:0.25rem;">Einzelleistung</span>
                    @endif
                </td>
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
                <td class="text-rechts" style="white-space: nowrap;">
                    <a href="{{ route('rechnungen.pdf', $r) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;" target="_blank">📄 PDF</a>
                    @if(in_array($r->rechnungstyp, ['kvg', 'kombiniert']))
                    <a href="{{ route('rechnungen.xml', $r) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;" target="_blank">📄 XML</a>
                    @endif
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

<script>
function laufSuche(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#lauf-tabelle tbody tr').forEach(function(tr) {
        const text = tr.textContent.toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>

</x-layouts.app>
