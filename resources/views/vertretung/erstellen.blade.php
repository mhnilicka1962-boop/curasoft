<x-layouts.app titel="Neue Vertretung">
<div style="max-width: 480px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Neue Vertretung</h1>
        <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer">← Zurück</a>
    </div>

    <div class="karte">
        <form method="POST" action="{{ route('vertretung.abwesenheit.speichern') }}">
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
                <label class="feld-label">Vertretung durch (Standard)</label>
                <select name="vertretung_id" class="feld">
                    <option value="">— optional —</option>
                    @foreach($mitarbeiter as $m)
                        <option value="{{ $m->id }}" {{ old('vertretung_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nachname }} {{ $m->vorname }}
                        </option>
                    @endforeach
                </select>
            </div>
            </div>

            <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                <div>
                    <label class="feld-label">Abwesend von</label>
                    <input type="date" name="datum_von" class="feld" value="{{ old('datum_von', today()->format('Y-m-d')) }}" required>
                    @error('datum_von')<div class="text-klein" style="color:var(--cs-fehler)">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Abwesend bis</label>
                    <input type="date" name="datum_bis" class="feld" value="{{ old('datum_bis', today()->addDays(13)->format('Y-m-d')) }}" required>
                    @error('datum_bis')<div class="text-klein" style="color:var(--cs-fehler)">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primaer">Speichern</button>
        </form>
    </div>

</div>
</x-layouts.app>
