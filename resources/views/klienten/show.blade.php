<x-layouts.app :titel="$klient->vollname()">
<div style="max-width: 860px;">

    {{-- Header --}}
    <div class="seiten-kopf">
        @if(request('back'))
            <a href="{{ request('back') }}" class="text-klein link-gedaempt">← Zurück</a>
        @else
            <a href="{{ route('klienten.index') }}" class="text-klein link-gedaempt">← Alle Klienten</a>
        @endif
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if(!$klient->aktiv)
                <span class="badge badge-grau">Inaktiv</span>
            @endif

            @if(auth()->user()->organisation->bexio_api_key)
            <form method="POST" action="{{ route('klienten.bexio.sync', $klient) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sekundaer" title="{{ $klient->bexio_kontakt_id ? 'Bexio-Kontakt aktualisieren (ID: '.$klient->bexio_kontakt_id.')' : 'Kontakt in Bexio anlegen' }}">
                    {{ $klient->bexio_kontakt_id ? '↻ Bexio' : '→ Bexio' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Warnungen --}}
    @php
        $warnungen = [];
        if ($klient->aktiv) {
            if ($klient->region_id && !\App\Models\Leistungsregion::where('region_id', $klient->region_id)->exists()) {
                $regionKuerzel = $klient->region?->kuerzel ?? 'Kanton';
                $warnungen[] = ['typ' => 'tarif', 'text' => "Kanton <strong>{$regionKuerzel}</strong> hat noch keine Tarife. Abrechnung ergibt CHF 0. → <a href=\"" . route('leistungsarten.index') . "\" style=\"color:#92400e;font-weight:600;\">Leistungsarten konfigurieren</a>"];
            }
            if ($klient->beitraege()->doesntExist()) {
                $warnungen[] = ['typ' => 'beitrag', 'text' => 'Kein Beitrag erfasst — ohne Beitrag ist keine korrekte Abrechnung möglich.'];
            }
        }
    @endphp
    @foreach($warnungen as $w)
    <div style="background:#fffbeb; border:2px solid #f59e0b; border-radius:6px; padding:0.625rem 1rem; margin-bottom:0.5rem; display:flex; gap:0.625rem; align-items:flex-start;">
        <span style="font-size:1.1rem; line-height:1.3;">⚠</span>
        <span style="font-size:0.875rem; color:#78350f;">{!! $w['text'] !!}</span>
    </div>
    @endforeach

    {{-- Kompakte Patienten-Karte --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; align-items: flex-start; gap: 0.875rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background-color: var(--cs-primaer-hell); color: var(--cs-primaer); display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; flex-shrink: 0;">
                {{ strtoupper(substr($klient->vorname, 0, 1)) }}{{ strtoupper(substr($klient->nachname, 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.125rem;">
                    <h2 style="font-size: 1.0625rem; font-weight: 700; margin: 0;">{{ $klient->vollname() }}</h2>
                    @if($klient->region)
                        <span class="badge badge-info" style="font-size: 0.7rem;">{{ $klient->region->kuerzel }}</span>
                    @endif
                </div>
                <div style="font-size: 0.8125rem; color: var(--cs-text-hell); display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                    @if($klient->geburtsdatum)
                        <span>{{ $klient->geburtsdatum->format('d.m.Y') }} ({{ $klient->geburtsdatum->age }} J.)</span>
                    @endif
                    @if($klient->geschlecht)
                        <span>{{ ['m' => 'Männl.', 'w' => 'Weibl.', 'x' => 'Div.'][$klient->geschlecht] }}</span>
                    @endif
                    @if($klient->zustaendig)
                        <span>Bezugsperson: <strong style="color: var(--cs-text);">{{ $klient->zustaendig->name }}</strong></span>
                    @endif
                    @if($klient->einsatz_geplant_von)
                        <span>Einsatz ab: <strong style="color: var(--cs-text);">{{ $klient->einsatz_geplant_von->format('d.m.Y') }}</strong></span>
                    @endif
                </div>
                @php
                    $adrKompakt = trim(($klient->adresse ?? '') . ($klient->adresse && ($klient->plz || $klient->ort) ? ', ' : '') . ($klient->plz ?? '') . ($klient->ort ? ' ' . $klient->ort : ''));
                @endphp
                <div style="font-size: 0.8125rem; display: flex; gap: 0.875rem; flex-wrap: wrap;">
                    @if($adrKompakt)
                        <span style="color: var(--cs-text-hell);">{{ $adrKompakt }}</span>
                    @endif
                    @if($klient->telefon)
                        <span><span style="color: var(--cs-text-hell);">Tel</span> {{ $klient->telefon }}</span>
                    @endif
                    @if($klient->notfallnummer)
                        <span><span style="color: var(--cs-text-hell);">Notfall</span> {{ $klient->notfallnummer }}</span>
                    @endif
                    @if($klient->krankenkasse_name)
                        <span><span style="color: var(--cs-text-hell);">KK</span> {{ $klient->krankenkasse_name }}</span>
                    @endif
                    @if($klient->ahv_nr)
                        <span><span style="color: var(--cs-text-hell);">AHV</span> {{ $klient->ahv_nr }}</span>
                    @endif
                </div>
                <div style="margin-top:0.5rem; display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    @if($einsaetzeAnzahl > 0)
                        <span class="badge badge-erfolg" style="font-size:0.7rem;">● {{ $einsaetzeAnzahl }} aktive Einsätze</span>
                    @else
                        <span class="badge badge-grau" style="font-size:0.7rem;">Keine aktiven Einsätze</span>
                    @endif
                    <button onclick="oeffneEinsaetzePopup()" class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.25rem 0.75rem;">Einsätze anzeigen</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Inline-Bearbeitungsformular (versteckt, ausser bei Validation-Fehler) --}}
    <div id="klient-edit-form" style="display:block; margin-bottom: 1rem;">
    <form method="POST" action="{{ route('klienten.update', $klient) }}">
        @csrf @method('PUT')
        <div class="karte" style="margin-bottom: 0.75rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                <div class="abschnitt-label" style="margin-bottom:0;">Persönliche Daten</div>
                <button type="submit" class="btn btn-primaer" style="padding:0.3rem 0.875rem; font-size:0.8125rem;">Speichern</button>
            </div>
            <div style="display: grid; grid-template-columns: 130px 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                <div>
                    <label class="feld-label">Anrede</label>
                    <select name="anrede" class="feld">
                        <option value="">—</option>
                        @foreach(['Herr','Frau','Dr. Herr','Dr. Frau'] as $a)
                            <option value="{{ $a }}" {{ $klient->anrede === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label">Vorname *</label>
                    <input type="text" name="vorname" class="feld" required value="{{ old('vorname', $klient->vorname) }}">
                </div>
                <div>
                    <label class="feld-label">Nachname *</label>
                    <input type="text" name="nachname" class="feld" required value="{{ old('nachname', $klient->nachname) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 70px; gap: 0.5rem; margin-bottom: 0.5rem;">
                <div>
                    <label class="feld-label">Geburtsdatum</label>
                    <input type="date" name="geburtsdatum" class="feld" value="{{ old('geburtsdatum', $klient->geburtsdatum?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="feld-label">Geschlecht</label>
                    <select name="geschlecht" class="feld">
                        <option value="">—</option>
                        <option value="m" {{ $klient->geschlecht === 'm' ? 'selected' : '' }}>Männlich</option>
                        <option value="w" {{ $klient->geschlecht === 'w' ? 'selected' : '' }}>Weiblich</option>
                        <option value="x" {{ $klient->geschlecht === 'x' ? 'selected' : '' }}>Divers</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Zivilstand</label>
                    <select name="zivilstand" class="feld">
                        <option value="">—</option>
                        <option value="ledig"       {{ $klient->zivilstand === 'ledig'       ? 'selected' : '' }}>Ledig</option>
                        <option value="verheiratet" {{ $klient->zivilstand === 'verheiratet' ? 'selected' : '' }}>Verheiratet</option>
                        <option value="geschieden"  {{ $klient->zivilstand === 'geschieden'  ? 'selected' : '' }}>Geschieden</option>
                        <option value="verwitwet"   {{ $klient->zivilstand === 'verwitwet'   ? 'selected' : '' }}>Verwitwet</option>
                        <option value="eingetragen" {{ $klient->zivilstand === 'eingetragen' ? 'selected' : '' }}>Eingetr. Partnerschaft</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Kinder</label>
                    <input type="number" name="anzahl_kinder" class="feld" min="0" value="{{ old('anzahl_kinder', $klient->anzahl_kinder) }}">
                </div>
            </div>
            <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--cs-border); display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                <div>
                    <label class="feld-label">Kanton Abrechnung <span style="color:var(--cs-fehler);">*</span></label>
                    <select name="region_id" class="feld" required>
                        <option value="">— wählen —</option>
                        @foreach($regionen as $r)
                            <option value="{{ $r->id }}" {{ $klient->region_id == $r->id ? 'selected' : '' }}>{{ $r->kuerzel }} — {{ $r->bezeichnung }}</option>
                        @endforeach
                    </select>
                    @if(!$klient->region_id)
                    <div class="warn-box" style="margin-top: 0.5rem; display: flex; gap: 0.4rem; align-items: flex-start;">
                        <span>⚠</span><div>Kein Abrechnungskanton gesetzt — Rechnungslauf nicht möglich.</div>
                    </div>
                    @endif
                </div>
                <div>
                    <label class="feld-label">Bezugsperson</label>
                    <select name="zustaendig_id" class="feld">
                        <option value="">— keine —</option>
                        @foreach($mitarbeiter as $m)
                            <option value="{{ $m->id }}" {{ $klient->zustaendig_id == $m->id ? 'selected' : '' }}>{{ $m->nachname }} {{ $m->vorname }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label">AHV-Nummer</label>
                    <input type="text" name="ahv_nr" class="feld" value="{{ old('ahv_nr', $klient->ahv_nr) }}" placeholder="756.XXXX.XXXX.XX">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.5rem; align-items: center; margin-top: 0.25rem;">
                <div>
                    <label class="feld-label">Typ</label>
                    <select name="klient_typ" class="feld">
                        <option value="patient"         {{ ($klient->klient_typ ?? 'patient') === 'patient'         ? 'selected' : '' }}>Patient</option>
                        <option value="pflegebeduerftig" {{ ($klient->klient_typ ?? '') === 'pflegebeduerftig'       ? 'selected' : '' }}>Pflegebedürftig</option>
                        <option value="angehoerig"       {{ ($klient->klient_typ ?? '') === 'angehoerig'             ? 'selected' : '' }}>Pflegender Angehöriger</option>
                    </select>
                </div>
                <div style="padding-top: 1.375rem;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                        <input type="hidden" name="aktiv" value="0">
                        <input type="checkbox" name="aktiv" value="1" {{ $klient->aktiv ? 'checked' : '' }}>
                        Aktiv
                    </label>
                </div>
            </div>
        </div>

        <div class="karte" style="margin-bottom: 0.75rem;">
            <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Kontakt & Adresse</div>
            <div style="margin-bottom: 0.625rem;">
                <label class="feld-label">Strasse & Hausnummer</label>
                <input type="text" name="adresse" class="feld" value="{{ old('adresse', $klient->adresse) }}" placeholder="Musterstrasse 12">
            </div>
            <div style="display: grid; grid-template-columns: 110px 1fr; gap: 0.625rem; margin-bottom: 0.625rem;">
                <div>
                    <label class="feld-label">PLZ</label>
                    <input type="text" name="plz" class="feld" value="{{ old('plz', $klient->plz) }}">
                </div>
                <div>
                    <label class="feld-label">Ort</label>
                    <input type="text" name="ort" class="feld" value="{{ old('ort', $klient->ort) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.625rem;">
                <div>
                    <label class="feld-label">Telefon</label>
                    <input type="text" name="telefon" class="feld" value="{{ old('telefon', $klient->telefon) }}">
                </div>
                <div>
                    <label class="feld-label">Notfallnummer</label>
                    <input type="text" name="notfallnummer" class="feld" value="{{ old('notfallnummer', $klient->notfallnummer) }}">
                </div>
                <div>
                    <label class="feld-label">E-Mail</label>
                    <input type="email" name="email" class="feld" value="{{ old('email', $klient->email) }}">
                </div>
            </div>
        </div>

        <div class="karte" style="margin-bottom: 0.75rem;">
            <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Abrechnung</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.625rem; margin-bottom: 0.625rem;">
                <div>
                    <label class="feld-label">Zahlbar (Tage)</label>
                    <input type="number" name="zahlbar_tage" class="feld" min="1" value="{{ old('zahlbar_tage', $klient->zahlbar_tage ?? 30) }}">
                </div>
                <div>
                    <label class="feld-label">Rechnungstyp</label>
                    <select name="rechnungstyp" class="feld">
                        <option value="">—</option>
                        <option value="kombiniert" {{ $klient->rechnungstyp === 'kombiniert' ? 'selected' : '' }}>Kombiniert</option>
                        <option value="kvg"        {{ $klient->rechnungstyp === 'kvg'        ? 'selected' : '' }}>Nur KVG</option>
                        <option value="klient"     {{ $klient->rechnungstyp === 'klient'     ? 'selected' : '' }}>Nur Patient</option>
                        <option value="gemeinde"   {{ $klient->rechnungstyp === 'gemeinde'   ? 'selected' : '' }}>Gemeinde</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.625rem;">
                <div>
                    <label class="feld-label">Datum Erstkontakt</label>
                    <input type="date" name="datum_erstkontakt" class="feld" value="{{ old('datum_erstkontakt', $klient->datum_erstkontakt?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="feld-label">Einsatz geplant ab</label>
                    <input type="date" name="einsatz_geplant_von" class="feld" value="{{ old('einsatz_geplant_von', $klient->einsatz_geplant_von?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="feld-label">Einsatz geplant bis</label>
                    <input type="date" name="einsatz_geplant_bis" class="feld" value="{{ old('einsatz_geplant_bis', $klient->einsatz_geplant_bis?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>

<div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primaer">Speichern</button>
        </div>
    </form>
    </div>

    {{-- Einsätze-Modal --}}
    <div id="einsaetze-modal" style="display:none; position:fixed; inset:0; z-index:500; background:rgba(0,0,0,0.45); overflow-y:auto;" onclick="if(event.target===this)schliesseEinsaetzePopup()">
        <div style="margin:2rem auto; max-width:860px; background:#fff; border-radius:8px; padding:1.5rem; position:relative; min-height:200px;">
            <button onclick="schliesseEinsaetzePopup()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.25rem; cursor:pointer; color:var(--cs-text-hell); line-height:1;">×</button>
            <div id="einsaetze-popup-inhalt">
                <p class="text-hell text-klein">Wird geladen…</p>
            </div>
        </div>
    </div>
    {{-- Einsätze: via Popup (Button in erster Kachel) --}}
    @if(false)
    <div style="display:none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
            <div class="abschnitt-label" style="margin-bottom: 0;">Einsätze</div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('klienten.rapportierung', [$klient, now()->year, now()->month]) }}"
                   style="display:inline-flex; flex-direction:column; align-items:flex-start; padding:0.375rem 0.875rem; background:var(--cs-primaer); color:#fff; border-radius:var(--cs-radius); text-decoration:none; line-height:1.3;">
                    <span style="font-size:0.8125rem; font-weight:600;">📋 Rapportierung verwalten</span>
                    <span style="font-size:0.7rem; opacity:0.85;">Monatsübersicht</span>
                </a>
                <a href="{{ route('klienten.qr', $klient) }}" target="_blank" class="btn btn-sekundaer" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">📱 QR Check-in</a>
            </div>
        </div>

        {{-- Tabs --}}
        <div style="display: flex; border-bottom: 2px solid var(--cs-border); margin-bottom: 0.75rem;">
            <button onclick="einsatzTab('anstehend')" id="tab-anstehend"
                style="padding: 0.375rem 0.875rem; font-size: 0.8125rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid var(--cs-primaer); margin-bottom: -2px; cursor: pointer; color: var(--cs-primaer);">
                Anstehend ({{ $anstehend->count() }})
            </button>
            <button onclick="einsatzTab('vergangen')" id="tab-vergangen"
                style="padding: 0.375rem 0.875rem; font-size: 0.8125rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
                Vergangen ({{ $vergangen->count() }})
            </button>
            <button onclick="einsatzTab('monat')" id="tab-monat"
                style="padding: 0.375rem 0.875rem; font-size: 0.8125rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
                {{ now()->format('F Y') }} ({{ $monatEinsaetze->count() }})
            </button>
        </div>

        {{-- Anstehend --}}
        <div id="panel-anstehend" style="max-height: 320px; overflow-y: auto;">
            @forelse($anstehend as $e)
            @php $istHeute = $e->datum->isToday(); @endphp
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4375rem 0.25rem; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.5rem; flex-wrap: wrap; {{ $istHeute ? 'background: #eff6ff; border-radius: 4px;' : '' }}">
                <div style="display: flex; gap: 0.625rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <span style="color: {{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; min-width: 80px; font-weight: {{ $istHeute ? '700' : '400' }}; white-space: nowrap;">
                        {{ $e->datum->format('d.m.Y') }}
                    </span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="white-space: nowrap; font-size: 0.8rem;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>
                    @endif
                    <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
                    @if($e->benutzer)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>
                    @endif
                    @if($e->tour)
                        <a href="{{ route('touren.show', $e->tour) }}" class="badge badge-primaer" style="font-size: 0.7rem; text-decoration: none;">{{ $e->tour->bezeichnung }}</a>
                    @elseif(!$e->tagespauschale_id && $e->status === 'geplant')
                        <span class="badge badge-warnung" style="font-size: 0.7rem;">⚠ Keine Tour</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.375rem; align-items: center; flex-shrink: 0;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    @if(!$e->tagespauschale_id)
                        <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                        @if($e->status === 'geplant' && !$e->tour_id)
                        <form method="POST" action="{{ route('einsaetze.destroy', $e) }}" style="margin: 0;" onsubmit="return confirm('Einsatz löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-fehler); font-size: 0.8rem; padding: 0; line-height: 1.4;">× löschen</button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="padding: 0.5rem 0; margin: 0;">Keine anstehenden Einsätze.</p>
            @endforelse
        </div>

        {{-- Monat --}}
        <div id="panel-monat" style="display: none; max-height: 320px; overflow-y: auto;">
            @forelse($monatEinsaetze as $e)
            @php $istHeute = $e->datum->isToday(); @endphp
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4375rem 0.25rem; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.5rem; flex-wrap: wrap; {{ $istHeute ? 'background: #eff6ff; border-radius: 4px;' : '' }}">
                <div style="display: flex; gap: 0.625rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <span style="color: {{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; min-width: 80px; font-weight: {{ $istHeute ? '700' : '400' }}; white-space: nowrap;">
                        {{ $e->datum->format('d.m.Y') }}
                    </span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="white-space: nowrap; font-size: 0.8rem;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>
                    @endif
                    <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
                    @if($e->benutzer)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.375rem; align-items: center; flex-shrink: 0;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    @if(!$e->tagespauschale_id)
                        <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="padding: 0.5rem 0; margin: 0;">Keine Einsätze in diesem Monat.</p>
            @endforelse
        </div>

        {{-- Vergangen --}}
        <div id="panel-vergangen" style="display: none; max-height: 320px; overflow-y: auto;">
            @forelse($vergangen as $e)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.625rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <span class="text-hell" style="min-width: 80px; white-space: nowrap;">
                        {{ $e->datum->format('d.m.Y') }}
                    </span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="white-space: nowrap; font-size: 0.8rem;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>
                    @endif
                    <span>{{ $e->leistungsart?->bezeichnung ?? ($e->tagespauschale_id ? 'Tagespauschale' : '—') }}</span>
                    @if($e->benutzer)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.375rem; align-items: center; flex-shrink: 0;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    @if($e->checkin_methode === 'rapportierung')
                        <span class="badge badge-info" title="Manuell vom Admin erfasst">Rapportierung</span>
                    @endif
                    @if(!$e->tagespauschale_id)
                        <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="padding: 0.5rem 0; margin: 0;">Keine vergangenen Einsätze.</p>
            @endforelse
        </div>

        {{-- Inline Planungsformular --}}
        <details style="margin-top: 0.75rem;" {{ session('erfolg') && str_contains(session('erfolg',''), 'geplant') ? 'open' : '' }}>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">
                + Einsatz planen
            </summary>
            <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                @if($errors->has('datum') || $errors->has('leistungsart_id'))
                    <div class="alert alert-fehler" style="margin-bottom: 0.75rem; font-size: 0.875rem;">
                        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('einsaetze.store') }}">
                    @csrf
                    <input type="hidden" name="klient_id" value="{{ $klient->id }}">
                    <input type="hidden" name="_klient_redirect" value="1">
                    <div class="form-grid" style="margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Leistungsart *</label>
                            <select name="leistungsart_id" class="feld" required style="font-size: 0.875rem;" id="plan-la">
                                <option value="">— wählen —</option>
                                @foreach($leistungsarten as $la)
                                    <option value="{{ $la->id }}" data-einheit="{{ $la->einheit }}"
                                        {{ old('leistungsart_id') == $la->id ? 'selected' : '' }}>
                                        {{ $la->bezeichnung }}{{ $la->einheit === 'tage' ? ' (Tage)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;" id="plan-label-datum">Datum *</label>
                            <input type="date" name="datum" class="feld" required style="font-size: 0.875rem;"
                                value="{{ old('datum', date('Y-m-d')) }}" id="plan-datum">
                        </div>
                        <div id="plan-block-datum-bis" style="display: none;">
                            <label class="feld-label" style="font-size: 0.75rem;">Datum bis</label>
                            <input type="date" name="datum_bis" class="feld" style="font-size: 0.875rem;"
                                value="{{ old('datum_bis') }}" id="plan-datum-bis">
                        </div>
                        <div id="plan-block-von">
                            <label class="feld-label" style="font-size: 0.75rem;">Von</label>
                            <input type="time" name="zeit_von" class="feld" style="font-size: 0.875rem;" value="{{ old('zeit_von') }}">
                        </div>
                        <div id="plan-block-bis">
                            <label class="feld-label" style="font-size: 0.75rem;">Bis</label>
                            <input type="time" name="zeit_bis" class="feld" style="font-size: 0.875rem;" value="{{ old('zeit_bis') }}">
                        </div>
                        @if(auth()->user()->rolle === 'admin' && $mitarbeiter->count())
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Mitarbeiter</label>
                            <select name="benutzer_id" class="feld" style="font-size: 0.875rem;">
                                <option value="">— selbst —</option>
                                @foreach($mitarbeiter as $m)
                                    <option value="{{ $m->id }}" {{ old('benutzer_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nachname }} {{ $m->vorname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <div style="margin-bottom: 0.75rem;">
                        <label class="feld-label" style="font-size: 0.75rem;">Bemerkung</label>
                        <textarea name="bemerkung" class="feld" rows="2" style="font-size: 0.875rem; resize: vertical;" maxlength="1000">{{ old('bemerkung') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Einsatz planen</button>
                </form>
            </div>
        </details>
    </div>
    @endif

    {{-- Abrechnung & Beiträge --}}
    @php $beitraege = $klient->beitraege()->with('erfasstVon')->get(); @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Abrechnung &amp; Beiträge</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $beitraege->count() }} Beitrag/Beiträge</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">

            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Abrechnung &amp; Versand</div>
            <form method="POST" action="{{ route('klienten.update', $klient) }}" style="margin-bottom: 1.25rem;">
                @csrf @method('PUT')
                <input type="hidden" name="vorname"  value="{{ $klient->vorname }}">
                <input type="hidden" name="nachname" value="{{ $klient->nachname }}">
                <input type="hidden" name="aktiv"    value="{{ $klient->aktiv ? 1 : 0 }}">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; align-items: end;">
                    <div>
                        <label class="form-label" style="font-size: 0.8125rem;">Rechnungstyp</label>
                        <select name="rechnungstyp" class="feld" style="font-size: 0.875rem;">
                            @foreach(\App\Models\Rechnung::$typen as $val => $lab)
                                <option value="{{ $val }}" {{ ($klient->rechnungstyp ?? 'kombiniert') === $val ? 'selected' : '' }}>{{ $lab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8125rem;">Versand Patient-Rechnung</label>
                        <select name="versandart_patient" class="feld" style="font-size: 0.875rem;">
                            <option value="post"    {{ ($klient->versandart_patient ?? 'post') === 'post'    ? 'selected' : '' }}>Post / Druck</option>
                            <option value="email"   {{ ($klient->versandart_patient ?? 'post') === 'email'   ? 'selected' : '' }}>Email</option>
                            <option value="manuell" {{ ($klient->versandart_patient ?? 'post') === 'manuell' ? 'selected' : '' }}>Manuell</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8125rem;">Versand KVG / Krankenkasse</label>
                        <select name="versandart_kvg" class="feld" style="font-size: 0.875rem;">
                            <option value="manuell"   {{ ($klient->versandart_kvg ?? 'manuell') === 'manuell'   ? 'selected' : '' }}>Manuell</option>
                            <option value="email"     {{ ($klient->versandart_kvg ?? 'manuell') === 'email'     ? 'selected' : '' }}>Email (KK)</option>
                            <option value="healthnet" {{ ($klient->versandart_kvg ?? 'manuell') === 'healthnet' ? 'selected' : '' }}>Healthnet</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.3rem 0.75rem;">Speichern</button>
            </form>

            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Beiträge</div>
            @if($beitraege->count())
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--cs-border);">
                        <th style="text-align: left; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Gültig ab</th>
                        <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Ansatz Kunde</th>
                        <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Limit %</th>
                        <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Ansatz SPITEX</th>
                        <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Kanton</th>
                        <th style="padding: 0.375rem 0.5rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($beitraege as $b)
                    <tr style="border-bottom: 1px solid var(--cs-border); {{ $loop->first ? 'background: var(--cs-hintergrund);' : '' }}">
                        <td style="padding: 0.375rem 0.5rem; font-weight: {{ $loop->first ? '600' : '400' }};">
                            {{ $b->gueltig_ab->format('d.m.Y') }}
                            @if($loop->first)<span class="badge badge-erfolg" style="font-size: 0.65rem; margin-left: 0.25rem;">aktuell</span>@endif
                        </td>
                        <td class="text-rechts" style="padding: 0.375rem 0.5rem;">{{ number_format($b->ansatz_kunde, 2, '.', "'") }}</td>
                        <td class="text-rechts" style="padding: 0.375rem 0.5rem;">{{ number_format($b->limit_restbetrag_prozent, 2, '.', "'") }}</td>
                        <td class="text-rechts" style="padding: 0.375rem 0.5rem;">{{ number_format($b->ansatz_spitex, 2, '.', "'") }}</td>
                        <td class="text-rechts" style="padding: 0.375rem 0.5rem;">{{ number_format($b->kanton_abrechnung, 2, '.', "'") }}</td>
                        <td class="text-rechts" style="padding: 0.375rem 0.5rem; white-space: nowrap;">
                            <button type="button" onclick="beitragBearbeiten({{ $b->id }})"
                                style="background: none; border: none; color: var(--cs-primaer); cursor: pointer; font-size: 0.75rem; padding: 0; margin-right: 0.75rem;">bearbeiten</button>
                            <form method="POST" action="{{ route('klienten.beitrag.loeschen', [$klient, $b]) }}" style="margin: 0; display: inline;" onsubmit="return confirm('Beitrag entfernen?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: var(--cs-fehler); cursor: pointer; font-size: 0.75rem; padding: 0;">entfernen</button>
                            </form>
                        </td>
                    </tr>
                    {{-- Inline-Bearbeitungsformular --}}
                    <tr id="beitrag-edit-{{ $b->id }}" style="display: none;">
                        <td colspan="6" style="padding: 0.5rem;">
                            <form method="POST" action="{{ route('klienten.beitrag.aktualisieren', [$klient, $b]) }}" style="background: var(--cs-hintergrund); border-radius: 6px; padding: 0.75rem;">
                                @csrf @method('PATCH')
                                <div class="form-grid" style="margin-bottom: 0.75rem;">
                                    <div>
                                        <label class="feld-label" style="font-size: 0.75rem;">Gültig ab *</label>
                                        <input type="date" name="gueltig_ab" class="feld" required value="{{ $b->gueltig_ab->format('Y-m-d') }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div>
                                        <label class="feld-label" style="font-size: 0.75rem;">Ansatz Kunde (CHF) *</label>
                                        <input type="number" name="ansatz_kunde" class="feld" step="0.05" min="0" required value="{{ $b->ansatz_kunde }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div>
                                        <label class="feld-label" style="font-size: 0.75rem;">Limit Restbetrag %</label>
                                        <input type="number" name="limit_restbetrag_prozent" class="feld" step="0.01" min="0" max="100" value="{{ $b->limit_restbetrag_prozent }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div>
                                        <label class="feld-label" style="font-size: 0.75rem;">Ansatz SPITEX (CHF)</label>
                                        <input type="number" name="ansatz_spitex" class="feld" step="0.05" min="0" value="{{ $b->ansatz_spitex }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div>
                                        <label class="feld-label" style="font-size: 0.75rem;">Kanton Abrechnung (CHF)</label>
                                        <input type="number" name="kanton_abrechnung" class="feld" step="0.05" min="0" value="{{ $b->kanton_abrechnung }}" style="font-size: 0.875rem;">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Speichern</button>
                                    <button type="button" onclick="beitragBearbeiten({{ $b->id }})" class="btn btn-sekundaer" style="font-size: 0.875rem;">Abbrechen</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="background: #fef2f2; border: 2px solid #fca5a5; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: #b91c1c;">
                <strong>Kein Beitrag erfasst</strong> — ohne Beitrag kann keine korrekte Abrechnung erstellt werden.
            </div>
            @endif

            <details id="beitrag-erfassen" data-beitrag>
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Beitrag erfassen</summary>
                <form method="POST" action="{{ route('klienten.beitrag.speichern', $klient) }}" style="margin-top: 0.75rem;">
                    @csrf
                    <div class="form-grid" style="margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Gültig ab *</label>
                            <input type="date" name="gueltig_ab" class="feld" required value="{{ old('gueltig_ab', date('Y-m-d')) }}" style="font-size: 0.875rem;">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Ansatz Kunde (CHF) *</label>
                            <input type="number" name="ansatz_kunde" class="feld" step="0.05" min="0" required value="{{ old('ansatz_kunde', '0.00') }}" style="font-size: 0.875rem;">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Limit Restbetrag %</label>
                            <input type="number" name="limit_restbetrag_prozent" class="feld" step="0.01" min="0" max="100" value="{{ old('limit_restbetrag_prozent', '0.00') }}" style="font-size: 0.875rem;">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Ansatz SPITEX (CHF)</label>
                            <input type="number" name="ansatz_spitex" class="feld" step="0.05" min="0" value="{{ old('ansatz_spitex', '0.00') }}" style="font-size: 0.875rem;">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Kanton Abrechnung (CHF)</label>
                            <input type="number" name="kanton_abrechnung" class="feld" step="0.05" min="0" value="{{ old('kanton_abrechnung', '0.00') }}" style="font-size: 0.875rem;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Beitrag speichern</button>
                </form>
            </details>
        </div>
    </details>

    {{-- Krankenkassen --}}
    @php $klientKk = $klient->krankenkassen()->with('krankenkasse')->orderBy('versicherungs_typ')->get(); @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Krankenkassen</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $klientKk->count() }} erfasst</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">
            @if($klientKk->count())
            <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 1rem;">
                @foreach($klientKk as $kk)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem;">
                    <div style="flex: 1;">
                        <span class="badge {{ $kk->versicherungs_typ === 'kvg' ? 'badge-erfolg' : 'badge-info' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $kk->typLabel() }}</span>
                        @if($kk->deckungstyp && $kk->deckungstyp !== 'allgemein')
                            <span class="badge badge-warnung" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $kk->deckungLabel() }}</span>
                        @endif
                        <span class="badge {{ $kk->tiers_payant ? 'badge-erfolg' : 'badge-grau' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $kk->tiers_payant ? 'Tiers payant' : 'Tiers garant' }}</span>
                        <span class="text-fett">{{ $kk->krankenkasse->name }}</span>
                        @if($kk->versichertennummer)
                            <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">Nr. {{ $kk->versichertennummer }}</span>
                        @endif
                        @if(!$kk->aktiv)
                            <span class="badge badge-grau" style="font-size: 0.7rem; margin-left: 0.375rem;">Inaktiv</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('klienten.kk.entfernen', [$klient, $kk]) }}" style="margin: 0;" onsubmit="return confirm('Krankenkasse entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
            <details>
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Krankenkasse hinzufügen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.kk.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Krankenkasse *</label>
                                <select name="krankenkasse_id" class="feld" required style="font-size: 0.875rem;">
                                    <option value="">— wählen —</option>
                                    @foreach(\App\Models\Krankenkasse::where('organisation_id', auth()->user()->organisation_id)->where('aktiv', true)->orderBy('name')->get() as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Typ *</label>
                                <select name="versicherungs_typ" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\KlientKrankenkasse::$versicherungsTypen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Abrechnungsmodell</label>
                                <select name="tiers_payant" class="feld" style="font-size: 0.875rem;">
                                    <option value="1" selected>Tiers payant (Standard)</option>
                                    <option value="0">Tiers garant</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Deckung</label>
                                <select name="deckungstyp" class="feld" style="font-size: 0.875rem;">
                                    <option value="">—</option>
                                    @foreach(\App\Models\KlientKrankenkasse::$deckungstypen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Versichertennummer</label>
                                <input type="text" name="versichertennummer" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Kartennummer</label>
                                <input type="text" name="kartennummer" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Gültig ab</label>
                                <input type="date" name="gueltig_ab" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Gültig bis</label>
                                <input type="date" name="gueltig_bis" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Hinzufügen</button>
                    </form>
                </div>
            </details>
        </div>
    </details>

    {{-- Medizinisch --}}
    @php
        $diagnosen    = $klient->diagnosen()->with('arzt')->orderByDesc('datum_gestellt')->get();
        $verordnungen = $klient->verordnungen()->with(['arzt', 'leistungsart'])->get();
        $pflegestufen = $klient->pflegestufen()->with('erfasstVon')->orderByDesc('einstufung_datum')->get();
        $klientAerzte = $klient->aerzte()->with('arzt')->get();
    @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Medizinisch</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $diagnosen->count() }} Diagn. · {{ $verordnungen->count() }} Verordn. · {{ $pflegestufen->count() }} Einstuf.</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">

            {{-- Pflegestufen --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Pflegebedarf / Einstufungen</div>
            @if($pflegestufen->count())
            <div style="margin-bottom: 0.75rem;">
                @foreach($pflegestufen as $ps)
                <div style="display: flex; align-items: center; gap: 0.875rem; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; flex-wrap: wrap;">
                    <span class="text-hell" style="min-width: 90px;">{{ $ps->einstufung_datum->format('d.m.Y') }}</span>
                    <span class="badge badge-info" style="font-size: 0.75rem;">{{ \App\Models\KlientPflegestufe::$instrumente[$ps->instrument] ?? $ps->instrument }}</span>
                    <span style="font-weight: 700;">Stufe {{ $ps->stufe }}</span>
                    @if($ps->punkte)<span class="text-hell">{{ number_format($ps->punkte, 1) }} Pkt.</span>@endif
                    @if($ps->naechste_pruefung)<span class="text-hell" style="font-size: 0.8rem;">Prüfung: {{ $ps->naechste_pruefung->format('d.m.Y') }}</span>@endif
                </div>
                @endforeach
            </div>
            @endif
            <details style="margin-bottom: 1rem;">
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Einstufung erfassen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.pflegestufe.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 100px 160px 160px; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Instrument *</label>
                                <select name="instrument" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\KlientPflegestufe::$instrumente as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Stufe *</label>
                                <input type="number" name="stufe" class="feld" required min="0" max="12" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Einstufungsdatum *</label>
                                <input type="date" name="einstufung_datum" class="feld" required style="font-size: 0.875rem;" value="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Nächste Prüfung</label>
                                <input type="date" name="naechste_pruefung" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="margin-bottom: 0.75rem;">
                            <label class="feld-label" style="font-size: 0.75rem;">Punkte (optional)</label>
                            <input type="number" name="punkte" class="feld" step="0.1" min="0" style="font-size: 0.875rem; max-width: 160px;">
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Einstufung speichern</button>
                    </form>
                </div>
            </details>

            {{-- Diagnosen --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Diagnosen (ICD-10)</div>
            @if($diagnosen->count())
            <div style="margin-bottom: 0.75rem;">
                @foreach($diagnosen as $d)
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.625rem;">
                    <div style="flex: 1;">
                        <span class="badge {{ $d->diagnose_typ === 'haupt' ? 'badge-fehler' : 'badge-grau' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ \App\Models\KlientDiagnose::$typen[$d->diagnose_typ] ?? $d->diagnose_typ }}</span>
                        <span class="text-fett" style="font-family: monospace;">{{ $d->icd10_code }}</span>
                        <span style="margin-left: 0.5rem;">{{ $d->icd10_bezeichnung }}</span>
                        @if($d->arzt)<span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">· {{ $d->arzt->vollname() }}</span>@endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                        @if($d->datum_gestellt)<span class="text-hell" style="font-size: 0.8rem;">{{ $d->datum_gestellt->format('d.m.Y') }}</span>@endif
                        <form method="POST" action="{{ route('klienten.diagnose.entfernen', [$klient, $d]) }}" style="margin: 0;" onsubmit="return confirm('Diagnose deaktivieren?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <details style="margin-bottom: 1rem;">
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Diagnose hinzufügen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.diagnose.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 120px 1fr 180px; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">ICD-10 Code *</label>
                                <input type="text" name="icd10_code" class="feld" required placeholder="z.B. I10" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Bezeichnung *</label>
                                <input type="text" name="icd10_bezeichnung" class="feld" required placeholder="z.B. Essentielle Hypertonie" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Typ *</label>
                                <select name="diagnose_typ" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\KlientDiagnose::$typen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-grid-3" style="gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Arzt (optional)</label>
                                <select name="arzt_id" class="feld" style="font-size: 0.875rem;">
                                    <option value="">—</option>
                                    @foreach(\App\Models\Arzt::where('organisation_id', auth()->user()->organisation_id)->where('aktiv', true)->orderBy('nachname')->get() as $a)
                                        <option value="{{ $a->id }}">{{ $a->vollname() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Datum gestellt</label>
                                <input type="date" name="datum_gestellt" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Datum bis</label>
                                <input type="date" name="datum_bis" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Diagnose hinzufügen</button>
                    </form>
                </div>
            </details>

            {{-- Verordnungen --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Ärztliche Verordnungen</div>
            @if($verordnungen->count())
            <div style="margin-bottom: 0.75rem;">
                @foreach($verordnungen as $v)
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem; margin-bottom: 0.375rem;">
                    <div style="flex: 1;">
                        <span class="badge {{ $v->statusBadge() }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $v->statusLabel() }}</span>
                        @if($v->leistungsart)<span class="badge badge-primaer" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $v->leistungsart->bezeichnung }}</span>@endif
                        @if($v->verordnungs_nr)<span class="text-fett">Nr. {{ $v->verordnungs_nr }}</span>@endif
                        @if($v->arzt)<span class="text-hell" style="margin-left: 0.375rem;">· Dr. {{ $v->arzt->vollname() }}</span>@endif
                        <div style="margin-top: 0.25rem; font-size: 0.8rem; color: var(--cs-text-hell);">
                            Gültig: {{ $v->gueltig_ab?->format('d.m.Y') }}
                            @if($v->gueltig_bis) – {{ $v->gueltig_bis->format('d.m.Y') }} @else (offen) @endif
                        </div>
                        @if($v->bemerkung)<div style="font-size: 0.8rem; color: var(--cs-text-hell);">{{ $v->bemerkung }}</div>@endif
                    </div>
                    <form method="POST" action="{{ route('klienten.verordnung.entfernen', [$klient, $v]) }}" style="margin: 0; flex-shrink: 0;" onsubmit="return confirm('Verordnung löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
            <details style="margin-bottom: 1rem;">
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Verordnung hinzufügen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.verordnung.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Arzt</label>
                                <select name="arzt_id" class="feld" style="font-size: 0.875rem;">
                                    <option value="">— kein Arzt —</option>
                                    @foreach(\App\Models\Arzt::where('organisation_id', auth()->user()->organisation_id)->where('aktiv', true)->orderBy('nachname')->get() as $a)
                                        <option value="{{ $a->id }}">{{ $a->vollname() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Leistungsart</label>
                                <select name="leistungsart_id" class="feld" style="font-size: 0.875rem;">
                                    <option value="">— alle —</option>
                                    @foreach(\App\Models\Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get() as $la)
                                        <option value="{{ $la->id }}">{{ $la->bezeichnung }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Verordnungs-Nr.</label>
                                <input type="text" name="verordnungs_nr" class="feld" placeholder="z.B. 2026-001" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Ausgestellt am</label>
                                <input type="date" name="ausgestellt_am" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Gültig ab *</label>
                                <input type="date" name="gueltig_ab" class="feld" required value="{{ today()->format('Y-m-d') }}" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Gültig bis</label>
                                <input type="date" name="gueltig_bis" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="margin-bottom: 0.75rem;">
                            <label class="feld-label" style="font-size: 0.75rem;">Bemerkung</label>
                            <input type="text" name="bemerkung" class="feld" placeholder="z.B. Verlängerung..." style="font-size: 0.875rem;">
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Verordnung speichern</button>
                    </form>
                </div>
            </details>

            {{-- Ärzte --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Behandelnde Ärzte</div>
            @if($klientAerzte->count())
            <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 0.75rem;">
                @foreach($klientAerzte as $ka)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem;">
                    <div>
                        @if($ka->hauptarzt)<span class="badge badge-primaer" style="font-size: 0.7rem; margin-right: 0.375rem;">Hauptarzt</span>@endif
                        <span class="text-fett">{{ $ka->arzt->vollname() }}</span>
                        <span class="text-hell" style="margin-left: 0.5rem;">{{ \App\Models\KlientArzt::$rollen[$ka->rolle] ?? $ka->rolle }}</span>
                        @if($ka->arzt->praxis_name)<span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">· {{ $ka->arzt->praxis_name }}</span>@endif
                        @if($ka->arzt->telefon)<span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">{{ $ka->arzt->telefon }}</span>@endif
                    </div>
                    <form method="POST" action="{{ route('klienten.arzt.entfernen', [$klient, $ka]) }}" style="margin: 0;" onsubmit="return confirm('Arzt entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
            <details>
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Arzt hinzufügen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.arzt.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Arzt *</label>
                                <select name="arzt_id" class="feld" required style="font-size: 0.875rem;">
                                    <option value="">— wählen —</option>
                                    @foreach(\App\Models\Arzt::where('organisation_id', auth()->user()->organisation_id)->where('aktiv', true)->orderBy('nachname')->get() as $a)
                                        <option value="{{ $a->id }}">{{ $a->vollname() }}{{ $a->praxis_name ? ' · ' . $a->praxis_name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Rolle *</label>
                                <select name="rolle" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\KlientArzt::$rollen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Gültig ab</label>
                                <input type="date" name="gueltig_ab" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div style="display: flex; align-items: flex-end; padding-bottom: 0.125rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                    <input type="hidden" name="hauptarzt" value="0">
                                    <input type="checkbox" name="hauptarzt" value="1"> Als Hauptarzt setzen
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Hinzufügen</button>
                    </form>
                </div>
            </details>
        </div>
    </details>

    {{-- Kontakte & Angehörige --}}
    @php $kontakte = $klient->kontakte()->where('aktiv', true)->get(); @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Kontakte &amp; Angehörige</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $kontakte->count() }} Kontakt/e · {{ $pflegendeAngehoerige->count() }} Pfl. Angeh.</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">

            {{-- Pflegender Angehöriger --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Pflegender Angehöriger</div>
            @if($pflegendeAngehoerige->isNotEmpty())
                @foreach($pflegendeAngehoerige as $pa)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border-radius: 6px; margin-bottom: 0.375rem;">
                    <div>
                        <span class="text-fett" style="font-size: 0.875rem;">{{ $pa->benutzer->vorname }} {{ $pa->benutzer->nachname }}</span>
                        <span class="badge badge-info" style="font-size: 0.7rem; margin-left: 0.5rem;">Pflegend</span>
                    </div>
                    <form method="POST" action="{{ route('klienten.angehoerig.entfernen', [$klient, $pa]) }}" onsubmit="return confirm('Zuweisung entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
                @endforeach
            @else
                <p class="text-klein text-hell" style="margin: 0 0 0.5rem;">Kein pflegender Angehöriger zugewiesen.</p>
            @endif
            @if(auth()->user()->rolle === 'admin' && $mitarbeiter->count())
            <form method="POST" action="{{ route('klienten.angehoerig.zuweisen', $klient) }}" style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
                @csrf
                <select name="benutzer_id" class="feld" required style="min-width: 200px; font-size: 0.875rem;">
                    <option value="">— Person wählen —</option>
                    @foreach($mitarbeiter as $m)
                        @if(!$pflegendeAngehoerige->contains('benutzer_id', $m->id))
                        <option value="{{ $m->id }}">{{ $m->nachname }} {{ $m->vorname }}</option>
                        @endif
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sekundaer">+ Zuweisen</button>
            </form>
            @endif

            {{-- Kontakte --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Kontakte</div>
            @if($kontakte->count())
            <div class="form-grid" style="margin-bottom: 0.75rem;">
                @foreach($kontakte as $k)
                <div style="border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem; background: var(--cs-hintergrund);">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.375rem;">
                        <span class="badge badge-info" style="font-size: 0.7rem;">{{ \App\Models\KlientKontakt::$rollen[$k->rolle] ?? $k->rolle }}</span>
                        <form method="POST" action="{{ route('klienten.kontakt.entfernen', [$klient, $k]) }}" style="margin: 0;" onsubmit="return confirm('Kontakt entfernen?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.8rem; padding: 0; line-height: 1;">×</button>
                        </form>
                    </div>
                    <div class="text-fett" style="font-size: 0.875rem;">{{ $k->vollname() }}</div>
                    @if($k->beziehung)<div class="text-hell" style="font-size: 0.8rem;">{{ $k->beziehung }}</div>@endif
                    @if($k->telefon)<div class="text-klein text-hell" style="margin-top: 0.25rem;">{{ $k->telefon }}</div>@endif
                    @if($k->telefon_mobil)<div class="text-klein text-hell">{{ $k->telefon_mobil }}</div>@endif
                    @if($k->email)<div class="text-klein text-hell">{{ $k->email }}</div>@endif
                    <div style="display: flex; gap: 0.375rem; flex-wrap: wrap; margin-top: 0.375rem;">
                        @if($k->bevollmaechtigt)<span class="badge badge-warnung" style="font-size: 0.7rem;">Bevollmächtigt</span>@endif
                        @if($k->rechnungen_erhalten)<span class="badge badge-info" style="font-size: 0.7rem;">Erhält Rechnungen</span>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <details>
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Kontakt hinzufügen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('klienten.kontakt.speichern', $klient) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Rolle *</label>
                                <select name="rolle" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\KlientKontakt::$rollen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Beziehung</label>
                                <input type="text" name="beziehung" class="feld" placeholder="z.B. Sohn, Tochter" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 120px 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Anrede</label>
                                <select name="anrede" class="feld" style="font-size: 0.875rem;">
                                    <option value="">—</option>
                                    <option value="Herr">Herr</option>
                                    <option value="Frau">Frau</option>
                                </select>
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Vorname</label>
                                <input type="text" name="vorname" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Nachname *</label>
                                <input type="text" name="nachname" class="feld" required style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div class="form-grid-3" style="gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Telefon</label>
                                <input type="text" name="telefon" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Mobile</label>
                                <input type="text" name="telefon_mobil" class="feld" style="font-size: 0.875rem;">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">E-Mail</label>
                                <input type="email" name="email" class="feld" style="font-size: 0.875rem;">
                            </div>
                        </div>
                        <div style="display: flex; gap: 1.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="hidden" name="bevollmaechtigt" value="0">
                                <input type="checkbox" name="bevollmaechtigt" value="1"> Bevollmächtigt
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="hidden" name="rechnungen_erhalten" value="0">
                                <input type="checkbox" name="rechnungen_erhalten" value="1"> Erhält Rechnungen
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Kontakt speichern</button>
                    </form>
                </div>
            </details>
        </div>
    </details>

    {{-- Adressen --}}
    @php
        $adressen    = $klient->adressen()->get();
        $adrRechnung = $adressen->firstWhere('adressart', 'rechnung');
        $adrNotfall  = $adressen->firstWhere('adressart', 'notfall');
        $adrHat = $adrRechnung || $adrNotfall;
    @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Adressen</span>
            <span class="text-hell" style="font-size: 0.75rem;">
                Einsatz: {{ trim(($klient->plz ?? '') . ' ' . ($klient->ort ?? '')) ?: '—' }}
                @if($adrRechnung) · Rechnung: {{ $adrRechnung->plz }} {{ $adrRechnung->ort }} @endif
                @if($adrNotfall) · Notfall: {{ $adrNotfall->plz }} {{ $adrNotfall->ort }} @endif
            </span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">

            {{-- Einsatzadresse (Klient-Modell, mit Kanton) --}}
            <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Einsatzadresse</div>
            <form method="POST" action="{{ route('klienten.update', $klient) }}" style="margin-bottom: 1.25rem;">
                @csrf @method('PUT')
                <input type="hidden" name="vorname"  value="{{ $klient->vorname }}">
                <input type="hidden" name="nachname" value="{{ $klient->nachname }}">
                <input type="hidden" name="aktiv"    value="{{ $klient->aktiv ? 1 : 0 }}">
                <div style="margin-bottom: 0.5rem;">
                    <label class="feld-label" style="font-size: 0.75rem;">Strasse &amp; Nr.</label>
                    <input type="text" name="adresse" class="feld" style="font-size: 0.875rem;" value="{{ old('adresse', $klient->adresse) }}">
                </div>
                <div style="display: grid; grid-template-columns: 80px 1fr 120px; gap: 0.5rem; margin-bottom: 0.625rem;">
                    <div>
                        <label class="feld-label" style="font-size: 0.75rem;">PLZ</label>
                        <input type="text" name="plz" class="feld" style="font-size: 0.875rem;" value="{{ old('plz', $klient->plz) }}">
                    </div>
                    <div>
                        <label class="feld-label" style="font-size: 0.75rem;">Ort</label>
                        <input type="text" name="ort" class="feld" style="font-size: 0.875rem;" value="{{ old('ort', $klient->ort) }}">
                    </div>
                    <div>
                        <label class="feld-label" style="font-size: 0.75rem;">Kanton</label>
                        <select name="region_id" class="feld" style="font-size: 0.875rem;">
                            <option value="">—</option>
                            @foreach(\App\Models\Region::orderBy('kuerzel')->get() as $r)
                                <option value="{{ $r->id }}" {{ $klient->region_id == $r->id ? 'selected' : '' }}>{{ $r->kuerzel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.3rem 0.75rem;">Speichern</button>
            </form>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">

                {{-- Rechnungsadresse --}}
                <div>
                    <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Rechnungsadresse</div>
                    @if($adrRechnung)
                    <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.625rem 0.75rem; font-size: 0.875rem; margin-bottom: 0.75rem;">
                        @if($adrRechnung->nachname)<div class="text-fett">{{ $adrRechnung->nachname }}</div>@endif
                        @if($adrRechnung->strasse)<div class="text-hell">{{ $adrRechnung->strasse }}</div>@endif
                        @if($adrRechnung->plz || $adrRechnung->ort)<div class="text-hell">{{ $adrRechnung->plz }} {{ $adrRechnung->ort }}</div>@endif
                        @if($adrRechnung->telefon)<div class="text-hell">{{ $adrRechnung->telefon }}</div>@endif
                        @if($adrRechnung->email)<div class="text-hell">{{ $adrRechnung->email }}</div>@endif
                        <form method="POST" action="{{ route('klienten.adresse.loeschen', [$klient, $adrRechnung]) }}" style="margin-top: 0.375rem;" onsubmit="return confirm('Adresse löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-fehler); font-size: 0.75rem; padding: 0;">× Löschen</button>
                        </form>
                    </div>
                    @endif
                    <details {{ !$adrRechnung ? 'open' : '' }}>
                        <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.25rem 0; list-style: none;">
                            {{ $adrRechnung ? '✎ Ändern' : '+ Erfassen' }}
                        </summary>
                        <form method="POST" action="{{ route('klienten.adresse.speichern', $klient) }}" style="margin-top: 0.5rem;">
                            @csrf
                            <input type="hidden" name="adressart" value="rechnung">
                            <div style="margin-bottom: 0.5rem;">
                                <label class="feld-label" style="font-size: 0.75rem;">Name / Firma</label>
                                <input type="text" name="name" class="feld" style="font-size: 0.875rem;" value="{{ old('name', $adrRechnung?->nachname) }}" placeholder="z.B. Sohn Hans Müller">
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <label class="feld-label" style="font-size: 0.75rem;">Strasse &amp; Nr.</label>
                                <input type="text" name="strasse" class="feld" style="font-size: 0.875rem;" value="{{ old('strasse', $adrRechnung?->strasse) }}">
                            </div>
                            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">PLZ</label>
                                    <input type="text" name="plz" class="feld" style="font-size: 0.875rem;" value="{{ old('plz', $adrRechnung?->plz) }}">
                                </div>
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">Ort</label>
                                    <input type="text" name="ort" class="feld" style="font-size: 0.875rem;" value="{{ old('ort', $adrRechnung?->ort) }}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.625rem;">
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">Telefon</label>
                                    <input type="text" name="telefon" class="feld" style="font-size: 0.875rem;" value="{{ old('telefon', $adrRechnung?->telefon) }}">
                                </div>
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">E-Mail</label>
                                    <input type="email" name="email" class="feld" style="font-size: 0.875rem;" value="{{ old('email', $adrRechnung?->email) }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.3rem 0.75rem;">Speichern</button>
                        </form>
                    </details>
                </div>

                {{-- Notfalladresse --}}
                <div>
                    <div class="abschnitt-label" style="margin-bottom: 0.625rem;">Notfalladresse</div>
                    @if($adrNotfall)
                    <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.625rem 0.75rem; font-size: 0.875rem; margin-bottom: 0.75rem;">
                        @if($adrNotfall->nachname)<div class="text-fett">{{ $adrNotfall->nachname }}</div>@endif
                        @if($adrNotfall->strasse)<div class="text-hell">{{ $adrNotfall->strasse }}</div>@endif
                        @if($adrNotfall->plz || $adrNotfall->ort)<div class="text-hell">{{ $adrNotfall->plz }} {{ $adrNotfall->ort }}</div>@endif
                        @if($adrNotfall->telefon)<div class="text-hell">{{ $adrNotfall->telefon }}</div>@endif
                        @if($adrNotfall->email)<div class="text-hell">{{ $adrNotfall->email }}</div>@endif
                        <form method="POST" action="{{ route('klienten.adresse.loeschen', [$klient, $adrNotfall]) }}" style="margin-top: 0.375rem;" onsubmit="return confirm('Adresse löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-fehler); font-size: 0.75rem; padding: 0;">× Löschen</button>
                        </form>
                    </div>
                    @endif
                    <details {{ !$adrNotfall ? 'open' : '' }}>
                        <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.25rem 0; list-style: none;">
                            {{ $adrNotfall ? '✎ Ändern' : '+ Erfassen' }}
                        </summary>
                        <form method="POST" action="{{ route('klienten.adresse.speichern', $klient) }}" style="margin-top: 0.5rem;">
                            @csrf
                            <input type="hidden" name="adressart" value="notfall">
                            <div style="margin-bottom: 0.5rem;">
                                <label class="feld-label" style="font-size: 0.75rem;">Name / Firma</label>
                                <input type="text" name="name" class="feld" style="font-size: 0.875rem;" value="{{ old('name', $adrNotfall?->nachname) }}" placeholder="z.B. Tochter Maria Meier">
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <label class="feld-label" style="font-size: 0.75rem;">Strasse &amp; Nr.</label>
                                <input type="text" name="strasse" class="feld" style="font-size: 0.875rem;" value="{{ old('strasse', $adrNotfall?->strasse) }}">
                            </div>
                            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">PLZ</label>
                                    <input type="text" name="plz" class="feld" style="font-size: 0.875rem;" value="{{ old('plz', $adrNotfall?->plz) }}">
                                </div>
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">Ort</label>
                                    <input type="text" name="ort" class="feld" style="font-size: 0.875rem;" value="{{ old('ort', $adrNotfall?->ort) }}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.625rem;">
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">Telefon</label>
                                    <input type="text" name="telefon" class="feld" style="font-size: 0.875rem;" value="{{ old('telefon', $adrNotfall?->telefon) }}">
                                </div>
                                <div>
                                    <label class="feld-label" style="font-size: 0.75rem;">E-Mail</label>
                                    <input type="email" name="email" class="feld" style="font-size: 0.875rem;" value="{{ old('email', $adrNotfall?->email) }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.3rem 0.75rem;">Speichern</button>
                        </form>
                    </details>
                </div>

            </div>
        </div>
    </details>

    {{-- Rapporte --}}
    @php $letzteRapporte = $klient->rapporte()->with('benutzer')->limit(5)->get(); @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Rapporte</span>
            <span style="font-size: 0.75rem;"><a href="{{ route('rapporte.create', ['klient_id' => $klient->id]) }}" class="link-primaer" onclick="event.stopPropagation()">+ Neuer Rapport</a></span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">
            @forelse($letzteRapporte as $r)
            <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem;">
                <div style="flex: 1;">
                    <span class="text-hell" style="font-size: 0.8rem; margin-right: 0.5rem;">{{ $r->datum->format('d.m.Y') }}</span>
                    <span class="badge {{ $r->rapport_typ === 'zwischenfall' ? 'badge-fehler' : 'badge-grau' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ \App\Models\Rapport::$typen[$r->rapport_typ] ?? $r->rapport_typ }}</span>
                    <span class="text-hell">{{ Str::limit($r->inhalt, 80) }}</span>
                </div>
                <a href="{{ route('rapporte.show', $r) }}" class="text-mini link-primaer" style="flex-shrink: 0;">Detail →</a>
            </div>
            @empty
            <p class="text-klein text-hell" style="margin: 0;">Noch keine Rapporte.</p>
            @endforelse
        </div>
    </details>

    {{-- Einzelleistungen --}}
    @if(in_array(auth()->user()->rolle, ['admin', 'buchhaltung']))
    <details id="einzelleistungen" style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;" {{ session('einzelleistung_offen') ? 'open' : '' }}>
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Einzelleistungen</span>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <button type="button"
                    onclick="event.preventDefault(); event.stopPropagation(); oeffneEinzelleistung({{ $klient->id }}, '{{ addslashes($klient->vorname . ' ' . $klient->nachname) }}')"
                    class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.2rem 0.6rem;">
                    + Einzelleistung
                </button>
                <span class="text-hell" style="font-size: 0.75rem;">{{ $einzelleistungen->count() }} Einträge</span>
            </div>
        </summary>
        <div style="padding: 0.75rem 1rem;">
            @if($einzelleistungen->isEmpty())
                <p class="text-klein text-hell" style="margin:0;">Keine Einzelleistungen erfasst.</p>
            @else
            <table class="tabelle" style="font-size:0.82rem;">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Beschreibung</th>
                        <th class="text-rechts">Betrag CHF</th>
                        <th class="text-mitte">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($einzelleistungen as $el)
                <tr>
                    <td>{{ $el->datum->format('d.m.Y') }}@if($el->datum_bis && $el->datum_bis != $el->datum) – {{ $el->datum_bis->format('d.m.Y') }}@endif</td>
                    <td>{{ $el->bemerkung }}</td>
                    <td class="text-rechts">{{ number_format($el->betrag_fix, 2, '.', "'") }}</td>
                    <td class="text-mitte">
                        @if($el->verrechnet)
                            <span class="badge badge-erfolg">Verrechnet</span>
                        @else
                            <span class="badge badge-warnung">Offen</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('rechnungen.einzelleistung.vorschau', $el) }}" target="_blank" class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.2rem 0.5rem;">PDF</a>
                        @if(!$el->verrechnet)
                        <button type="button" class="btn btn-sekundaer" style="font-size:0.75rem; padding:0.2rem 0.5rem;"
                            onclick="bearbeiteEinzelleistung({{ $el->id }}, '{{ $el->datum->format('Y-m-d') }}', '{{ $el->datum_bis ? $el->datum_bis->format('Y-m-d') : $el->datum->format('Y-m-d') }}', {{ json_encode($el->bemerkung) }}, '{{ $el->betrag_fix }}')">
                            Bearbeiten</button>
                        <form method="POST" action="{{ route('rechnungen.einzelleistung.loeschen', $el) }}" style="display:inline;" onsubmit="return confirm('Einzelleistung löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-gefahr" style="font-size:0.75rem; padding:0.2rem 0.5rem;">Löschen</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </details>
    @endif

    {{-- Tagespauschalen + Rechnungen --}}
    @if(in_array(auth()->user()->rolle, ['admin', 'buchhaltung']))
    {{-- Tagespauschalen --}}
    @php
        $tagespauschalen = \App\Models\Tagespauschale::where('klient_id', $klient->id)
            ->where('organisation_id', auth()->user()->organisation_id)
            ->orderByDesc('datum_von')
            ->get();
        $aktiveTagespauschale = $tagespauschalen->first(fn($tp) =>
            $tp->datum_von <= today() && $tp->datum_bis >= today()
        );
    @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Tagespauschalen</span>
            <span style="display: flex; align-items: center; gap: 0.5rem;">
                @if($aktiveTagespauschale)
                    <span class="badge badge-erfolg" style="font-size: 0.7rem;">
                        Aktiv · CHF {{ number_format($aktiveTagespauschale->ansatz, 2, '.', "'") }}/Tag
                    </span>
                @endif
                <span class="text-hell" style="font-size: 0.75rem;">{{ $tagespauschalen->count() }} Einträge</span>
            </span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">
            @if($aktiveTagespauschale)
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--cs-radius); padding: 0.625rem 0.875rem; margin-bottom: 0.875rem; font-size: 0.875rem;">
                <div style="font-weight: 600; color: #15803d;">Aktive Tagespauschale</div>
                <div class="text-hell">
                    {{ $aktiveTagespauschale->datum_von->format('d.m.Y') }} – {{ $aktiveTagespauschale->datum_bis->format('d.m.Y') }}
                    · CHF {{ number_format($aktiveTagespauschale->ansatz, 2, '.', "'") }}/Tag
                    · {{ $aktiveTagespauschale->rechnungstypLabel() }}
                    @if($aktiveTagespauschale->text) · {{ $aktiveTagespauschale->text }} @endif
                </div>
            </div>
            @endif

            @forelse($tagespauschalen as $tp)
            @php
                $aktiv       = $tp->datum_von <= today() && $tp->datum_bis >= today();
                $anzahlTage  = $tp->anzahlTage();
                $verrechnet  = $tp->anzahlVerrechnet();
                $offen       = $anzahlTage - $verrechnet;
            @endphp
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.625rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <span class="text-hell" style="white-space: nowrap;">
                        {{ $tp->datum_von->format('d.m.Y') }} – {{ $tp->datum_bis->format('d.m.Y') }}
                    </span>
                    <span class="text-fett">CHF {{ number_format($tp->ansatz, 2, '.', "'") }}/Tag</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">{{ $tp->rechnungstypLabel() }}</span>
                    @if($aktiv)<span class="badge badge-erfolg" style="font-size: 0.7rem;">Aktiv</span>@endif
                    {{-- Verrechnet / Offen --}}
                    @if($verrechnet > 0)
                        <span class="badge badge-primaer" style="font-size: 0.7rem;">{{ $verrechnet }}/{{ $anzahlTage }} verrechnet</span>
                    @endif
                    @if($offen > 0)
                        <span class="badge badge-warnung" style="font-size: 0.7rem;">{{ $offen }} offen</span>
                    @endif
                    @if($tp->text)<span class="text-hell" style="font-size: 0.8rem;">{{ $tp->text }}</span>@endif
                </div>
                <a href="{{ route('tagespauschalen.show', $tp) }}" class="text-mini link-primaer" style="flex-shrink: 0;">Detail →</a>
            </div>
            @empty
            <p class="text-klein text-hell" style="margin: 0 0 0.75rem;">Keine Tagespauschalen erfasst.</p>
            @endforelse

            <div style="margin-top: 0.875rem;">
                <a href="{{ route('tagespauschalen.create', ['klient_id' => $klient->id]) }}"
                   class="btn btn-sekundaer btn-sm" style="font-size: 0.8125rem;">+ Tagespauschale erfassen</a>
            </div>
        </div>
    </details>

    {{-- Rechnungen --}}
    @php
        $rechnungenTotal = $klient->rechnungen()->count();
        $rechnungen = $klient->rechnungen()
            ->orderByDesc('rechnungsdatum')
            ->orderByDesc('id')
            ->limit(15)
            ->get();
    @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Rechnungen</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $rechnungenTotal }} Rechnung/en</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">
            @forelse($rechnungen as $r)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.625rem; align-items: center; flex-wrap: wrap; flex: 1;">
                    <span class="text-hell" style="min-width: 90px; white-space: nowrap;">{{ $r->rechnungsdatum->format('d.m.Y') }}</span>
                    <span class="text-fett" style="font-family: monospace; font-size: 0.8rem;">{{ $r->rechnungsnummer }}</span>
                    {!! $r->typBadge() !!}
                    {!! $r->statusBadge() !!}
                    <span style="color: var(--cs-primaer); font-weight: 600;">CHF {{ number_format($r->betrag_total, 2, '.', "'") }}</span>
                    @if($r->betrag_kk > 0)
                        <span class="text-hell" style="font-size: 0.8rem;">KK: {{ number_format($r->betrag_kk, 2, '.', "'") }}</span>
                    @endif
                </div>
                <a href="{{ route('rechnungen.show', $r) }}" class="text-mini link-primaer" style="flex-shrink: 0;">Detail →</a>
            </div>
            @empty
            <p class="text-klein text-hell" style="margin: 0;">Noch keine Rechnungen erstellt.</p>
            @endforelse
            @if($rechnungenTotal > 0)
            <div style="margin-top: 0.875rem; display: flex; gap: 1rem; font-size: 0.8125rem;">
                <a href="{{ route('rechnungen.index') }}" class="link-primaer">→ Alle {{ $rechnungenTotal }} Rechnungen</a>
                <a href="{{ route('rechnungslauf.index') }}" class="link-gedaempt">Rechnungsläufe</a>
            </div>
            @endif
        </div>
    </details>
    @endif

    {{-- Dokumente --}}
    @php $dokumente = $klient->dokumente()->with('hochgeladenVon')->orderByDesc('created_at')->get(); @endphp
    <details style="background: #fff; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); margin-bottom: 0.5rem; overflow: hidden;">
        <summary style="padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; user-select: none;">
            <span>Dokumente</span>
            <span class="text-hell" style="font-size: 0.75rem;">{{ $dokumente->count() }} Datei/en</span>
        </summary>
        <div style="padding: 1rem; border-top: 1px solid var(--cs-border);">
            @if($dokumente->count())
            <div style="margin-bottom: 1rem;">
                @foreach($dokumente as $dok)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.4375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem;">
                    <div style="flex: 1;">
                        <span class="badge badge-grau" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ \App\Models\Dokument::$typen[$dok->dokument_typ] ?? $dok->dokument_typ }}</span>
                        <a href="{{ route('dokumente.download', $dok) }}" class="text-fett link-primaer">{{ $dok->bezeichnung }}</a>
                        <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">{{ $dok->groesseFormatiert() }}</span>
                        @if($dok->vertraulich)<span class="badge badge-warnung" style="font-size: 0.7rem; margin-left: 0.375rem;">Vertraulich</span>@endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $dok->created_at->format('d.m.Y') }}</span>
                        <form method="POST" action="{{ route('dokumente.destroy', $dok) }}" style="margin: 0;" onsubmit="return confirm('Dokument löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <details>
                <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none;">+ Dokument hochladen</summary>
                <div style="margin-top: 0.75rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                    <form method="POST" action="{{ route('dokumente.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="klient_id" value="{{ $klient->id }}">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Bezeichnung *</label>
                                <input type="text" name="bezeichnung" class="feld" required style="font-size: 0.875rem;" placeholder="z.B. Pflegevertrag 2024">
                            </div>
                            <div>
                                <label class="feld-label" style="font-size: 0.75rem;">Typ *</label>
                                <select name="dokument_typ" class="feld" required style="font-size: 0.875rem;">
                                    @foreach(\App\Models\Dokument::$typen as $wert => $lbl)
                                        <option value="{{ $wert }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="margin-bottom: 0.75rem;">
                            <label class="feld-label" style="font-size: 0.75rem;">Datei * (PDF, DOCX, XLSX, Bilder — max. 20 MB)</label>
                            <input type="file" name="datei" class="feld" required style="font-size: 0.875rem;"
                                accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png,.gif">
                            @error('datei')<div class="feld-fehler">{{ $message }}</div>@enderror
                        </div>
                        <div style="margin-bottom: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="hidden" name="vertraulich" value="0">
                                <input type="checkbox" name="vertraulich" value="1"> Vertraulich
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Hochladen</button>
                    </form>
                </div>
            </details>
        </div>
    </details>

</div>

@push('scripts')
<script>
function beitragBearbeiten(id) {
    const row = document.getElementById('beitrag-edit-' + id);
    if (row) row.style.display = row.style.display === 'none' ? '' : 'none';
}
function toggleKlientEdit() {
    const form = document.getElementById('klient-edit-form');
    const btn  = document.getElementById('btn-klient-edit');
    const open = form.style.display === 'none';
    form.style.display = open ? 'block' : 'none';
    btn.textContent    = open ? '✕ Schliessen' : '✏ Bearbeiten';
    if (open) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

var einsaetzePopupGeladen = false;

function oeffneEinsaetzePopup() {
    document.getElementById('einsaetze-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (einsaetzePopupGeladen) return;
    fetch('{{ route('klienten.einsaetze-popup', $klient) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            document.getElementById('einsaetze-popup-inhalt').innerHTML = html;
            einsaetzePopupGeladen = true;
        })
        .catch(function() {
            document.getElementById('einsaetze-popup-inhalt').innerHTML = '<p style="color:var(--cs-fehler);">Fehler beim Laden.</p>';
        });
}

function schliesseEinsaetzePopup() {
    document.getElementById('einsaetze-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function einsatzTabPopup(tab) {
    ['anstehend','vergangen','monat'].forEach(function(t) {
        var panel = document.getElementById('ppanel-' + t);
        var btn   = document.getElementById('ptab-' + t);
        if (panel) panel.style.display = t === tab ? 'block' : 'none';
        if (btn) {
            btn.style.borderBottomColor = t === tab ? 'var(--cs-primaer)' : 'transparent';
            btn.style.color             = t === tab ? 'var(--cs-primaer)' : 'var(--cs-text-hell)';
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') schliesseEinsaetzePopup();
});
</script>
@endpush

{{-- Popup: Einzelleistung erfassen / bearbeiten --}}
<div id="popup-einzelleistung-klient" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:200; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:1.5rem; width:380px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h3 id="el-titel" style="margin:0 0 0.25rem; font-size:1rem;">Einzelleistung erfassen</h3>
        <p id="el-klient-name" class="text-hell text-klein" style="margin:0 0 1rem;"></p>
        <form id="el-form" method="POST" action="{{ route('rechnungen.einzelleistung') }}">
            @csrf
            <input type="hidden" name="_method" id="el-method" value="POST">
            <input type="hidden" name="klient_id" id="el-klient-id">
            <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1rem;">
                <div class="form-grid-2" style="gap:0.75rem;">
                    <div>
                        <label class="feld-label">Datum von</label>
                        <input type="date" name="datum" id="el-datum-von" class="feld" value="{{ today()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="feld-label">Datum bis</label>
                        <input type="date" name="datum_bis" id="el-datum-bis" class="feld" value="{{ today()->format('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <label class="feld-label">Beschreibung (erscheint auf Rechnung)</label>
                    <input type="text" name="bemerkung" id="el-beschreibung" class="feld" maxlength="500" placeholder="z.B. Ausflug nach Bern" required>
                </div>
                <div>
                    <label class="feld-label">Betrag CHF</label>
                    <input type="number" name="betrag_fix" id="el-betrag" class="feld" min="0" step="any" placeholder="0.00" required>
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('popup-einzelleistung-klient').style.display='none'" class="btn btn-sekundaer">Abbrechen</button>
                <button type="submit" id="el-submit-btn" class="btn btn-primaer">Erfassen</button>
            </div>
        </form>
    </div>
</div>

<script>
function oeffneEinzelleistung(klientId, klientName) {
    document.getElementById('el-titel').textContent = 'Einzelleistung erfassen';
    document.getElementById('el-submit-btn').textContent = 'Erfassen';
    document.getElementById('el-form').action = '{{ route('rechnungen.einzelleistung') }}';
    document.getElementById('el-method').value = 'POST';
    document.getElementById('el-klient-id').value = klientId;
    document.getElementById('el-klient-name').textContent = klientName;
    document.getElementById('el-datum-von').value = '{{ today()->format('Y-m-d') }}';
    document.getElementById('el-datum-bis').value = '{{ today()->format('Y-m-d') }}';
    document.getElementById('el-beschreibung').value = '';
    document.getElementById('el-betrag').value = '';
    document.getElementById('popup-einzelleistung-klient').style.display = 'flex';
}
function bearbeiteEinzelleistung(id, datumVon, datumBis, beschreibung, betrag) {
    document.getElementById('el-titel').textContent = 'Einzelleistung bearbeiten';
    document.getElementById('el-submit-btn').textContent = 'Speichern';
    document.getElementById('el-form').action = '/rechnungen/einzelleistung/' + id;
    document.getElementById('el-method').value = 'PATCH';
    document.getElementById('el-klient-id').value = '';
    document.getElementById('el-klient-name').textContent = '';
    document.getElementById('el-datum-von').value = datumVon;
    document.getElementById('el-datum-bis').value = datumBis;
    document.getElementById('el-beschreibung').value = beschreibung;
    document.getElementById('el-betrag').value = betrag;
    document.getElementById('popup-einzelleistung-klient').style.display = 'flex';
}
</script>

</x-layouts.app>
