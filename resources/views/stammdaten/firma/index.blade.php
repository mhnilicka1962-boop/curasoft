<x-layouts.app :titel="'Firma / Organisation'">
<div style="max-width: 900px;">

    <div style="margin-bottom: 1.25rem;">
        <div class="text-mini text-hell">Stammdaten</div>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Firma / Organisation</h1>
    </div>

    <form method="POST" action="{{ route('firma.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Abschnitt: Firmadaten --}}
        <div class="karte" style="margin-bottom: 1rem;">
            <div class="abschnitt-label" style="margin-bottom: 1rem;">
                Firmastammdaten
            </div>

            <div class="form-grid-2" style="gap: 0.875rem;">
                <div style="grid-column: 1 / -1;">
                    <label class="feld-label">Firmaname <span style="color:var(--cs-fehler);">*</span></label>
                    <input type="text" name="name" class="feld" value="{{ old('name', $org->name) }}" required>
                    @error('name') <div style="color:var(--cs-fehler);font-size:0.8125rem;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="feld-label">ZSR-Nr. (Spitex-Zulassung)</label>
                    <input type="text" name="zsr_nr" class="feld" value="{{ old('zsr_nr', $org->zsr_nr) }}" placeholder="M0842.03">
                    <div class="text-mini text-hell" style="margin-top: 0.2rem;">Ausgestellt von SASIS AG — pro Kanton überschreibbar (Abschnitt Kantone)</div>
                </div>
                <div>
                    <label class="feld-label">GLN (13-stellig, GS1)</label>
                    <input type="text" name="gln" class="feld" value="{{ old('gln', $org->gln) }}" placeholder="7601003XXXXXX" maxlength="13" pattern="[0-9]{13}">
                    <div class="text-mini text-hell" style="margin-top: 0.2rem;">Pflicht für XML 450.100 — beantragen bei <a href="https://www.gs1.ch" target="_blank" style="color:var(--cs-primaer);">gs1.ch</a></div>
                </div>
                <div>
                    <label class="feld-label">MWST-Nr.</label>
                    <input type="text" name="mwst_nr" class="feld" value="{{ old('mwst_nr', $org->mwst_nr) }}" placeholder="CHE-115.863.341">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="feld-label">Strasse / Adresse</label>
                    <input type="text" name="adresse" class="feld" value="{{ old('adresse', $org->adresse) }}">
                </div>
                <div>
                    <label class="feld-label">Postfach</label>
                    <input type="text" name="postfach" class="feld" value="{{ old('postfach', $org->postfach) }}" placeholder="Postfach 6">
                </div>
                <div>
                    <label class="feld-label">Adresszusatz</label>
                    <input type="text" name="adresszusatz" class="feld" value="{{ old('adresszusatz', $org->adresszusatz) }}">
                </div>
                <div>
                    <label class="feld-label">PLZ</label>
                    <input type="text" name="plz" class="feld" value="{{ old('plz', $org->plz) }}" placeholder="6024">
                </div>
                <div>
                    <label class="feld-label">Ort</label>
                    <input type="text" name="ort" class="feld" value="{{ old('ort', $org->ort) }}" placeholder="Hildisrieden">
                </div>
                <div>
                    <label class="feld-label">Telefon</label>
                    <input type="text" name="telefon" class="feld" value="{{ old('telefon', $org->telefon) }}" placeholder="041 450 50 40">
                </div>
                <div>
                    <label class="feld-label">Telefax</label>
                    <input type="text" name="fax" class="feld" value="{{ old('fax', $org->fax) }}" placeholder="041 450 50 41">
                </div>
                <div>
                    <label class="feld-label">E-Mail</label>
                    <input type="email" name="email" class="feld" value="{{ old('email', $org->email) }}" placeholder="info@curapflege.ch">
                </div>
                <div>
                    <label class="feld-label">Website</label>
                    <input type="text" name="website" class="feld" value="{{ old('website', $org->website) }}" placeholder="www.curapflege.ch">
                </div>
            </div>
        </div>

        {{-- Abschnitt: Bank (Standardwerte) --}}
        <div class="karte" style="margin-bottom: 1rem;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">
                Bankverbindung (Standard — gilt für alle Kantone ohne eigene Angaben)
            </div>
            <div class="text-klein text-hell" style="margin-bottom: 1rem;">
                Pro Kanton können separate IBAN / ESR-Nummern hinterlegt werden (Abschnitt weiter unten).
            </div>

            <div class="form-grid-2" style="gap: 0.875rem;">
                <div>
                    <label class="feld-label">Bank</label>
                    <input type="text" name="bank" class="feld" value="{{ old('bank', $org->bank) }}" placeholder="Postfinance">
                </div>
                <div>
                    <label class="feld-label">Bankadresse</label>
                    <input type="text" name="bankadresse" class="feld" value="{{ old('bankadresse', $org->bankadresse) }}" placeholder="3000 Bern">
                </div>
                <div>
                    <label class="feld-label">IBAN (21-stellig)</label>
                    <input type="text" name="iban" class="feld" value="{{ old('iban', $org->iban) }}" placeholder="CH08 0900 0000 6049 0383 6" maxlength="30">
                </div>
                <div>
                    <label class="feld-label">Postkonto / Kontonummer</label>
                    <input type="text" name="postcheckkonto" class="feld" value="{{ old('postcheckkonto', $org->postcheckkonto) }}" placeholder="60-490383-6">
                </div>
            </div>
        </div>

        {{-- Abschnitt: Rechnungseinstellungen --}}
        <div class="karte" style="margin-bottom: 1.5rem;">
            <div class="abschnitt-label" style="margin-bottom: 1rem;">
                Rechnungseinstellungen
            </div>

            <div style="margin-bottom: 0.875rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="druck_mit_firmendaten" value="1"
                        {{ old('druck_mit_firmendaten', $org->druck_mit_firmendaten) ? 'checked' : '' }}
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Druck mit Firmendaten
                </label>
            </div>

            <div class="form-grid-2" style="gap: 0.875rem;">
                <div>
                    <label class="feld-label">Rechnungsadresse</label>
                    <select name="rechnungsadresse_position" class="feld">
                        <option value="links"  {{ old('rechnungsadresse_position', $org->rechnungsadresse_position) === 'links'  ? 'selected' : '' }}>Links (Standard)</option>
                        <option value="rechts" {{ old('rechnungsadresse_position', $org->rechnungsadresse_position) === 'rechts' ? 'selected' : '' }}>Rechts</option>
                    </select>
                </div>
                <div>
                    <label class="feld-label">Logo-Ausrichtung</label>
                    <select name="logo_ausrichtung" class="feld">
                        <option value="links_anschrift_rechts"      {{ old('logo_ausrichtung', $org->logo_ausrichtung) === 'links_anschrift_rechts'      ? 'selected' : '' }}>Logo links — Anschrift rechts</option>
                        <option value="rechts_anschrift_links"      {{ old('logo_ausrichtung', $org->logo_ausrichtung) === 'rechts_anschrift_links'      ? 'selected' : '' }}>Logo rechts — Anschrift links</option>
                        <option value="mitte_anschrift_fusszeile"   {{ old('logo_ausrichtung', $org->logo_ausrichtung) === 'mitte_anschrift_fusszeile'   ? 'selected' : '' }}>Logo Mitte — Anschrift Fusszeile</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Abschnitt: Design & Logo --}}
        <div class="karte" style="margin-bottom: 1rem;">
            <div class="abschnitt-label" style="margin-bottom: 1rem;">Design & Logo</div>

            {{-- Aktuelles Logo --}}
            @if($org->logo_pfad)
            <div style="margin-bottom: 1rem;">
                <div class="feld-label">Aktuelles Logo</div>
                <img src="{{ asset($org->logo_pfad) }}" alt="Logo"
                    style="max-height: 48px; max-width: 200px; object-fit: contain; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.5rem; background: #fff; margin-top: 0.375rem; display: block;">
            </div>
            @endif

            {{-- Logo hochladen --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label" for="logo">{{ $org->logo_pfad ? 'Neues Logo hochladen' : 'Logo hochladen' }}</label>
                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/gif"
                    class="feld" style="padding: 0.375rem;" onchange="logoVorschau(this)">
                <div class="text-mini text-hell" style="margin-top: 0.25rem;">PNG, SVG oder JPG — max. 2 MB. Empfohlen: transparenter Hintergrund, ca. 200×50 px.</div>
                <div id="logo-vorschau-wrapper" style="display: none; margin-top: 0.75rem;">
                    <img id="logo-vorschau-img" src="" alt="Vorschau"
                        style="max-height: 48px; max-width: 200px; object-fit: contain; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.5rem; background: #fff;">
                </div>
            </div>

            {{-- Primärfarbe --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label">Primärfarbe</label>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
                    <input type="color" id="farbe_picker" value="{{ old('theme_farbe_primaer', $org->theme_farbe_primaer ?? '#2563eb') }}"
                        style="width: 2.5rem; height: 2.5rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); cursor: pointer; padding: 2px;"
                        oninput="document.getElementById('farbe_hex').value=this.value; document.getElementById('theme_farbe_primaer').value=this.value; document.getElementById('farb-demo').style.backgroundColor=this.value;">
                    <input type="text" id="farbe_hex" value="{{ old('theme_farbe_primaer', $org->theme_farbe_primaer ?? '#2563eb') }}"
                        class="feld" style="max-width: 120px; font-family: monospace;" placeholder="#2563eb"
                        oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('farbe_picker').value=this.value; document.getElementById('theme_farbe_primaer').value=this.value; document.getElementById('farb-demo').style.backgroundColor=this.value;}">
                    <input type="hidden" name="theme_farbe_primaer" id="theme_farbe_primaer" value="{{ old('theme_farbe_primaer', $org->theme_farbe_primaer ?? '#2563eb') }}">
                    <div id="farb-demo" style="width: 2.5rem; height: 2.5rem; border-radius: var(--cs-radius); background-color: {{ old('theme_farbe_primaer', $org->theme_farbe_primaer ?? '#2563eb') }};"></div>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap;">
                    @foreach(['#2563eb','#16a34a','#9333ea','#dc2626','#d97706','#0891b2','#1f2937'] as $farbeOpt)
                        <button type="button"
                            style="width: 1.5rem; height: 1.5rem; border-radius: 50%; background-color: {{ $farbeOpt }}; border: 2px solid transparent; cursor: pointer;"
                            onclick="setFarbe('{{ $farbeOpt }}')"></button>
                    @endforeach
                </div>
            </div>

            {{-- Navigation --}}
            <div>
                <label class="feld-label">Navigation</label>
                <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
                    @php $aktLayout = old('theme_layout', $org->theme_layout ?? 'sidebar'); @endphp
                    @foreach(['sidebar' => ['◫', 'Sidebar', 'Navigation links'], 'topnav' => ['⬒', 'Top-Navigation', 'Navigation oben']] as $val => [$icon, $lbl, $desc])
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="theme_layout" value="{{ $val }}"
                            {{ $aktLayout === $val ? 'checked' : '' }}
                            style="display: none;" class="layout-radio-firma">
                        <div style="border: 2px solid {{ $aktLayout === $val ? 'var(--cs-primaer)' : 'var(--cs-border)' }}; border-radius: var(--cs-radius); padding: 0.75rem; text-align: center; font-size: 0.8125rem; transition: border-color 0.15s;">
                            <div style="font-size: 1.25rem; margin-bottom: 0.25rem;">{{ $icon }}</div>
                            <div style="font-weight: 500;">{{ $lbl }}</div>
                            <div style="color: var(--cs-text-hell); font-size: 0.75rem;">{{ $desc }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <button type="submit" class="btn btn-primaer">Firmadaten speichern</button>
        </div>
    </form>

    <script>
    function logoVorschau(input) {
        const wrapper = document.getElementById('logo-vorschau-wrapper');
        const img = document.getElementById('logo-vorschau-img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; wrapper.style.display = 'block'; };
            reader.readAsDataURL(input.files[0]);
        } else {
            wrapper.style.display = 'none';
        }
    }
    function setFarbe(hex) {
        document.getElementById('farbe_picker').value = hex;
        document.getElementById('farbe_hex').value = hex;
        document.getElementById('theme_farbe_primaer').value = hex;
        document.getElementById('farb-demo').style.backgroundColor = hex;
    }
    document.querySelectorAll('.layout-radio-firma').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.layout-radio-firma').forEach(r => {
                r.closest('label').querySelector('div').style.borderColor = 'var(--cs-border)';
            });
            radio.closest('label').querySelector('div').style.borderColor = 'var(--cs-primaer)';
        });
    });
    </script>

    {{-- Abschnitt: Kantone --}}
    <div class="karte-null" style="margin-bottom: 1rem;">
        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);">
            <div class="abschnitt-label">
                Kantone / Tätigkeitsgebiet
            </div>
            <div class="text-klein text-hell" style="margin-top: 0.25rem;">
                Hier pro Kanton separate IBAN, ESR-Nr. oder QR-IBAN hinterlegen. Leere Felder = Standard-Bankdaten der Firma.
            </div>
        </div>

        @if($orgRegionenMap->isNotEmpty())
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Kanton</th>
                    <th>ZSR-Nr.</th>
                    <th>IBAN (Override)</th>
                    <th>Postkonto</th>
                    <th>ESR-Nr.</th>
                    <th>QR-IBAN</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orgRegionenMap->sortBy(fn($r) => $r->region->kuerzel ?? '') as $orgRegion)
                <tr>
                    <td class="text-fett">
                        {{ $orgRegion->region->kuerzel }}
                        <span class="text-hell" style="font-weight: 400; font-size: 0.8125rem;">{{ $orgRegion->region->bezeichnung }}</span>
                    </td>
                    <td style="font-size: 0.8125rem; font-family: monospace;">
                        @if($orgRegion->zsr_nr)
                            <strong>{{ $orgRegion->zsr_nr }}</strong>
                        @else
                            <span class="text-hell">↑ Haupt</span>
                        @endif
                    </td>
                    <td style="font-size: 0.8125rem; font-family: monospace;">{{ $orgRegion->iban ?: '—' }}</td>
                    <td style="font-size: 0.8125rem; font-family: monospace;">{{ $orgRegion->postcheckkonto ?: '—' }}</td>
                    <td style="font-size: 0.8125rem; font-family: monospace;">{{ $orgRegion->esr_teilnehmernr ?: '—' }}</td>
                    <td style="font-size: 0.8125rem; font-family: monospace;">{{ $orgRegion->qr_iban ?: '—' }}</td>
                    <td>
                        @if($orgRegion->aktiv)
                            <span class="badge badge-erfolg">Aktiv</span>
                        @else
                            <span class="badge badge-grau">Inaktiv</span>
                        @endif
                    </td>
                    <td class="text-rechts">
                        <button onclick="kantonBearbeiten({{ $orgRegion->region_id }}, '{{ $orgRegion->region->kuerzel }}', '{{ $orgRegion->zsr_nr }}', '{{ $orgRegion->iban }}', '{{ $orgRegion->postcheckkonto }}', '{{ $orgRegion->esr_teilnehmernr }}', '{{ $orgRegion->qr_iban }}', {{ $orgRegion->aktiv ? 'true' : 'false' }})"
                            class="btn btn-sekundaer" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Bearbeiten
                        </button>
                        <form method="POST" action="{{ route('firma.region.entfernen', $orgRegion->region) }}" style="display:inline;" onsubmit="return confirm('Kanton entfernen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-gefahr" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 1.5rem; text-align: center;" class="text-hell">
            Noch keine Kantone konfiguriert.
        </div>
        @endif
    </div>

    {{-- Kanton hinzufügen / bearbeiten --}}
    <div class="karte" id="kanton-formular">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">
            Kanton hinzufügen / bearbeiten
        </div>

        <form method="POST" action="{{ route('firma.region.speichern') }}">
            @csrf

            <div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Kanton</label>
                    <select name="region_id" class="feld" required id="kanton-region-id">
                        <option value="">— wählen —</option>
                        @foreach($alleRegionen as $region)
                            <option value="{{ $region->id }}">{{ $region->kuerzel }} — {{ $region->bezeichnung }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label">ZSR-Nr. (falls abweichend vom Standard)</label>
                    <input type="text" name="zsr_nr" id="kanton-zsr" class="feld" placeholder="M0842.04" maxlength="20">
                    <div class="text-mini text-hell" style="margin-top: 0.2rem;">Leer = Standard-ZSR der Firma</div>
                </div>
            </div>

            <div class="form-grid-3" style="gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">IBAN (falls abweichend)</label>
                    <input type="text" name="iban" id="kanton-iban" class="feld" placeholder="CH08 0900 0000 …" maxlength="30">
                </div>
                <div>
                    <label class="feld-label">Postkonto (falls abweichend)</label>
                    <input type="text" name="postcheckkonto" id="kanton-postcheckkonto" class="feld" placeholder="60-490383-6">
                </div>
                <div>
                    <label class="feld-label">Bank (falls abweichend)</label>
                    <input type="text" name="bank" id="kanton-bank" class="feld" placeholder="Kantonalbank ZH">
                </div>
            </div>

            <div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">ESR-Teilnehmernr. (für ESR-Einzahlungsschein)</label>
                    <input type="text" name="esr_teilnehmernr" id="kanton-esr" class="feld" placeholder="01-xxxxx-x" maxlength="20">
                </div>
                <div>
                    <label class="feld-label">QR-IBAN (für QR-Rechnung)</label>
                    <input type="text" name="qr_iban" id="kanton-qr-iban" class="feld" placeholder="CH04 3080 8000 …" maxlength="30">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="aktiv" id="kanton-aktiv" value="1" checked
                        style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Kanton ist aktiv (Org tätig in diesem Kanton)
                </label>
            </div>

            {{-- Info-Box: Fallback-Logik --}}
            <div style="background: var(--cs-primaer-hell); border-radius: var(--cs-radius); padding: 0.625rem 0.875rem; margin-bottom: 1rem; font-size: 0.8125rem; color: var(--cs-text-hell);">
                Leere Felder = Haupt-Bankdaten der Firma werden verwendet. Nur ausfüllen wenn für diesen Kanton abweichende Bankdaten gelten.
            </div>

            <button type="submit" class="btn btn-primaer">Kanton speichern</button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function kantonBearbeiten(regionId, kuerzel, zsrNr, iban, postcheckkonto, esr, qrIban, aktiv) {
    document.getElementById('kanton-region-id').value = regionId;
    document.getElementById('kanton-zsr').value = zsrNr || '';
    document.getElementById('kanton-iban').value = iban || '';
    document.getElementById('kanton-postcheckkonto').value = postcheckkonto || '';
    document.getElementById('kanton-esr').value = esr || '';
    document.getElementById('kanton-qr-iban').value = qrIban || '';
    document.getElementById('kanton-aktiv').checked = aktiv;
    document.getElementById('kanton-formular').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush

{{-- Bexio Integration --}}
<div class="karte" style="margin-top: 1rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Bexio Integration</div>

    <form method="POST" action="{{ route('firma.bexio.speichern') }}">
        @csrf
        <div class="form-grid-2" style="gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label class="feld-label">API-Key</label>
                <input type="password" name="bexio_api_key" class="feld"
                    value="{{ old('bexio_api_key', $org->bexio_api_key ? '••••••••' : '') }}"
                    placeholder="sk_live_…"
                    autocomplete="off">
                <div class="text-mini text-hell" style="margin-top: 0.25rem;">
                    Bexio Settings → API-Keys
                </div>
            </div>
            <div>
                <label class="feld-label">Mandant-ID</label>
                <input type="text" name="bexio_mandant_id" class="feld"
                    value="{{ old('bexio_mandant_id', $org->bexio_mandant_id) }}"
                    placeholder="123456">
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="submit" class="btn btn-sekundaer">Bexio-Einstellungen speichern</button>
            @if($org->bexio_api_key)
                <a href="{{ route('firma.bexio.testen') }}" class="btn btn-sekundaer">Verbindung testen</a>
                <span style="font-size: 0.8125rem; color: var(--cs-erfolg);">API-Key konfiguriert</span>
            @else
                <span class="text-klein text-hell">Nicht konfiguriert</span>
            @endif
        </div>
    </form>
</div>

</x-layouts.app>
