<x-layouts.app :titel="$einsatz->exists ? 'Einsatz — ' . $einsatz->klient->nachname . ' ' . $einsatz->klient->vorname : 'Neuer Einsatz'">
<div style="max-width: 600px;">

    {{-- Kopfzeile --}}
    <div class="seiten-kopf" style="margin-bottom: 1.25rem; align-items: flex-start;">
        {{-- Zurück-Link --}}
        @if(!$einsatz->exists && request('_tour_redirect'))
        <a href="{{ route('touren.show', request('_tour_redirect')) }}"
           class="link-gedaempt" style="font-size: 0.875rem;">← Zurück zur Tour</a>
        @elseif(!$einsatz->exists && request('_nach_touren'))
        <a href="{{ route('touren.create', ['benutzer_id' => request('benutzer_id'), 'datum' => request('datum')]) }}"
           class="link-gedaempt" style="font-size: 0.875rem;">← Zurück zur Tour</a>
        @elseif(!$einsatz->exists && request('klient_id'))
        @php $klientZurueck = \App\Models\Klient::find(request('klient_id')); @endphp
        <a href="{{ route('klienten.show', request('klient_id')) }}"
           class="link-gedaempt" style="font-size: 0.875rem;">
            ← {{ $klientZurueck?->nachname }} {{ $klientZurueck?->vorname }}
        </a>
        @elseif($einsatz->exists)
        <a href="{{ route('einsaetze.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Einsätze</a>
        @else
        <a href="{{ route('einsaetze.index') }}" class="link-gedaempt" style="font-size: 0.875rem;">← Einsätze</a>
        @endif

        {{-- Aktions-Buttons (nur bei bestehendem Einsatz) --}}
        @if($einsatz->exists)
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @if($einsatz->status === 'geplant' && !$einsatz->tour_id)
            <form method="POST" action="{{ route('einsaetze.destroy', $einsatz) }}"
                onsubmit="return confirm('Einsatz wirklich löschen?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; color: var(--cs-fehler); border-color: var(--cs-fehler);">
                    Löschen
                </button>
            </form>
            @endif
            <a href="{{ route('einsaetze.vor-ort', $einsatz) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem;">
                📋 Vor-Ort
            </a>
        </div>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    {{-- Formular --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
            <div class="abschnitt-label" style="margin: 0;">
                {{ $einsatz->exists ? 'Einsatz bearbeiten' : 'Neuer Einsatz' }}
                @if($einsatz->exists)
                <span class="badge {{ $einsatz->statusBadgeKlasse() }}" style="margin-left: 0.5rem;">{{ $einsatz->statusLabel() }}</span>
                <span class="text-hell" style="font-size: 0.75rem; font-weight: 400; margin-left: 0.5rem;">#{{ $einsatz->id }}</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" form="einsatz-form" class="btn btn-primaer" id="btn-submit">
                    {{ $einsatz->exists ? 'Speichern' : 'Einsatz anlegen' }}
                </button>
                <a href="{{ route('einsaetze.index') }}" class="btn btn-sekundaer">Abbrechen</a>
            </div>
        </div>

        @if($einsatz->exists && $einsatz->tagespauschale_id)
        <div class="alert alert-info" style="margin-bottom: 1.25rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem;">
            Tagespauschalen-Einsatz — nur die Bemerkung kann bearbeitet werden.
        </div>
        @endif

        <form id="einsatz-form" method="POST" action="{{ $einsatz->exists ? route('einsaetze.update', $einsatz) : route('einsaetze.store') }}">
            @csrf
            @if($einsatz->exists) @method('PUT') @endif

            @if(!$einsatz->exists && request('_tour_redirect'))
                <input type="hidden" name="_tour_redirect" value="{{ request('_tour_redirect') }}">
            @elseif(!$einsatz->exists && request('_nach_touren'))
                <input type="hidden" name="_nach_touren" value="1">
            @elseif(!$einsatz->exists && request('klient_id'))
                <input type="hidden" name="_klient_redirect" value="1">
            @endif

            @if($einsatz->exists && $einsatz->tagespauschale_id)
            <fieldset disabled style="border:none; padding:0; margin:0; opacity:0.6;">
            @endif

            {{-- Klient --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="klient_id">Klient <span style="color:var(--cs-fehler);">*</span></label>
                <select id="klient_id" name="klient_id" class="feld" required>
                    <option value="">— bitte wählen —</option>
                    @foreach($klienten as $k)
                        <option value="{{ $k->id }}"
                            data-kanton="{{ $k->region?->kuerzel ?? '' }}"
                            {{ old('klient_id', $einsatz->klient_id ?? request('klient_id')) == $k->id ? 'selected' : '' }}>
                            {{ $k->vollname() }}
                            @if($k->region) ({{ $k->region->kuerzel }}) @endif
                        </option>
                    @endforeach
                </select>
                <div id="klient-kanton" class="text-klein text-hell" style="margin-top: 0.25rem;"></div>
            </div>

            {{-- Leistungsarten (Checkboxen) --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label">Leistungsarten <span style="color:var(--cs-fehler);">*</span></label>
                @error('leistungsarten')<div class="feld-fehler" style="margin-bottom:0.4rem;">{{ $message }}</div>@enderror
                @php $gespeicherte = $einsatz->exists ? $einsatz->einsatzLeistungsarten->keyBy('leistungsart_id') : collect(); @endphp
                @foreach($leistungsarten as $i => $la)
                @php $el = $gespeicherte[$la->id] ?? null; $checked = !!$el; @endphp
                <div class="la-cb-zeile" style="display:flex; align-items:center; gap:0.75rem; padding:0.3rem 0; border-bottom:1px solid var(--cs-border);">
                    <label style="display:flex; align-items:center; gap:0.5rem; flex:1; cursor:pointer; font-size:0.9rem;">
                        <input type="checkbox" name="leistungsarten[{{ $i }}][id]" value="{{ $la->id }}"
                            {{ $checked ? 'checked' : '' }}
                            onchange="toggleMin(this)"
                            style="width:1.1rem; height:1.1rem; accent-color:var(--cs-primaer);">
                        {{ $la->bezeichnung }}
                    </label>
                    <input type="number" name="leistungsarten[{{ $i }}][minuten]"
                        value="{{ $el?->minuten ?? 30 }}"
                        min="5" step="5"
                        style="width:70px; {{ $checked ? '' : 'opacity:0.3;' }}"
                        class="feld la-min-input">
                    <span class="text-hell" style="font-size:0.8125rem;">Min.</span>
                </div>
                @endforeach
            </div>

            {{-- Datum --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="datum">Datum <span style="color:var(--cs-fehler);">*</span></label>
                <input type="date" id="datum" name="datum" class="feld"
                    value="{{ old('datum', $einsatz->datum?->format('Y-m-d') ?? request('datum', date('Y-m-d'))) }}" required>
            </div>

            {{-- Zeit --}}
            <div class="form-grid-2" style="margin-bottom: 1rem;">
                <div>
                    <label class="feld-label" for="zeit_von">Von (geplant)</label>
                    <input type="time" id="zeit_von" name="zeit_von" class="feld" step="300"
                        value="{{ old('zeit_von', $einsatz->zeit_von ? substr($einsatz->zeit_von, 0, 5) : request('zeit_von')) }}">
                    <div class="text-klein text-hell" style="margin-top: 0.25rem;">5-Minuten-Schritte (KLV)</div>
                </div>
                <div>
                    <label class="feld-label" for="zeit_bis">Bis (geplant)</label>
                    <input type="time" id="zeit_bis" name="zeit_bis" class="feld" step="300"
                        value="{{ old('zeit_bis', $einsatz->zeit_bis ? substr($einsatz->zeit_bis, 0, 5) : '') }}">
                    <div class="text-klein text-hell" style="margin-top: 0.25rem;">5-Minuten-Schritte (KLV)</div>
                </div>
            </div>

            {{-- Mitarbeiter (nur Admin) --}}
            @if(auth()->user()->rolle === 'admin' && $mitarbeiter->count())
            <div style="margin-bottom: 0.5rem;">
                <label class="feld-label" for="benutzer_id">Mitarbeiter</label>
                <select id="benutzer_id" name="benutzer_id" class="feld">
                    <option value="">— Eigener Account —</option>
                    @foreach($mitarbeiter as $m)
                        <option value="{{ $m->id }}"
                            data-anstellungsart="{{ $m->anstellungsart }}"
                            {{ old('benutzer_id', $einsatz->benutzer_id ?? request('benutzer_id')) == $m->id ? 'selected' : '' }}>
                            {{ $m->nachname }} {{ $m->vorname }}
                            @if($m->anstellungsart === 'angehoerig') 👪 (Angehörig)
                            @elseif($m->anstellungsart === 'freiwillig') (Freiwillig)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="hinweis-angehoerig-ma" style="display:none; margin-bottom:1rem;">
                <div class="info-box" style="font-size:0.8125rem;">
                    👪 <strong>Pflegender Angehöriger</strong> — Leistungserbringer-Typ wird automatisch gesetzt.
                </div>
            </div>
            <div style="margin-bottom: 1rem; display:none;" id="helfer-bereich">
                <label class="feld-label" for="helfer_id">Pflegender Angehöriger (Helfer)</label>
                <select id="helfer_id" name="helfer_id" class="feld">
                    <option value="">— kein Helfer —</option>
                </select>
            </div>
            @elseif($einsatz->exists && auth()->user()->rolle === 'admin' && isset($angehoerigenBenutzer) && $angehoerigenBenutzer->count())
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="helfer_id">Helfer (pflegender Angehöriger)</label>
                <select id="helfer_id" name="helfer_id" class="feld" style="max-width: 320px;">
                    <option value="">— kein Helfer —</option>
                    @foreach($angehoerigenBenutzer as $h)
                        <option value="{{ $h->id }}" {{ old('helfer_id', $einsatz->helfer_id) == $h->id ? 'selected' : '' }}>
                            {{ $h->nachname }} {{ $h->vorname }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Status (nur Admin, nur bei bestehendem Einsatz) --}}
            @if($einsatz->exists && auth()->user()->rolle === 'admin')
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="status">Status</label>
                <select id="status" name="status" class="feld" style="max-width:220px;">
                    <option value="geplant"       {{ old('status', $einsatz->status) === 'geplant'       ? 'selected' : '' }}>Geplant</option>
                    <option value="aktiv"         {{ old('status', $einsatz->status) === 'aktiv'         ? 'selected' : '' }}>Aktiv</option>
                    <option value="abgeschlossen" {{ old('status', $einsatz->status) === 'abgeschlossen' ? 'selected' : '' }}>Abgeschlossen</option>
                    <option value="storniert"     {{ old('status', $einsatz->status) === 'storniert'     ? 'selected' : '' }}>Storniert</option>
                </select>
            </div>
            @endif

            {{-- Leistungserbringer --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="leistungserbringer_typ">Leistungserbringer</label>
                <select id="leistungserbringer_typ" name="leistungserbringer_typ" class="feld" style="max-width: 260px;">
                    <option value="fachperson" {{ old('leistungserbringer_typ', $einsatz->leistungserbringer_typ ?? 'fachperson') === 'fachperson' ? 'selected' : '' }}>Fachperson (Standard)</option>
                    <option value="angehoerig" {{ old('leistungserbringer_typ', $einsatz->leistungserbringer_typ) === 'angehoerig' ? 'selected' : '' }}>Pflegender Angehöriger</option>
                </select>
                <p style="font-size: 0.75rem; color: var(--cs-text-hell); margin-top: 0.25rem;">Relevant für KVG-Abrechnung und XML 450.100.</p>
            </div>

            {{-- Ärztliche Verordnung --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="verordnung_id">Ärztliche Verordnung</label>
                <select id="verordnung_id" name="verordnung_id" class="feld">
                    <option value="">— keine / später zuweisen —</option>
                    @php
                        $klientIdFuerVO = $einsatz->klient_id ?? request('klient_id');
                        $verordnungen = $klientIdFuerVO
                            ? \App\Models\KlientVerordnung::where('klient_id', $klientIdFuerVO)->where('aktiv', true)->orderByDesc('gueltig_ab')->get()
                            : collect();
                    @endphp
                    @foreach($verordnungen as $vo)
                        <option value="{{ $vo->id }}" {{ old('verordnung_id', $einsatz->verordnung_id) == $vo->id ? 'selected' : '' }}>
                            {{ $vo->leistungsart?->bezeichnung ?? 'Alle Leistungen' }}
                            · gültig {{ $vo->gueltig_ab?->format('d.m.Y') }}
                            @if($vo->gueltig_bis) – {{ $vo->gueltig_bis->format('d.m.Y') }} @endif
                            @if($vo->verordnungs_nr) (Nr. {{ $vo->verordnungs_nr }}) @endif
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 0.75rem; color: var(--cs-text-hell); margin-top: 0.25rem;">Für KK-Abrechnung (Behandlungspflege).</p>
            </div>

            {{-- Bemerkung --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="bemerkung">Bemerkung</label>
                <textarea id="bemerkung" name="bemerkung" class="feld" rows="2"
                    style="resize: vertical;" maxlength="1000">{{ old('bemerkung', $einsatz->bemerkung) }}</textarea>
            </div>

            {{-- Wiederholung (nur neu) --}}
            @if(!$einsatz->exists)
            <div style="margin-bottom: 1.25rem; padding: 0.875rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <label class="feld-label" style="margin: 0;">Wiederholung</label>
                    <select id="wiederholung" name="wiederholung" class="feld" style="max-width: 180px;" onchange="zeigeWiederholung()">
                        <option value="">Keine</option>
                        <option value="woechentlich" {{ old('wiederholung') === 'woechentlich' ? 'selected' : '' }}>Wöchentlich</option>
                        <option value="taeglich"     {{ old('wiederholung') === 'taeglich'     ? 'selected' : '' }}>Täglich</option>
                    </select>
                </div>
                <div id="block-woechentlich" style="display: none;">
                    <div style="margin-bottom: 0.75rem;">
                        <div class="feld-label" style="font-size: 0.75rem; margin-bottom: 0.375rem;">Wochentage</div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @foreach(['1'=>'Mo','2'=>'Di','3'=>'Mi','4'=>'Do','5'=>'Fr','6'=>'Sa','0'=>'So'] as $nr => $tag)
                            <label style="display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.625rem; border: 1px solid var(--cs-border); border-radius: 999px; font-size: 0.8125rem; cursor: pointer; background: #fff;" id="tag-label-{{ $nr }}">
                                <input type="checkbox" name="wochentage[]" value="{{ $nr }}"
                                    {{ in_array($nr, old('wochentage', [])) ? 'checked' : '' }}
                                    onchange="aktualisierePreview()" style="display:none;" class="wochentag-cb">
                                <span>{{ $tag }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="block-serie-ende" style="display: none;">
                    <label class="feld-label" style="font-size: 0.75rem;">Wiederholen bis *</label>
                    <input type="date" id="serie_ende" name="serie_ende" class="feld" style="max-width: 200px;"
                        value="{{ old('serie_ende') }}" oninput="aktualisierePreview()">
                    <div id="serie-preview" style="margin-top: 0.625rem; font-size: 0.8125rem; color: var(--cs-primaer); font-weight: 500;"></div>
                </div>
            </div>
            @endif

            @if($einsatz->exists && $einsatz->tagespauschale_id)
            </fieldset>
            @endif

        </form>
    </div>

    {{-- Erfasste Leistungen (Aktivitäten) --}}
    @if($einsatz->exists && $einsatz->aktivitaeten->isNotEmpty())
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">
            Erfasste Leistungen
            @if($einsatz->checkin_methode === 'rapportierung')
                <span class="badge badge-info" style="margin-left: 0.5rem; font-weight: 400;">Rapportierung</span>
            @endif
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            @foreach($einsatz->aktivitaeten as $akt)
            <tr style="border-bottom: 1px solid var(--cs-border);">
                <td style="padding: 0.3rem 0; color: var(--cs-text-hell); font-size: 0.8rem; width: 40%;">{{ $akt->kategorie }}</td>
                <td style="padding: 0.3rem 0; font-weight: 500;">{{ $akt->aktivitaet }}</td>
                <td style="padding: 0.3rem 0; text-align: right; color: var(--cs-primaer); font-weight: 600;">{{ $akt->minuten }} Min.</td>
            </tr>
            @endforeach
            @if($einsatz->aktivitaeten->count() > 1)
            <tr>
                <td colspan="2" style="padding: 0.4rem 0; font-weight: 600; font-size: 0.8rem;">Total</td>
                <td style="padding: 0.4rem 0; text-align: right; font-weight: 700; color: var(--cs-primaer);">{{ $einsatz->aktivitaeten->sum('minuten') }} Min.</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- Check-in/out Status --}}
    @if($einsatz->exists && $einsatz->status !== 'storniert')
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Zeiterfassung</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div style="padding: 0.875rem; background: {{ $einsatz->isEingecheckt() ? '#dcfce7' : 'var(--cs-hintergrund)' }}; border-radius: var(--cs-radius); border: 1px solid {{ $einsatz->isEingecheckt() ? '#86efac' : 'var(--cs-border)' }};">
                <div class="detail-label" style="margin-bottom: 0.25rem;">Check-in</div>
                @if($einsatz->isEingecheckt())
                    <div style="font-size: 1.125rem; font-weight: 700; color: #166534;">{{ $einsatz->checkin_zeit->format('H:i') }}</div>
                    <div style="font-size: 0.75rem; color: #166534; margin-top: 0.125rem;">
                        {{ ['qr' => 'QR-Code', 'gps' => 'GPS', 'manuell' => 'Manuell'][$einsatz->checkin_methode] ?? '' }}
                        @if($einsatz->checkin_distanz_meter !== null) · {{ $einsatz->checkin_distanz_meter }}m @endif
                    </div>
                @else
                    <div class="text-klein text-hell">—</div>
                @endif
            </div>
            <div style="padding: 0.875rem; background: {{ $einsatz->isAusgecheckt() ? '#dcfce7' : 'var(--cs-hintergrund)' }}; border-radius: var(--cs-radius); border: 1px solid {{ $einsatz->isAusgecheckt() ? '#86efac' : 'var(--cs-border)' }};">
                <div class="detail-label" style="margin-bottom: 0.25rem;">Check-out</div>
                @if($einsatz->isAusgecheckt())
                    <div style="font-size: 1.125rem; font-weight: 700; color: #166534;">{{ $einsatz->checkout_zeit->format('H:i') }}</div>
                    <div style="font-size: 0.75rem; color: #166534; margin-top: 0.125rem;">
                        {{ ['qr' => 'QR-Code', 'gps' => 'GPS', 'manuell' => 'Manuell'][$einsatz->checkout_methode] ?? '' }}
                        @if($einsatz->dauerMinuten()) · {{ $einsatz->dauerMinuten() }} Min. @endif
                    </div>
                @else
                    <div class="text-klein text-hell">—</div>
                @endif
            </div>
        </div>

        {{-- Check-in Aktionen --}}
        @if(!$einsatz->isEingecheckt())
            <button id="btn-gps-checkin" class="btn btn-primaer" style="width: 100%; justify-content: center; margin-bottom: 0.5rem;" onclick="gpsCheckin()">
                GPS Check-in
            </button>
            <form id="form-gps-checkin" method="POST" action="{{ route('checkin.gps', $einsatz) }}" style="display:none;">
                @csrf
                <input type="hidden" name="lat" id="gps-lat">
                <input type="hidden" name="lng" id="gps-lng">
            </form>
            <details style="margin-top: 0.5rem;">
                <summary class="text-klein text-hell" style="cursor: pointer; padding: 0.375rem 0;">Manuell eintragen</summary>
                <form method="POST" action="{{ route('checkin.manuell', $einsatz) }}" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-top: 0.5rem;">
                    @csrf
                    <div style="flex: 1;">
                        <label class="feld-label text-mini">Check-in Zeit</label>
                        <input type="time" name="checkin_zeit" class="feld" value="{{ date('H:i') }}" required>
                    </div>
                    <button type="submit" class="btn btn-sekundaer">Eintragen</button>
                </form>
            </details>
        @elseif(!$einsatz->isAusgecheckt())
            <a href="{{ route('checkin.aktiv', $einsatz) }}" class="btn btn-primaer" style="width: 100%; justify-content: center; margin-bottom: 0.5rem;">
                Einsatz läuft — zum Check-out
            </a>
        @endif
    </div>
    @endif

    {{-- Rapport nach Checkout --}}
    @if($einsatz->exists && $einsatz->isAusgecheckt())
    <div class="info-box" style="margin-top: 0.75rem; text-align: center;">
        <div style="font-size: 0.875rem; color: #1d4ed8; margin-bottom: 0.625rem; font-weight: 600;">
            Einsatz abgeschlossen — Rapport erfassen?
        </div>
        <a href="{{ route('rapporte.create', ['einsatz_id' => $einsatz->id]) }}"
            class="btn btn-primaer" style="font-size: 0.9375rem; padding: 0.625rem 1.25rem;">
            ✏ Rapport jetzt schreiben
        </a>
    </div>
    @endif

    {{-- QR-Code Link --}}
    @if($einsatz->exists)
    <div class="text-mitte" style="margin-top: 0.75rem;">
        <a href="{{ route('klienten.qr', $einsatz->klient) }}" class="link-gedaempt" style="font-size: 0.8125rem;">
            QR-Code des Klienten anzeigen
        </a>
    </div>
    @endif

</div>

@push('scripts')
<script>
const klientSelect    = document.getElementById('klient_id');
const kantonInfo      = document.getElementById('klient-kanton');
const datumInput      = document.getElementById('datum');
const benutzerSelect  = document.getElementById('benutzer_id');
const erbringerSelect = document.getElementById('leistungserbringer_typ');
const helferSelect    = document.getElementById('helfer_id');
const angehoerigeMap  = @json($angehoerigeMap ?? []);

function toggleMin(cb) {
    const input = cb.closest('.la-cb-zeile').querySelector('.la-min-input');
    input.style.opacity = cb.checked ? '1' : '0.3';
}

function aktualisiereKanton() {
    const opt = klientSelect.options[klientSelect.selectedIndex];
    kantonInfo.textContent = opt?.dataset.kanton ? 'Kanton: ' + opt.dataset.kanton : '';
}

function aktualisiereHelfer() {
    if (!helferSelect || !document.getElementById('helfer-bereich')) return;
    const angehoerige = angehoerigeMap[klientSelect.value] ?? [];
    helferSelect.innerHTML = '<option value="">— kein Helfer —</option>';
    angehoerige.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id; opt.textContent = a.name + ' (Angehörig)';
        helferSelect.appendChild(opt);
    });
    const bereich = document.getElementById('helfer-bereich');
    if (bereich) bereich.style.display = angehoerige.length ? '' : 'none';
}

function pruefeAngehoerig() {
    if (!benutzerSelect || !erbringerSelect) return;
    const istAngehoerig = benutzerSelect.options[benutzerSelect.selectedIndex]?.dataset?.anstellungsart === 'angehoerig';
    erbringerSelect.value = istAngehoerig ? 'angehoerig' : 'fachperson';
    const hinweis = document.getElementById('hinweis-angehoerig-ma');
    if (hinweis) hinweis.style.display = istAngehoerig ? '' : 'none';
}

klientSelect.addEventListener('change', () => { aktualisiereKanton(); aktualisiereHelfer(); });
if (benutzerSelect) benutzerSelect.addEventListener('change', pruefeAngehoerig);

aktualisiereKanton();
aktualisiereHelfer();
pruefeAngehoerig();

@if(!$einsatz->exists)
// Wiederholung
function zeigeWiederholung() {
    const w = document.getElementById('wiederholung').value;
    document.getElementById('block-woechentlich').style.display = w === 'woechentlich' ? 'block' : 'none';
    document.getElementById('block-serie-ende').style.display   = w ? 'block' : 'none';
    aktualisierePreview();
}
function aktualisierePreview() {
    const w   = document.getElementById('wiederholung').value;
    const von = datumInput.value;
    const bis = document.getElementById('serie_ende').value;
    const prev = document.getElementById('serie-preview');
    const btn  = document.getElementById('btn-submit');
    if (!w || !von || !bis) { prev.textContent = ''; btn.textContent = 'Einsatz anlegen'; return; }
    const start = new Date(von), ende = new Date(bis);
    if (ende <= start) { prev.textContent = 'Enddatum nach Startdatum.'; prev.style.color = 'var(--cs-fehler)'; return; }
    let anzahl = 0; const cur = new Date(start);
    if (w === 'taeglich') {
        while (cur <= ende && anzahl < 365) { anzahl++; cur.setDate(cur.getDate() + 1); }
    } else {
        const gew = [...document.querySelectorAll('.wochentag-cb:checked')].map(cb => parseInt(cb.value));
        if (!gew.length) { prev.textContent = 'Bitte Wochentag wählen.'; prev.style.color = 'var(--cs-fehler)'; return; }
        while (cur <= ende && anzahl < 365) { if (gew.includes(cur.getDay())) anzahl++; cur.setDate(cur.getDate() + 1); }
    }
    prev.style.color = 'var(--cs-primaer)';
    prev.textContent = anzahl + ' Einsatz' + (anzahl !== 1 ? 'ätze' : '') + ' werden erstellt.';
    btn.textContent  = anzahl + ' Einsatz' + (anzahl !== 1 ? 'ätze' : '') + ' anlegen';
}
document.querySelectorAll('.wochentag-cb').forEach(cb => {
    const label = cb.closest('label');
    function upd() { label.style.background = cb.checked ? 'var(--cs-primaer)' : '#fff'; label.style.color = cb.checked ? '#fff' : 'inherit'; label.style.borderColor = cb.checked ? 'var(--cs-primaer)' : 'var(--cs-border)'; }
    cb.addEventListener('change', upd); upd();
});
zeigeWiederholung();
@endif

@if($einsatz->exists && $einsatz->status !== 'storniert')
function gpsCheckin() {
    const btn = document.getElementById('btn-gps-checkin');
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = 'Position wird ermittelt…';
    if (!navigator.geolocation) {
        alert('GPS wird von diesem Browser nicht unterstützt.');
        btn.disabled = false; btn.textContent = 'GPS Check-in'; return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('gps-lat').value = pos.coords.latitude;
            document.getElementById('gps-lng').value = pos.coords.longitude;
            document.getElementById('form-gps-checkin').submit();
        },
        (err) => {
            alert('GPS nicht verfügbar: ' + err.message + '\nBitte manuell eintragen.');
            btn.disabled = false; btn.textContent = 'GPS Check-in';
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
}
@endif
</script>
@endpush
</x-layouts.app>
