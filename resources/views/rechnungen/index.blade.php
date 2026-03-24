<x-layouts.app :titel="'Rechnungen'">

{{-- Übersicht-Kacheln --}}
<div class="form-grid" style="margin-bottom: 1.5rem;">
    @foreach(['entwurf' => ['Entwürfe','badge-grau'], 'gesendet' => ['Gesendet','badge-info'], 'bezahlt' => ['Bezahlt','badge-erfolg'], 'storniert' => ['Storniert','badge-fehler']] as $s => [$label, $badge])
    <div class="karte" style="padding: 0.875rem; cursor: pointer;" onclick="document.querySelector('[name=status]').value='{{ $s }}'; document.getElementById('filter-form').submit()">
        <div class="abschnitt-label" style="margin-bottom: 0.375rem;">{{ $label }}</div>
        <div style="font-size: 1.5rem; font-weight: 700;">{{ $totale[$s]->anzahl ?? 0 }}</div>
        <div class="text-hell" style="font-size: 0.8125rem;">CHF {{ number_format($totale[$s]->summe ?? 0, 2, '.', "'") }}</div>
    </div>
    @endforeach
</div>

{{-- Filter + Neu --}}
<div class="seiten-kopf" style="margin-bottom: 1rem;">
    <form id="filter-form" method="GET" action="{{ route('rechnungen.index') }}" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <input type="text" name="suche" class="feld" style="width: 180px;" placeholder="Nr. oder Name…" value="{{ request('suche') }}">
        <select name="jahr" class="feld" style="width: 90px;" onchange="this.form.submit()">
            <option value="">Jahr</option>
            @foreach(range(date('Y'), date('Y') - 4) as $j)
                <option value="{{ $j }}" {{ request('jahr') == $j ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
        </select>
        <select name="monat" class="feld" style="width: 110px;" onchange="this.form.submit()">
            <option value="">Monat</option>
            @foreach(['1'=>'Januar','2'=>'Februar','3'=>'März','4'=>'April','5'=>'Mai','6'=>'Juni','7'=>'Juli','8'=>'August','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Dezember'] as $m => $name)
                <option value="{{ $m }}" {{ request('monat') == $m ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        <select name="status" class="feld" style="width: 130px;" onchange="this.form.submit()">
            <option value="">Alle Status</option>
            @foreach(['entwurf' => 'Entwurf', 'gesendet' => 'Gesendet', 'bezahlt' => 'Bezahlt', 'storniert' => 'Storniert'] as $val => $lab)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sekundaer">Suchen</button>
        <a href="{{ route('rechnungen.index') }}" class="btn btn-sekundaer">Reset</a>
        <a href="{{ route('rechnungen.csv', request()->only(['suche','status','jahr','monat'])) }}" class="btn btn-sekundaer" title="Aktuelle Auswahl als CSV exportieren">↓ Auswertung CSV</a>
        <a href="{{ route('rechnungen.auswertung-pdf', request()->only(['suche','status','jahr','monat'])) }}" class="btn btn-sekundaer" title="Aktuelle Auswahl als PDF exportieren">↓ Auswertung PDF</a>
    </form>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" onclick="document.getElementById('popup-einzelleistung').style.display='flex'" class="btn btn-sekundaer">+ Einzelleistung</button>
        <a href="{{ route('rechnungen.create') }}" class="btn btn-primaer">+ Neue Rechnung</a>
    </div>
</div>

{{-- Tabelle --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Nummer</th>
                <th>Typ</th>
                <th>Klient</th>
                <th>Periode</th>
                <th>Datum</th>
                <th class="text-rechts">Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rechnungen as $r)
            <tr>
                <td style="font-family: monospace; font-size: 0.8125rem; font-weight: 500;">
                    <a href="{{ route('rechnungen.show', $r) }}" class="link-primaer">
                        {{ $r->rechnungsnummer }}
                    </a>
                </td>
                <td>{!! $r->typBadge() !!}</td>
                <td><a href="{{ route('klienten.show', $r->klient) }}" class="link-primaer">{{ $r->klient->nachname }} {{ $r->klient->vorname }}</a></td>
                <td class="text-hell" style="font-size: 0.8125rem;">
                    {{ $r->periode_von->format('d.m.Y') }} – {{ $r->periode_bis->format('d.m.Y') }}
                </td>
                <td style="font-size: 0.8125rem;">{{ $r->rechnungsdatum->format('d.m.Y') }}</td>
                <td class="text-rechts text-fett">CHF {{ number_format($r->betrag_total, 2, '.', "'") }}</td>
                <td>{!! $r->statusBadge() !!}</td>
                <td class="text-rechts">
                    <a href="{{ route('rechnungen.show', $r) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Keine Rechnungen gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($rechnungen->hasPages())
<div style="margin-top: 1rem;">{{ $rechnungen->links() }}</div>
@endif

{{-- Popup: Einzelleistung erfassen --}}
<div id="popup-einzelleistung" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.5rem; width:380px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 1rem; font-size:1rem;">Einzelleistung erfassen</h3>
        <form method="POST" action="{{ route('rechnungen.einzelleistung') }}">
            @csrf
            <div class="form-grid-2" style="gap:0.75rem; margin-bottom:0.75rem;">
                <div style="grid-column:1/-1;">
                    <label class="feld-label">Klient</label>
                    <select name="klient_id" class="feld" required>
                        <option value="">— wählen —</option>
                        @foreach($klienten as $k)
                        <option value="{{ $k->id }}">{{ $k->nachname }} {{ $k->vorname }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label">Datum</label>
                    <input type="date" name="datum" class="feld" value="{{ today()->format('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="feld-label">Betrag CHF</label>
                    <input type="number" name="betrag_fix" class="feld" min="0" step="any" placeholder="0.00" required>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="feld-label">Beschreibung (erscheint auf Rechnung)</label>
                    <input type="text" name="bemerkung" class="feld" maxlength="500" placeholder="z.B. Ausflug nach Bern" required>
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('popup-einzelleistung').style.display='none'" class="btn btn-sekundaer">Abbrechen</button>
                <button type="submit" class="btn btn-primaer">Erfassen</button>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
