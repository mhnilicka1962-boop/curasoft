<x-layouts.app :titel="$klient->vollname()">

<div style="max-width: 860px;">

    {{-- Header --}}
    <div class="seiten-kopf">
        <a href="{{ route('klienten.index') }}" class="text-klein link-gedaempt">
            ← Alle Klienten
        </a>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if(!$klient->aktiv)
                <span class="badge badge-grau">Inaktiv</span>
            @endif
            <a href="{{ route('klienten.edit', $klient) }}" class="btn btn-sekundaer">Bearbeiten</a>
            <a href="{{ route('einsaetze.create', ['klient_id' => $klient->id]) }}" class="btn btn-primaer">+ Einsatz</a>
        </div>
    </div>

    {{-- Name & Basis-Info --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; align-items: flex-start; gap: 1.25rem;">
            <div style="width: 3rem; height: 3rem; border-radius: 50%; background-color: var(--cs-primaer-hell); color: var(--cs-primaer); display: flex; align-items: center; justify-content: center; font-size: 1.125rem; font-weight: 700; flex-shrink: 0;">
                {{ strtoupper(substr($klient->vorname, 0, 1)) }}{{ strtoupper(substr($klient->nachname, 0, 1)) }}
            </div>
            <div style="flex: 1;">
                <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.25rem;">
                    {{ $klient->vollname() }}
                </h2>
                <div class="text-klein text-hell" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @if($klient->geburtsdatum)
                        <span>geb. {{ $klient->geburtsdatum->format('d.m.Y') }} ({{ $klient->geburtsdatum->age }} J.)</span>
                    @endif
                    @if($klient->geschlecht)
                        <span>{{ ['m' => 'Männlich', 'w' => 'Weiblich', 'x' => 'Divers'][$klient->geschlecht] }}</span>
                    @endif
                    @if($klient->zivilstand)
                        <span>{{ ucfirst($klient->zivilstand) }}</span>
                    @endif
                    @if($klient->region)
                        <span class="badge badge-info" style="font-size: 0.75rem;">{{ $klient->region->kuerzel }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Planungs-Daten --}}
        @if($klient->einsatz_geplant_von || $klient->zustaendig)
        <div class="abschnitt-trenn text-klein text-hell" style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
            @if($klient->einsatz_geplant_von)
                <span>Einsatz:
                    <strong style="color: var(--cs-text);">{{ $klient->einsatz_geplant_von->format('d.m.Y') }}</strong>
                    @if($klient->einsatz_geplant_bis) – <strong style="color: var(--cs-text);">{{ $klient->einsatz_geplant_bis->format('d.m.Y') }}</strong> @endif
                </span>
            @endif
            @if($klient->datum_erstkontakt)
                <span>Erstkontakt: <strong style="color: var(--cs-text);">{{ $klient->datum_erstkontakt->format('d.m.Y') }}</strong></span>
            @endif
            @if($klient->zustaendig)
                <span>Zuständig: <strong style="color: var(--cs-text);">{{ $klient->zustaendig->name }}</strong></span>
            @endif
        </div>
        @endif
    </div>

    {{-- Pflegeplan: Nächste 14 Tage --}}
    @php
        $pflegeplan = $klient->einsaetze()
            ->with('benutzer', 'leistungsart')
            ->whereDate('datum', '>=', today())
            ->whereDate('datum', '<=', today()->addDays(13))
            ->whereNotIn('status', ['storniert'])
            ->orderBy('datum')->orderBy('zeit_von')
            ->get()
            ->groupBy(fn($e) => $e->datum->format('Y-m-d'));
        $gezeigteSeries = [];
    @endphp

    <div class="karte" style="margin-bottom: 1rem; padding: 0;">
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between;">
            <span class="abschnitt-label" style="margin: 0;">Pflegeplan — Nächste 14 Tage</span>
            <a href="{{ route('einsaetze.create', ['klient_id' => $klient->id]) }}" class="text-klein link-primaer">+ Einsatz planen</a>
        </div>

        @if($pflegeplan->isEmpty())
        <div style="padding: 1.25rem 1rem; text-align: center; font-size: 0.875rem; color: var(--cs-text-hell);">
            Keine Einsätze in den nächsten 14 Tagen geplant.
            <a href="{{ route('einsaetze.create', ['klient_id' => $klient->id]) }}" class="link-primaer" style="display: block; margin-top: 0.5rem;">→ Jetzt planen</a>
        </div>
        @else
        @foreach(collect(range(0,13))->map(fn($i) => today()->addDays($i)) as $tag)
        @php
            $key = $tag->format('Y-m-d');
            $tagesEinsaetze = $pflegeplan->get($key, collect());
            $istHeute = $tag->isToday();
            $hatEinsatz = $tagesEinsaetze->isNotEmpty();
        @endphp
        <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.5rem 1rem; border-bottom: 1px solid var(--cs-border);
            background: {{ $istHeute ? 'var(--cs-primaer-hell, #eff6ff)' : ($hatEinsatz ? 'transparent' : 'var(--cs-hintergrund)') }};">

            {{-- Datum --}}
            <div style="min-width: 100px; flex-shrink: 0;">
                <div style="font-size: 0.8125rem; font-weight: {{ $istHeute ? '700' : '400' }}; color: {{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text)' }};">
                    {{ $tag->isoFormat('dd, D. MMM') }}
                </div>
                @if($istHeute)
                    <span class="badge badge-primaer" style="font-size: 0.65rem;">Heute</span>
                @endif
            </div>

            {{-- Einsätze oder Lücke --}}
            @if($hatEinsatz)
            <div style="flex: 1; display: flex; flex-direction: column; gap: 0.25rem;">
                @foreach($tagesEinsaetze as $e)
                @php $zeigeSerieBtn = $e->serie_id && !in_array($e->serie_id, $gezeigteSeries); @endphp
                @if($zeigeSerieBtn) @php $gezeigteSeries[] = $e->serie_id; @endphp @endif
                <div style="display: flex; align-items: center; gap: 0.625rem; font-size: 0.8125rem; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: var(--cs-primaer);">
                        {{ $e->benutzer?->vorname }} {{ $e->benutzer?->nachname }}
                    </span>
                    <span class="text-hell">{{ $e->leistungsart?->bezeichnung }}</span>
                    @if($e->serie_id)
                        <span class="badge badge-info" style="font-size: 0.65rem;">Serie</span>
                    @endif
                    @if($e->zeit_von)
                        <span class="text-hell" style="font-size: 0.75rem; white-space: nowrap;">
                            {{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}
                        </span>
                    @endif
                    <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.65rem;">{{ $e->statusLabel() }}</span>
                    @if($zeigeSerieBtn)
                    <form method="POST" action="{{ route('einsaetze.serie.loeschen', $e->serie_id) }}" style="margin-left: auto;"
                        onsubmit="return confirm('Alle zukünftigen Einsätze dieser Serie löschen?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="_klient_redirect" value="{{ $klient->id }}">
                        <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 0.75rem; color: var(--cs-fehler); padding: 0; white-space: nowrap;">× Serie löschen</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div style="flex: 1; font-size: 0.8125rem; color: var(--cs-text-hell); font-style: italic;">
                Kein Einsatz geplant
            </div>
            @endif
        </div>
        @endforeach
        @endif
    </div>

    <div class="form-grid-2" style="gap: 1rem; margin-bottom: 1rem;">

        {{-- Kontakt --}}
        <div class="karte">
            <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Kontakt & Adresse</div>
            @foreach([
                'Adresse'      => trim(($klient->adresse ?? '') . ($klient->adresse ? ', ' : '') . ($klient->plz ?? '') . ' ' . ($klient->ort ?? '')) ?: null,
                'Telefon'      => $klient->telefon,
                'Notfall'      => $klient->notfallnummer,
                'E-Mail'       => $klient->email,
            ] as $label => $wert)
            @if($wert)
            <div style="display: flex; gap: 0.5rem; padding: 0.375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem;">
                <span class="text-hell" style="min-width: 80px; flex-shrink: 0;">{{ $label }}</span>
                <span>{{ $wert }}</span>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Krankenkasse --}}
        <div class="karte">
            <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Krankenkasse & AHV</div>
            @foreach([
                'Krankenkasse' => $klient->krankenkasse_name,
                'KK-Nr.'       => $klient->krankenkasse_nr,
                'AHV-Nr.'      => $klient->ahv_nr,
                'Zahlbar'      => $klient->zahlbar_tage ? $klient->zahlbar_tage . ' Tage' : null,
            ] as $label => $wert)
            @if($wert)
            <div style="display: flex; gap: 0.5rem; padding: 0.375rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem;">
                <span class="text-hell" style="min-width: 100px; flex-shrink: 0;">{{ $label }}</span>
                <span>{{ $wert }}</span>
            </div>
            @endif
            @endforeach
        </div>

    </div>

    {{-- Adressen --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
            <div class="abschnitt-label">Adressen</div>
        </div>

        @php $adressen = $klient->adressen()->with('region')->get(); @endphp

        {{-- Vorhandene Adressen --}}
        @if($adressen->count())
        <div class="form-grid" style="margin-bottom: 1rem;">
            @foreach($adressen as $adr)
            <div style="border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem; position: relative; background: var(--cs-hintergrund);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="badge badge-info" style="font-size: 0.7rem;">{{ $adr->artLabel() }}</span>
                    <form method="POST" action="{{ route('klienten.adresse.loeschen', [$klient, $adr]) }}" style="margin: 0;"
                        onsubmit="return confirm('Adresse entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.8rem; padding: 0; line-height: 1;">×</button>
                    </form>
                </div>
                @if($adr->vollname())
                    <div class="text-fett" style="font-size: 0.875rem; margin-bottom: 0.25rem;">{{ $adr->vollname() }}</div>
                @endif
                @if($adr->strasse)
                    <div class="text-klein text-hell">{{ $adr->strasse }}</div>
                @endif
                @if($adr->plz || $adr->ort)
                    <div class="text-klein text-hell">{{ $adr->plz }} {{ $adr->ort }}{{ $adr->region ? ' (' . $adr->region->kuerzel . ')' : '' }}</div>
                @endif
                @if($adr->telefon)
                    <div class="text-klein text-hell" style="margin-top: 0.25rem;">{{ $adr->telefon }}</div>
                @endif
                @if($adr->email)
                    <div class="text-klein text-hell">{{ $adr->email }}</div>
                @endif
                @if($adr->gueltig_ab)
                    <div class="text-mini text-hell" style="margin-top: 0.375rem; border-top: 1px solid var(--cs-border); padding-top: 0.25rem;">
                        ab {{ $adr->gueltig_ab->format('d.m.Y') }}
                        @if($adr->gueltig_bis) – {{ $adr->gueltig_bis->format('d.m.Y') }} @endif
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Neue Adresse hinzufügen --}}
        <details id="details-adresse" {{ $errors->any() ? 'open' : '' }}>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                <span>+ Adresse hinzufügen</span>
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <form method="POST" action="{{ route('klienten.adresse.speichern', $klient) }}">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Adressart *</label>
                            <select name="adressart" class="feld" required style="font-size: 0.875rem;">
                                <option value="">— wählen —</option>
                                @foreach(\App\Models\KlientAdresse::$arten as $wert => $label)
                                    <option value="{{ $wert }}" {{ old('adressart') === $wert ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Gültig ab</label>
                            <input type="date" name="gueltig_ab" class="feld" style="font-size: 0.875rem;"
                                value="{{ old('gueltig_ab') }}">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 140px 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Anrede</label>
                            <select name="anrede" class="feld" style="font-size: 0.875rem;">
                                <option value="">—</option>
                                <option value="Herr" {{ old('anrede') === 'Herr' ? 'selected' : '' }}>Herr</option>
                                <option value="Frau" {{ old('anrede') === 'Frau' ? 'selected' : '' }}>Frau</option>
                            </select>
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Vorname</label>
                            <input type="text" name="vorname" class="feld" style="font-size: 0.875rem;" value="{{ old('vorname') }}">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Nachname</label>
                            <input type="text" name="nachname" class="feld" style="font-size: 0.875rem;" value="{{ old('nachname') }}">
                        </div>
                    </div>

                    <div>
                        <label class="feld-label" style="font-size: 0.75rem;">Firma</label>
                        <input type="text" name="firma" class="feld" style="font-size: 0.875rem; margin-bottom: 0.75rem;" value="{{ old('firma') }}">
                    </div>

                    <div style="margin-bottom: 0.75rem;">
                        <label class="feld-label" style="font-size: 0.75rem;">Strasse & Nr.</label>
                        <input type="text" name="strasse" class="feld" style="font-size: 0.875rem;" value="{{ old('strasse') }}">
                    </div>

                    <div style="display: grid; grid-template-columns: 100px 1fr 100px; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">PLZ</label>
                            <input type="text" name="plz" class="feld" style="font-size: 0.875rem;" value="{{ old('plz') }}">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Ort</label>
                            <input type="text" name="ort" class="feld" style="font-size: 0.875rem;" value="{{ old('ort') }}">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Kanton</label>
                            <select name="region_id" class="feld" style="font-size: 0.875rem;">
                                <option value="">—</option>
                                @foreach(\App\Models\Region::orderBy('kuerzel')->get() as $r)
                                    <option value="{{ $r->id }}" {{ old('region_id') == $r->id ? 'selected' : '' }}>{{ $r->kuerzel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Telefon</label>
                            <input type="text" name="telefon" class="feld" style="font-size: 0.875rem;" value="{{ old('telefon') }}">
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">E-Mail</label>
                            <input type="email" name="email" class="feld" style="font-size: 0.875rem;" value="{{ old('email') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Adresse speichern</button>
                </form>
            </div>
        </details>
    </div>

    {{-- Ärzte --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Behandelnde Ärzte</div>

        @php $klientAerzte = $klient->aerzte()->with('arzt')->get(); @endphp

        @if($klientAerzte->count())
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
            @foreach($klientAerzte as $ka)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem;">
                <div>
                    @if($ka->hauptarzt)
                        <span class="badge badge-primaer" style="font-size: 0.7rem; margin-right: 0.375rem;">Hauptarzt</span>
                    @endif
                    <span class="text-fett">{{ $ka->arzt->vollname() }}</span>
                    <span class="text-hell" style="margin-left: 0.5rem;">{{ \App\Models\KlientArzt::$rollen[$ka->rolle] ?? $ka->rolle }}</span>
                    @if($ka->arzt->praxis_name)
                        <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">· {{ $ka->arzt->praxis_name }}</span>
                    @endif
                    @if($ka->arzt->telefon)
                        <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">{{ $ka->arzt->telefon }}</span>
                    @endif
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
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Arzt hinzufügen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
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

    {{-- Krankenkassen --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Krankenkassen</div>

        @php $klientKk = $klient->krankenkassen()->with('krankenkasse')->orderBy('versicherungs_typ')->get(); @endphp

        @if($klientKk->count())
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
            @foreach($klientKk as $kk)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem;">
                <div style="flex: 1;">
                    <span class="badge {{ $kk->versicherungs_typ === 'kvg' ? 'badge-erfolg' : 'badge-info' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $kk->typLabel() }}</span>
                    @if($kk->deckungstyp && $kk->deckungstyp !== 'allgemein')
                        <span class="badge badge-warnung" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ $kk->deckungLabel() }}</span>
                    @endif
                    <span class="badge {{ $kk->tiers_payant ? 'badge-erfolg' : 'badge-grau' }}" style="font-size: 0.7rem; margin-right: 0.375rem;" title="{{ $kk->tiers_payant ? 'KK zahlt direkt an Spitex' : 'Klient zahlt, holt sich Geld zurück' }}">{{ $kk->tiers_payant ? 'Tiers payant' : 'Tiers garant' }}</span>
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
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Krankenkasse hinzufügen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <form method="POST" action="{{ route('klienten.kk.speichern', $klient) }}">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 160px 180px 180px; gap: 0.75rem; margin-bottom: 0.75rem;">
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
                            <label class="feld-label" style="font-size: 0.75rem;">Deckung</label>
                            <select name="deckungstyp" class="feld" style="font-size: 0.875rem;">
                                <option value="">—</option>
                                @foreach(\App\Models\KlientKrankenkasse::$deckungstypen as $wert => $lbl)
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
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

    {{-- Beiträge --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Beiträge</div>

        @php $beitraege = $klient->beitraege()->with('erfasstVon')->get(); @endphp

        @if($beitraege->count())
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--cs-border);">
                    <th style="text-align: left; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Gültig ab</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Ansatz Kunde</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Limit %</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Ansatz SPITEX</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Kanton</th>
                    <th style="text-align: left; padding: 0.375rem 0.5rem; color: var(--cs-text-hell); font-weight: 600;">Erfasst</th>
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
                    <td class="text-hell text-mini" style="padding: 0.375rem 0.5rem;">
                        {{ $b->created_at->format('d.m.Y') }}
                        @if($b->erfasstVon) / {{ $b->erfasstVon->vorname }} {{ $b->erfasstVon->nachname }} @endif
                    </td>
                    <td class="text-rechts" style="padding: 0.375rem 0.5rem;">
                        <form method="POST" action="{{ route('klienten.beitrag.loeschen', [$klient, $b]) }}" style="margin: 0;" onsubmit="return confirm('Beitrag entfernen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: var(--cs-fehler); cursor: pointer; font-size: 0.75rem; padding: 0;">entfernen</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-klein text-hell" style="margin-bottom: 1rem;">Noch keine Beiträge erfasst.</p>
        @endif

        <details>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Beitrag erfassen
            </summary>
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

    {{-- Kontakte / Angehörige --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Kontakte &amp; Angehörige</div>

        @php $kontakte = $klient->kontakte()->where('aktiv', true)->get(); @endphp

        @if($kontakte->count())
        <div class="form-grid" style="margin-bottom: 1rem;">
            @foreach($kontakte as $k)
            <div style="border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem; position: relative; background: var(--cs-hintergrund);">
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
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Kontakt hinzufügen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
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

    {{-- Pflegestufen --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Pflegebedarf / Einstufungen</div>

        @php $pflegestufen = $klient->pflegestufen()->with('erfasstVon')->orderByDesc('einstufung_datum')->get(); @endphp

        @if($pflegestufen->count())
        <div style="margin-bottom: 1rem;">
            @foreach($pflegestufen as $ps)
            <div style="display: flex; align-items: center; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; flex-wrap: wrap;">
                <span class="text-hell" style="min-width: 90px;">{{ $ps->einstufung_datum->format('d.m.Y') }}</span>
                <span class="badge badge-info" style="font-size: 0.75rem;">{{ \App\Models\KlientPflegestufe::$instrumente[$ps->instrument] ?? $ps->instrument }}</span>
                <span style="font-weight: 700; font-size: 1rem;">Stufe {{ $ps->stufe }}</span>
                @if($ps->punkte)
                    <span class="text-hell">{{ number_format($ps->punkte, 1) }} Pkt.</span>
                @endif
                @if($ps->naechste_pruefung)
                    <span class="text-hell" style="font-size: 0.8rem;">Nächste Prüfung: {{ $ps->naechste_pruefung->format('d.m.Y') }}</span>
                @endif
                @if($ps->erfasstVon)
                    <span class="text-hell" style="font-size: 0.8rem; margin-left: auto;">{{ $ps->erfasstVon->vorname }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <details>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Einstufung erfassen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
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
    </div>

    {{-- Diagnosen --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Diagnosen (ICD-10)</div>

        @php $diagnosen = $klient->diagnosen()->with('arzt')->orderByDesc('datum_gestellt')->get(); @endphp

        @if($diagnosen->count())
        <div style="margin-bottom: 1rem;">
            @foreach($diagnosen as $d)
            <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem;">
                <div style="flex: 1;">
                    <span class="badge {{ $d->diagnose_typ === 'haupt' ? 'badge-fehler' : 'badge-grau' }}" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ \App\Models\KlientDiagnose::$typen[$d->diagnose_typ] ?? $d->diagnose_typ }}</span>
                    <span class="text-fett" style="font-family: monospace;">{{ $d->icd10_code }}</span>
                    <span style="margin-left: 0.5rem;">{{ $d->icd10_bezeichnung }}</span>
                    @if($d->arzt)
                        <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">· {{ $d->arzt->vollname() }}</span>
                    @endif
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    @if($d->datum_gestellt)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $d->datum_gestellt->format('d.m.Y') }}</span>
                    @endif
                    <form method="POST" action="{{ route('klienten.diagnose.entfernen', [$klient, $d]) }}" style="margin: 0;" onsubmit="return confirm('Diagnose deaktivieren?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--cs-text-hell); font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <details>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Diagnose hinzufügen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
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
    </div>

    {{-- Dokumente --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Dokumente</div>

        @php $dokumente = $klient->dokumente()->with('hochgeladenVon')->orderByDesc('created_at')->get(); @endphp

        @if($dokumente->count())
        <div style="margin-bottom: 1rem;">
            @foreach($dokumente as $dok)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem;">
                <div style="flex: 1;">
                    <span class="badge badge-grau" style="font-size: 0.7rem; margin-right: 0.375rem;">{{ \App\Models\Dokument::$typen[$dok->dokument_typ] ?? $dok->dokument_typ }}</span>
                    <a href="{{ route('dokumente.download', $dok) }}" class="text-fett link-primaer">{{ $dok->bezeichnung }}</a>
                    <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.5rem;">{{ $dok->groesseFormatiert() }}</span>
                    @if($dok->vertraulich)
                        <span class="badge badge-warnung" style="font-size: 0.7rem; margin-left: 0.375rem;">Vertraulich</span>
                    @endif
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
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Dokument hochladen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <form method="POST" action="{{ route('dokumente.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="klient_id" value="{{ $klient->id }}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Bezeichnung *</label>
                            <input type="text" name="bezeichnung" class="feld" required style="font-size: 0.875rem;"
                                placeholder="z.B. Pflegevertrag 2024">
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

    {{-- Rapporte --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
            <div class="abschnitt-label">Rapporte</div>
            <a href="{{ route('rapporte.create', ['klient_id' => $klient->id]) }}" class="text-klein link-primaer">+ Neuer Rapport</a>
        </div>

        @php $letzteRapporte = $klient->rapporte()->with('benutzer')->limit(5)->get(); @endphp

        @forelse($letzteRapporte as $r)
        <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem;">
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

    {{-- Einsätze --}}
    @php
        $heute = today();
        $alleEinsaetze = $klient->einsaetze()->with('leistungsart', 'benutzer')->orderBy('datum')->orderBy('zeit_von')->get();
        $anstehend = $alleEinsaetze->filter(fn($e) => $e->datum >= $heute && !in_array($e->status, ['abgeschlossen','storniert']))->values();
        $vergangen  = $alleEinsaetze->filter(fn($e) => $e->datum < $heute || in_array($e->status, ['abgeschlossen','storniert']))->sortByDesc('datum')->values();
    @endphp

    <div class="karte" id="einsaetze-section">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
            <div class="abschnitt-label">Einsätze</div>
        </div>

        {{-- Tabs --}}
        <div style="display: flex; gap: 0; border-bottom: 2px solid var(--cs-border); margin-bottom: 1rem;">
            <button onclick="einsatzTab('anstehend')" id="tab-anstehend"
                style="padding: 0.4rem 1rem; font-size: 0.8125rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid var(--cs-primaer); margin-bottom: -2px; cursor: pointer; color: var(--cs-primaer);">
                Anstehend <span style="font-weight: 400; font-size: 0.75rem; opacity: 0.7;">({{ $anstehend->count() }})</span>
            </button>
            <button onclick="einsatzTab('vergangen')" id="tab-vergangen"
                style="padding: 0.4rem 1rem; font-size: 0.8125rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
                Vergangen <span style="font-weight: 400; font-size: 0.75rem; opacity: 0.7;">({{ $vergangen->count() }})</span>
            </button>
        </div>

        {{-- Anstehend --}}
        <div id="panel-anstehend">
            @forelse($anstehend as $e)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <span style="color: {{ $e->datum->isToday() ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }}; min-width: 80px; font-weight: {{ $e->datum->isToday() ? '700' : '400' }};">
                        {{ $e->datum->format('d.m.Y') }}
                        @if($e->datum_bis) – {{ $e->datum_bis->format('d.m.Y') }} @endif
                    </span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="white-space: nowrap;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>
                    @endif
                    <span>{{ $e->leistungsart?->bezeichnung ?? '—' }}</span>
                    @if($e->benutzer)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="padding: 0.75rem 0; margin: 0;">Keine anstehenden Einsätze.</p>
            @endforelse
        </div>

        {{-- Vergangen --}}
        <div id="panel-vergangen" style="display: none;">
            @forelse($vergangen as $e)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <span class="text-hell" style="min-width: 80px;">
                        {{ $e->datum->format('d.m.Y') }}
                        @if($e->datum_bis) – {{ $e->datum_bis->format('d.m.Y') }} @endif
                    </span>
                    @if($e->zeit_von)
                        <span class="text-hell" style="white-space: nowrap;">{{ substr($e->zeit_von,0,5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis,0,5) : '' }}</span>
                    @endif
                    <span>{{ $e->leistungsart?->bezeichnung ?? '—' }}</span>
                    @if($e->benutzer)
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $e->benutzer->vorname }} {{ $e->benutzer->nachname }}</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    <a href="{{ route('einsaetze.show', $e) }}" class="text-mini link-primaer">Detail →</a>
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="padding: 0.75rem 0; margin: 0;">Keine vergangenen Einsätze.</p>
            @endforelse
        </div>

        {{-- Inline Planungsformular --}}
        <details style="margin-top: 1rem;" {{ session('erfolg') && str_contains(session('erfolg',''), 'geplant') ? 'open' : '' }}>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Einsatz planen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                @if($errors->has('datum') || $errors->has('leistungsart_id'))
                    <div class="alert alert-fehler" style="margin-bottom: 1rem; font-size: 0.875rem;">
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
                            <input type="time" name="zeit_von" class="feld" style="font-size: 0.875rem;"
                                value="{{ old('zeit_von') }}">
                        </div>

                        <div id="plan-block-bis">
                            <label class="feld-label" style="font-size: 0.75rem;">Bis</label>
                            <input type="time" name="zeit_bis" class="feld" style="font-size: 0.875rem;"
                                value="{{ old('zeit_bis') }}">
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

</div>

@push('scripts')
<script>
function einsatzTab(tab) {
    document.getElementById('panel-anstehend').style.display = tab === 'anstehend' ? 'block' : 'none';
    document.getElementById('panel-vergangen').style.display  = tab === 'vergangen'  ? 'block' : 'none';
    const primaer = getComputedStyle(document.documentElement).getPropertyValue('--cs-primaer').trim();
    const hell    = getComputedStyle(document.documentElement).getPropertyValue('--cs-text-hell').trim();
    document.getElementById('tab-anstehend').style.borderBottomColor = tab === 'anstehend' ? 'var(--cs-primaer)' : 'transparent';
    document.getElementById('tab-anstehend').style.color             = tab === 'anstehend' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)';
    document.getElementById('tab-vergangen').style.borderBottomColor = tab === 'vergangen'  ? 'var(--cs-primaer)' : 'transparent';
    document.getElementById('tab-vergangen').style.color             = tab === 'vergangen'  ? 'var(--cs-primaer)' : 'var(--cs-text-hell)';
}

const planLa       = document.getElementById('plan-la');
const planDatumBis = document.getElementById('plan-block-datum-bis');
const planVon      = document.getElementById('plan-block-von');
const planBis      = document.getElementById('plan-block-bis');

function aktualisierePlanForm() {
    const opt = planLa ? planLa.options[planLa.selectedIndex] : null;
    const istTage = opt && opt.dataset.einheit === 'tage';
    if (planDatumBis) planDatumBis.style.display = istTage ? 'block' : 'none';
    if (planVon)      planVon.style.display      = istTage ? 'none'  : 'block';
    if (planBis)      planBis.style.display      = istTage ? 'none'  : 'block';
}

if (planLa) {
    planLa.addEventListener('change', aktualisierePlanForm);
    aktualisierePlanForm();
}
</script>
@endpush

</x-layouts.app>
