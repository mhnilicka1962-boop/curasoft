<x-layouts.app :titel="$mitarbeiter->nachname . ' ' . $mitarbeiter->vorname">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('mitarbeiter.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Mitarbeitende</a>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0.25rem 0 0;">
            {{ $mitarbeiter->anrede ? $mitarbeiter->anrede . ' ' : '' }}{{ $mitarbeiter->vorname }} {{ $mitarbeiter->nachname }}
        </h1>
        <div class="text-klein text-hell" style="margin-top: 0.2rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            @php $rolleKlasse = match($mitarbeiter->rolle) { 'admin' => 'badge-fehler', 'buchhaltung' => 'badge-info', default => 'badge-primaer' }; @endphp
            <span class="badge {{ $rolleKlasse }}">{{ ucfirst($mitarbeiter->rolle) }}</span>
            @if(!$mitarbeiter->aktiv)<span class="badge badge-grau">Inaktiv</span>@endif
            <span>{{ $mitarbeiter->pensum }}% Pensum</span>
        </div>
    </div>
</div>

@if(session('erfolg'))
    <div class="erfolg-box">
        {{ session('erfolg') }}
    </div>
@endif

{{-- ═══ 1. STAMMDATEN ═══ --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label">Stammdaten</div>
    <form method="POST" action="{{ route('mitarbeiter.update', $mitarbeiter) }}">
        @csrf @method('PUT')

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label class="feld-label">Anrede</label>
                <select name="anrede" class="feld">
                    <option value="">—</option>
                    <option value="Herr" {{ $mitarbeiter->anrede === 'Herr' ? 'selected' : '' }}>Herr</option>
                    <option value="Frau" {{ $mitarbeiter->anrede === 'Frau' ? 'selected' : '' }}>Frau</option>
                </select>
            </div>
            <div>
                <label class="feld-label">Geschlecht</label>
                <select name="geschlecht" class="feld">
                    <option value="">—</option>
                    <option value="m" {{ $mitarbeiter->geschlecht === 'm' ? 'selected' : '' }}>Männlich</option>
                    <option value="f" {{ $mitarbeiter->geschlecht === 'f' ? 'selected' : '' }}>Weiblich</option>
                    <option value="d" {{ $mitarbeiter->geschlecht === 'd' ? 'selected' : '' }}>Divers</option>
                </select>
            </div>
            <div>
                <label class="feld-label">Vorname *</label>
                <input type="text" name="vorname" class="feld" required value="{{ old('vorname', $mitarbeiter->vorname) }}">
                @error('vorname')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="feld-label">Name *</label>
                <input type="text" name="nachname" class="feld" required value="{{ old('nachname', $mitarbeiter->nachname) }}">
                @error('nachname')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="feld-label">Geburtsdatum</label>
                <input type="date" name="geburtsdatum" class="feld" value="{{ old('geburtsdatum', $mitarbeiter->geburtsdatum?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="feld-label">Nationalität</label>
                <input type="text" name="nationalitaet" class="feld" value="{{ old('nationalitaet', $mitarbeiter->nationalitaet) }}" placeholder="CH">
            </div>
            <div>
                <label class="feld-label">Zivilstand</label>
                <select name="zivilstand" class="feld">
                    <option value="">—</option>
                    @foreach(['Ledig','Verheiratet','Geschieden','Verwitwet','Eingetragene Partnerschaft'] as $zs)
                        <option value="{{ $zs }}" {{ $mitarbeiter->zivilstand === $zs ? 'selected' : '' }}>{{ $zs }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div style="grid-column: span 2;">
                <label class="feld-label">Strasse</label>
                <input type="text" name="strasse" class="feld" value="{{ old('strasse', $mitarbeiter->strasse) }}">
            </div>
            <div>
                <label class="feld-label">PLZ</label>
                <input type="text" name="plz" class="feld" value="{{ old('plz', $mitarbeiter->plz) }}">
            </div>
            <div>
                <label class="feld-label">Ort</label>
                <input type="text" name="ort" class="feld" value="{{ old('ort', $mitarbeiter->ort) }}">
            </div>
            <div>
                <label class="feld-label">Telefon</label>
                <input type="text" name="telefon" class="feld" value="{{ old('telefon', $mitarbeiter->telefon) }}">
            </div>
            <div>
                <label class="feld-label">Telefax</label>
                <input type="text" name="telefax" class="feld" value="{{ old('telefax', $mitarbeiter->telefax) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label class="feld-label">E-Mail (Login) *</label>
                <input type="email" name="email" class="feld" required value="{{ old('email', $mitarbeiter->email) }}">
                @error('email')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="feld-label">E-Mail privat</label>
                <input type="email" name="email_privat" class="feld" value="{{ old('email_privat', $mitarbeiter->email_privat) }}">
            </div>
            <div>
                <label class="feld-label">AHV-Nr.</label>
                <input type="text" name="ahv_nr" class="feld" value="{{ old('ahv_nr', $mitarbeiter->ahv_nr) }}" placeholder="756.XXXX.XXXX.XX">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label class="feld-label">Bank</label>
                <input type="text" name="bank" class="feld" value="{{ old('bank', $mitarbeiter->bank) }}">
            </div>
            <div>
                <label class="feld-label">IBAN</label>
                <input type="text" name="iban" class="feld" value="{{ old('iban', $mitarbeiter->iban) }}" placeholder="CH00 0000 0000 0000 0000 0">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label class="feld-label">Pensum %</label>
                <input type="number" name="pensum" class="feld" min="0" max="100" value="{{ old('pensum', $mitarbeiter->pensum) }}">
            </div>
            <div>
                <label class="feld-label">Eintritt</label>
                <input type="date" name="eintrittsdatum" class="feld" value="{{ old('eintrittsdatum', $mitarbeiter->eintrittsdatum?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="feld-label">Austritt</label>
                <input type="date" name="austrittsdatum" class="feld" value="{{ old('austrittsdatum', $mitarbeiter->austrittsdatum?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="feld-label">Rolle *</label>
                <select name="rolle" class="feld" required>
                    <option value="pflege"      {{ $mitarbeiter->rolle === 'pflege'      ? 'selected' : '' }}>Pflege</option>
                    <option value="buchhaltung" {{ $mitarbeiter->rolle === 'buchhaltung' ? 'selected' : '' }}>Buchhaltung</option>
                    <option value="admin"       {{ $mitarbeiter->rolle === 'admin'       ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="feld-label">Anstellungsart</label>
                <select name="anstellungsart" class="feld">
                    <option value="fachperson"  {{ ($mitarbeiter->anstellungsart ?? 'fachperson') === 'fachperson'  ? 'selected' : '' }}>Fachperson</option>
                    <option value="angehoerig"  {{ ($mitarbeiter->anstellungsart ?? '') === 'angehoerig'  ? 'selected' : '' }}>Pflegender Angehöriger</option>
                    <option value="freiwillig"  {{ ($mitarbeiter->anstellungsart ?? '') === 'freiwillig'  ? 'selected' : '' }}>Freiwillig</option>
                    <option value="praktikum"   {{ ($mitarbeiter->anstellungsart ?? '') === 'praktikum'   ? 'selected' : '' }}>Praktikum</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label class="feld-label">Neues Passwort (leer lassen = unverändert)</label>
            <input type="password" name="password" class="feld" autocomplete="new-password" style="max-width: 300px;">
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label class="feld-label">Notizen</label>
            <textarea name="notizen" class="feld" rows="3" style="resize: vertical;">{{ old('notizen', $mitarbeiter->notizen) }}</textarea>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                <input type="hidden" name="aktiv" value="0">
                <input type="checkbox" name="aktiv" value="1" {{ $mitarbeiter->aktiv ? 'checked' : '' }}>
                Aktiv
            </label>
        </div>

        <div class="abschnitt-trenn" style="padding-top: 0.75rem;">
            <button type="submit" class="btn btn-primaer">Speichern</button>
        </div>
    </form>
</div>

{{-- ═══ 2. QUALIFIKATIONEN ═══ --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label">Ausbildung / Qualifikationen</div>
    <form method="POST" action="{{ route('mitarbeiter.qualifikationen', $mitarbeiter) }}">
        @csrf
        <div style="display: flex; flex-wrap: wrap; gap: 0.625rem; margin-bottom: 1rem;">
            @foreach($qualifikationen as $q)
            <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.875rem; cursor: pointer; background: {{ $mitarbeiter->qualifikationen->contains($q->id) ? 'var(--cs-primaer)' : 'var(--cs-hintergrund)' }}; color: {{ $mitarbeiter->qualifikationen->contains($q->id) ? '#fff' : 'var(--cs-text)' }}; border: 1px solid {{ $mitarbeiter->qualifikationen->contains($q->id) ? 'var(--cs-primaer)' : 'var(--cs-border)' }}; padding: 0.3rem 0.65rem; border-radius: 999px; transition: all 0.1s;">
                <input type="checkbox" name="qualifikation_ids[]" value="{{ $q->id }}"
                    {{ $mitarbeiter->qualifikationen->contains($q->id) ? 'checked' : '' }}
                    style="display: none;"
                    onchange="this.closest('label').style.background = this.checked ? 'var(--cs-primaer)' : 'var(--cs-hintergrund)'; this.closest('label').style.color = this.checked ? '#fff' : 'var(--cs-text)'; this.closest('label').style.borderColor = this.checked ? 'var(--cs-primaer)' : 'var(--cs-border)';">
                {{ $q->bezeichnung }}
            </label>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primaer">Qualifikationen speichern</button>
    </form>
</div>

{{-- ═══ 3. KLIENTENZUWEISUNG ═══ --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label">Zugewiesene Klienten</div>

    @if($mitarbeiter->klientZuweisungen->isNotEmpty())
    <table class="tabelle" style="margin-bottom: 1rem;">
        <thead>
            <tr>
                <th>Klient</th>
                <th>Rolle</th>
                <th>Beziehung</th>
                <th class="text-mitte">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($mitarbeiter->klientZuweisungen as $zuw)
            <tr style="{{ !$zuw->aktiv ? 'opacity: 0.5;' : '' }}">
                <td>
                    <a href="{{ route('klienten.show', $zuw->klient) }}" class="link-primaer">
                        {{ $zuw->klient->vollname() }}
                    </a>
                </td>
                <td>
                    @php $rolleKlasse = match($zuw->rolle) { 'hauptbetreuer' => 'badge-erfolg', 'betreuer' => 'badge-primaer', default => 'badge-grau' }; @endphp
                    <span class="badge {{ $rolleKlasse }}">{{ \App\Models\KlientBenutzer::$rollen[$zuw->rolle] }}</span>
                </td>
                <td>
                    @if($zuw->beziehungstyp === 'angehoerig_pflegend')
                        <span class="badge badge-info">Pflegender Angehöriger</span>
                    @elseif($zuw->beziehungstyp === 'freiwillig')
                        <span class="badge badge-grau">Freiwillig</span>
                    @endif
                </td>
                <td class="text-mitte">
                    @if($zuw->aktiv)<span class="badge badge-erfolg">Aktiv</span>@else<span class="badge badge-grau">Inaktiv</span>@endif
                </td>
                <td>
                    <form method="POST" action="{{ route('mitarbeiter.klient.entfernen', [$mitarbeiter, $zuw]) }}" style="display: inline;" onsubmit="return confirm('Zuweisung entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✕</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-klein text-hell" style="margin-bottom: 1rem;">Keine Klienten zugewiesen.</div>
    @endif

    <form method="POST" action="{{ route('mitarbeiter.klient.zuweisen', $mitarbeiter) }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
        @csrf
        <div>
            <label class="feld-label">Klient</label>
            <select name="klient_id" class="feld" required style="min-width: 220px;">
                <option value="">— wählen —</option>
                @foreach($klienten as $k)
                    <option value="{{ $k->id }}">{{ $k->vollname() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="feld-label">Rolle</label>
            <select name="rolle" class="feld">
                @foreach(\App\Models\KlientBenutzer::$rollen as $wert => $lbl)
                    <option value="{{ $wert }}">{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="feld-label">Beziehungstyp</label>
            <select name="beziehungstyp" class="feld">
                <option value="fachperson">Fachperson</option>
                <option value="angehoerig_pflegend">Pflegender Angehöriger</option>
                <option value="freiwillig">Freiwillig</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sekundaer">Zuweisen</button>
    </form>
</div>

</x-layouts.app>
