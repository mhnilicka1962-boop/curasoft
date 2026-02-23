<x-layouts.app titel="Neuer Patient">
<div style="max-width: 680px;">

    <a href="{{ route('klienten.index') }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1.25rem;">← Klienten</a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem;">Neuer Patient</h1>
    <p class="text-hell" style="font-size: 0.875rem; margin: 0 0 1.5rem;">Grunddaten + Betreuer + Einsatzplan — alles auf einmal.</p>

    @if($errors->any())
        <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('schnellerfassung.speichern') }}">
        @csrf

        {{-- ① Patientendaten --}}
        <div class="karte" style="margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">1</div>
                <span style="font-weight: 700; font-size: 1rem;">Patient</span>
            </div>

            <div class="form-grid-2" style="margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label">Vorname *</label>
                    <input type="text" name="vorname" class="feld" required value="{{ old('vorname') }}" autofocus>
                </div>
                <div>
                    <label class="feld-label">Nachname *</label>
                    <input type="text" name="nachname" class="feld" required value="{{ old('nachname') }}">
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label">Telefon</label>
                    <input type="text" name="telefon" class="feld" value="{{ old('telefon') }}" placeholder="z.B. 079 123 45 67">
                </div>
                <div>
                    <label class="feld-label">Kanton</label>
                    <select name="region_id" class="feld">
                        <option value="">— wählen —</option>
                        @foreach($regionen as $r)
                            <option value="{{ $r->id }}" {{ old('region_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->kuerzel }} – {{ $r->bezeichnung }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <label class="feld-label">Adresse</label>
                <input type="text" name="adresse" class="feld" value="{{ old('adresse') }}" placeholder="Strasse Nr.">
            </div>
            <div class="form-grid-2">
                <div>
                    <label class="feld-label">PLZ</label>
                    <input type="text" name="plz" class="feld" value="{{ old('plz') }}">
                </div>
                <div>
                    <label class="feld-label">Ort</label>
                    <input type="text" name="ort" class="feld" value="{{ old('ort') }}">
                </div>
            </div>
        </div>

        {{-- ② Betreuer --}}
        <div class="karte" style="margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">2</div>
                <span style="font-weight: 700; font-size: 1rem;">Betreuer</span>
                <span class="text-hell" style="font-size: 0.8125rem;">(optional — kann später ergänzt werden)</span>
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="feld-label">Mitarbeiter</label>
                    <select name="benutzer_id" id="sel-ma" class="feld" onchange="pruefePlan()">
                        <option value="">— kein Betreuer —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->vorname }} {{ $m->nachname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label">Leistungsart</label>
                    <select name="leistungsart_id" id="sel-la" class="feld" onchange="pruefePlan()">
                        <option value="">— wählen —</option>
                        @foreach($leistungsarten as $la)
                            <option value="{{ $la->id }}" {{ old('leistungsart_id') == $la->id ? 'selected' : '' }}>
                                {{ $la->bezeichnung }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ③ Einsatzplan --}}
        <div class="karte" style="margin-bottom: 1.5rem;" id="block-plan">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">3</div>
                <span style="font-weight: 700; font-size: 1rem;">Einsatzplan</span>
                <span class="text-hell" style="font-size: 0.8125rem;">(optional)</span>
            </div>

            {{-- Wochentage --}}
            <div style="margin-bottom: 0.875rem;">
                <label class="feld-label" style="margin-bottom: 0.375rem;">Wochentage</label>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @foreach(['1'=>'Mo','2'=>'Di','3'=>'Mi','4'=>'Do','5'=>'Fr','6'=>'Sa','0'=>'So'] as $nr => $tag)
                    <label class="wt-label" data-nr="{{ $nr }}" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.3rem 0.75rem; border: 1px solid var(--cs-border); border-radius: 999px; font-size: 0.875rem; cursor: pointer; background: #fff; user-select: none;">
                        <input type="checkbox" name="wochentage[]" value="{{ $nr }}"
                            {{ in_array($nr, old('wochentage', [])) ? 'checked' : '' }}
                            style="display:none;" class="wt-cb" onchange="aktualisiereWtLabel(this); aktualisierePreview()">
                        {{ $tag }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Zeiten --}}
            <div class="form-grid-2" style="margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Zeit von</label>
                    <input type="time" name="zeit_von" class="feld" value="{{ old('zeit_von') }}" oninput="aktualisierePreview()">
                </div>
                <div>
                    <label class="feld-label">Zeit bis</label>
                    <input type="time" name="zeit_bis" class="feld" value="{{ old('zeit_bis') }}" oninput="aktualisierePreview()">
                </div>
            </div>

            {{-- Zeitraum --}}
            <div class="form-grid-2" style="margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Ab Datum</label>
                    <input type="date" id="datum-start" name="datum_start" class="feld"
                        value="{{ old('datum_start', date('Y-m-d')) }}" oninput="aktualisierePreview()">
                </div>
                <div>
                    <label class="feld-label">Bis Datum</label>
                    <input type="date" id="datum-ende" name="datum_ende" class="feld"
                        value="{{ old('datum_ende') }}" oninput="aktualisierePreview()">
                    <div class="text-hell" style="font-size: 0.75rem; margin-top: 0.2rem;">Leer = 3 Monate ab Start</div>
                </div>
            </div>

            {{-- Vorschau --}}
            <div id="plan-preview" style="padding: 0.625rem 0.875rem; background: var(--cs-primaer-hell, #eff6ff); border-radius: var(--cs-radius); font-size: 0.8125rem; color: var(--cs-primaer); font-weight: 500; display: none;"></div>
        </div>

        {{-- Submit --}}
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="submit" class="btn btn-primaer" id="btn-submit" style="font-size: 1rem; padding: 0.625rem 1.5rem;">
                Patient anlegen
            </button>
            <a href="{{ route('klienten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function aktualisiereWtLabel(cb) {
    const label = cb.closest('label');
    label.style.background  = cb.checked ? 'var(--cs-primaer)' : '#fff';
    label.style.color       = cb.checked ? '#fff' : '';
    label.style.borderColor = cb.checked ? 'var(--cs-primaer)' : 'var(--cs-border)';
}

document.querySelectorAll('.wt-cb').forEach(cb => aktualisiereWtLabel(cb));

function pruefePlan() {
    aktualisierePreview();
}

function aktualisierePreview() {
    const ma   = document.getElementById('sel-ma').value;
    const la   = document.getElementById('sel-la').value;
    const von  = document.getElementById('datum-start').value;
    const bis  = document.getElementById('datum-ende').value;
    const wt   = [...document.querySelectorAll('.wt-cb:checked')].map(c => parseInt(c.value));
    const prev = document.getElementById('plan-preview');
    const btn  = document.getElementById('btn-submit');

    if (!ma || !la) {
        prev.style.display = 'none';
        btn.textContent = 'Patient anlegen';
        return;
    }

    if (!von || !wt.length) {
        prev.style.display = 'none';
        btn.textContent = 'Patient + Betreuer anlegen';
        return;
    }

    const start = new Date(von);
    const ende  = bis ? new Date(bis) : new Date(new Date(von).setMonth(new Date(von).getMonth() + 3));
    let anzahl  = 0;
    const cur   = new Date(start);

    while (cur <= ende && anzahl < 365) {
        if (wt.includes(cur.getDay())) anzahl++;
        cur.setDate(cur.getDate() + 1);
    }

    const tagNamen = {1:'Mo',2:'Di',3:'Mi',4:'Do',5:'Fr',6:'Sa',0:'So'};
    const wtText = wt.map(d => tagNamen[d]).join(', ');

    prev.style.display = 'block';
    prev.textContent   = '→ ' + anzahl + ' Einsatz' + (anzahl !== 1 ? 'ätze' : '') + ' werden geplant (' + wtText + ')';
    btn.textContent    = 'Patient + ' + anzahl + ' Einsatz' + (anzahl !== 1 ? 'ätze' : '') + ' anlegen';
}

aktualisierePreview();
</script>
@endpush
</x-layouts.app>
