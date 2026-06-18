<x-layout titel="Bedarfsanalyse abschliessen">

<div class="seiten-kopf">
    <h1>Bedarfsanalyse abschliessen</h1>
    <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => 5]) }}"
       class="btn btn-sekundaer">← Zurück</a>
</div>

{{-- Zusammenfassung --}}
<div class="karte" style="margin-bottom:1.5rem;">
    <h2 style="margin:0 0 1rem;">Zusammenfassung</h2>

    <div class="detail-raster">
        <div><strong>Name</strong><span>{{ $analyse->anzeigeName() }}</span></div>
        @if($analyse->geburtsdatum)
        <div><strong>Geburtsdatum</strong><span>{{ $analyse->geburtsdatum->format('d.m.Y') }}</span></div>
        @endif
        @if($analyse->adresse ?? $analyse->strasse)
        <div><strong>Adresse</strong><span>{{ $analyse->strasse }}, {{ $analyse->plz }} {{ $analyse->ort }}</span></div>
        @endif
        @if($analyse->telefon)
        <div><strong>Telefon</strong><span>{{ $analyse->telefon }}</span></div>
        @endif
        @if($analyse->ahv_nr)
        <div><strong>AHV-Nr.</strong><span>{{ $analyse->ahv_nr }}</span></div>
        @endif
        @if($analyse->kvg_krankenkasse)
        <div><strong>KVG</strong><span>{{ $analyse->kvg_krankenkasse }}</span></div>
        @endif
        @if($analyse->pflegestufe)
        <div><strong>Pflegestufe</strong><span>{{ ucfirst($analyse->pflegestufe) }}</span></div>
        @endif
        @if($analyse->eintrittstermin)
        <div><strong>Eintrittstermin</strong><span>{{ $analyse->eintrittstermin->format('d.m.Y') }}</span></div>
        @endif
    </div>
</div>

{{-- Klient-Einstellungen --}}
<div class="karte">
    <h2 style="margin:0 0 1rem;">Klient anlegen</h2>
    <p style="color:var(--cs-text-gedaempft); margin-bottom:1.5rem;">
        Bitte Region und Rechnungstyp wählen — diese sind für die Abrechnung erforderlich.
    </p>

    <form method="POST" action="{{ route('bedarfsanalysen.abschliessen', $analyse) }}">
        @csrf
        <div class="form-grid">
            <div class="feld">
                <label>Region / Kanton <span style="color:red">*</span></label>
                <select name="region_id" required>
                    <option value="">— bitte wählen —</option>
                    @foreach($regionen as $r)
                    <option value="{{ $r->id }}" @selected(old('region_id') == $r->id)>
                        {{ $r->bezeichnung }} ({{ $r->kuerzel }})
                    </option>
                    @endforeach
                </select>
                @error('region_id')<span class="fehler-text">{{ $message }}</span>@enderror
            </div>
            <div class="feld">
                <label>Rechnungstyp <span style="color:red">*</span></label>
                <select name="rechnungstyp" required>
                    <option value="kombiniert" @selected(old('rechnungstyp','kombiniert')==='kombiniert')>Kombiniert (Standard)</option>
                    <option value="tiers_garant" @selected(old('rechnungstyp')==='tiers_garant')>Tiers garant</option>
                    <option value="tiers_payant" @selected(old('rechnungstyp')==='tiers_payant')>Tiers payant</option>
                </select>
            </div>
        </div>

        <div style="margin-top:1.5rem; display:flex; gap:1rem; justify-content:flex-end;">
            <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => 5]) }}"
               class="btn btn-sekundaer">Zurück</a>
            <button type="submit" class="btn btn-primaer">Klient anlegen &amp; Bedarfsanalyse abschliessen</button>
        </div>
    </form>
</div>

</x-layout>
