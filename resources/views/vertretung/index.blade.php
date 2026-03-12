<x-layouts.app titel="Ferienvertretung / MA krank">
<div style="max-width: 640px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Ferienvertretung / MA krank</h1>
        <a href="{{ route('hilfe') }}#script-vertretung" class="btn btn-sekundaer" style="font-size: 0.8125rem;">← Zurück Hilfe</a>
    </div>

    @if(session('erfolg'))
    <div class="erfolg-box" style="margin-bottom: 1.25rem;">{{ session('erfolg') }}</div>
    @endif

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Schritt 1 — Wer fällt aus, für welchen Zeitraum?</div>

        <form action="{{ route('vertretung.vorschau') }}" method="POST">
            @csrf

            <div class="form-grid-2" style="margin-bottom: 1rem;">
                <div>
                    <label class="feld-label">Mitarbeiter (fällt aus)</label>
                    <select name="benutzer_id" class="feld" required>
                        <option value="">— wählen —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nachname }} {{ $m->vorname }}
                            </option>
                        @endforeach
                    </select>
                    @error('benutzer_id')<div class="text-klein" style="color:var(--cs-fehler)">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Vertretung durch</label>
                    <select name="vertretung_id" class="feld">
                        <option value="">— noch offen —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ old('vertretung_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nachname }} {{ $m->vorname }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-klein text-hell" style="margin-top: 0.25rem;">Optional — Qualifikation wird geprüft</div>
                </div>
            </div>

            <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                <div>
                    <label class="feld-label">Von</label>
                    <input type="date" name="datum_von" class="feld" value="{{ old('datum_von', today()->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="feld-label">Bis</label>
                    <input type="date" name="datum_bis" class="feld" value="{{ old('datum_bis', today()->addDays(13)->format('Y-m-d')) }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primaer">Betroffene Einsätze anzeigen →</button>
        </form>
    </div>

</div>
</x-layouts.app>
