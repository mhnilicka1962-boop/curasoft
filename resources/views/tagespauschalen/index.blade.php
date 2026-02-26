<x-layouts.app :titel="'Tagespauschalen'">

<div class="seiten-kopf" style="margin-bottom: 1.5rem;">
    <div>
        <h2 style="margin: 0;">Tagespauschalen</h2>
        <div class="text-hell" style="font-size: 0.875rem; margin-top: 0.25rem;">
            Wiederkehrende Tagespauschalen — werden automatisch in Rechnungsläufe einbezogen
        </div>
    </div>
    <a href="{{ route('tagespauschalen.create') }}" class="btn btn-primaer">+ Neue Tagespauschale</a>
</div>

{{-- Filter --}}
<form method="GET" class="karte" style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
    <div>
        <label class="feld-label">Klient</label>
        <select name="klient_id" class="feld" style="min-width: 200px;">
            <option value="">Alle Klienten</option>
            @foreach($klienten as $k)
                <option value="{{ $k->id }}" {{ request('klient_id') == $k->id ? 'selected' : '' }}>
                    {{ $k->nachname }} {{ $k->vorname }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-sekundaer">Filtern</button>
    @if(request('klient_id'))
        <a href="{{ route('tagespauschalen.index') }}" class="btn btn-sekundaer">Zurücksetzen</a>
    @endif
</form>

{{-- Tabelle --}}
<div class="karte-null">
    <table class="tabelle">
        <thead>
            <tr>
                <th>Klient</th>
                <th>Zeitraum</th>
                <th class="text-rechts">Ansatz/Tag</th>
                <th>Typ</th>
                <th>Einsätze</th>
                <th>Text</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tagespauschalen as $tp)
            @php
                $heute     = today();
                $aktiv     = $tp->datum_von <= $heute && $tp->datum_bis >= $heute;
                $abgelaufen= $tp->datum_bis < $heute;
                $zukuenftig= $tp->datum_von > $heute;
            @endphp
            <tr>
                <td>
                    <a href="{{ route('klienten.show', $tp->klient) }}" class="link-primaer">
                        {{ $tp->klient->nachname }} {{ $tp->klient->vorname }}
                    </a>
                </td>
                <td style="white-space: nowrap;">
                    {{ $tp->datum_von->format('d.m.Y') }} – {{ $tp->datum_bis->format('d.m.Y') }}
                    <div style="font-size: 0.75rem; margin-top: 0.1rem;">
                        @if($aktiv)
                            <span class="badge badge-erfolg">Aktiv</span>
                        @elseif($abgelaufen)
                            <span class="badge badge-grau">Abgelaufen</span>
                        @else
                            <span class="badge badge-info">Zukünftig</span>
                        @endif
                    </div>
                </td>
                <td class="text-rechts text-fett">CHF {{ number_format($tp->ansatz, 2, '.', "'") }}</td>
                <td><span class="badge badge-grau">{{ $tp->rechnungstypLabel() }}</span></td>
                <td style="font-size: 0.8125rem;">
                    @php $anzVerr = $tp->anzahlVerrechnet(); @endphp
                    <span class="text-hell">{{ $anzVerr }} / {{ $tp->anzahlTage() }}</span>
                </td>
                <td style="font-size: 0.8125rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $tp->text }}">
                    {{ $tp->text ?? '—' }}
                </td>
                <td class="text-rechts">
                    <a href="{{ route('tagespauschalen.show', $tp) }}" class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.8125rem;">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-mitte text-hell" style="padding: 2.5rem;">
                    Keine Tagespauschalen vorhanden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tagespauschalen->hasPages())
<div style="margin-top: 1rem;">{{ $tagespauschalen->links() }}</div>
@endif

</x-layouts.app>
