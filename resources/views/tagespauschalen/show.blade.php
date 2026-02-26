<x-layouts.app :titel="'Tagespauschale'">
<div style="max-width: 620px;">

    <a href="{{ route('klienten.show', $tagespauschale->klient) }}"
       class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">
       ← {{ $tagespauschale->klient->nachname }} {{ $tagespauschale->klient->vorname }}
    </a>

    @if(session('erfolg'))
        <div class="meldung meldung-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
    @endif
    @if(session('fehler'))
        <div class="meldung meldung-fehler" style="margin-bottom: 1rem;">{{ session('fehler') }}</div>
    @endif

    {{-- Info bereits verrechnete Einsätze --}}
    @if($letzteVerrechnungDatum)
    <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--cs-radius); padding: 0.625rem 0.875rem; margin-bottom: 1rem; font-size: 0.875rem;">
        <span style="font-weight: 600; color: #15803d;">{{ $tagespauschale->anzahlVerrechnet() }} Tage bereits verrechnet</span>
        <span class="text-hell"> — letzter verrechneter Einsatz: {{ \Carbon\Carbon::parse($letzteVerrechnungDatum)->format('d.m.Y') }}</span>
    </div>
    @endif

    {{-- Edit-Formular --}}
    <div class="karte" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1.25rem;">Tagespauschale</div>

        <form method="POST" action="{{ route('tagespauschalen.update', $tagespauschale) }}">
            @csrf
            @method('PATCH')

            {{-- Zeitraum --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Gültig von</label>
                    <input type="date" name="datum_von" class="feld"
                        value="{{ old('datum_von', $tagespauschale->datum_von->format('Y-m-d')) }}" required>
                    @error('datum_von') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">Gültig bis</label>
                    <input type="date" name="datum_bis" id="datum_bis" class="feld"
                        value="{{ old('datum_bis', $tagespauschale->datum_bis->format('Y-m-d')) }}" required>
                    @error('datum_bis') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Rechnungstyp + Ansatz --}}
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Rechnungstyp</label>
                    <select name="rechnungstyp" class="feld" required>
                        <option value="kvg"      {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'kvg'      ? 'selected' : '' }}>KVG → Krankenkasse</option>
                        <option value="klient"   {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'klient'   ? 'selected' : '' }}>Klient (Selbstbehalt)</option>
                        <option value="gemeinde" {{ old('rechnungstyp', $tagespauschale->rechnungstyp) === 'gemeinde' ? 'selected' : '' }}>Gemeinde / Kanton</option>
                    </select>
                    @error('rechnungstyp') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">Ansatz (CHF/Tag)</label>
                    <input type="number" name="ansatz" id="ansatz" class="feld"
                        step="0.05" min="0"
                        value="{{ old('ansatz', number_format($tagespauschale->ansatz, 2, '.', '')) }}" required>
                    @error('ansatz') <div class="feld-fehler">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Text --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Text auf Rechnung</label>
                <input type="text" name="text" class="feld" maxlength="500"
                    value="{{ old('text', $tagespauschale->text) }}">
                @error('text') <div class="feld-fehler">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primaer">Speichern</button>
        </form>
    </div>

    {{-- Monatsübersicht --}}
    @if($einsaetzeStats->isNotEmpty())
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Einsätze nach Monat</div>
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Monat</th>
                    <th class="text-rechts">Tage</th>
                    <th class="text-rechts">Verrechnet</th>
                    <th class="text-rechts">Offen</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($einsaetzeStats as $stat)
                @php $offen = $stat->anzahl - $stat->verrechnet; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $stat->monat)->format('F Y') }}</td>
                    <td class="text-rechts">{{ $stat->anzahl }}</td>
                    <td class="text-rechts" style="color: var(--erfolg, #15803d);">{{ $stat->verrechnet }}</td>
                    <td class="text-rechts">{{ $offen }}</td>
                    <td>
                        @if($offen === 0)
                            <span class="badge badge-erfolg">Verrechnet</span>
                        @elseif($stat->verrechnet > 0)
                            <span class="badge badge-warnung">Teilweise</span>
                        @else
                            <span class="badge badge-grau">Offen</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
</x-layouts.app>
