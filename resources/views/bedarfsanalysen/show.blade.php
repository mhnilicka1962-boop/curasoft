<x-layout titel="Bedarfsanalyse — {{ $klient->nachname }} {{ $klient->vorname }}">

<div class="seiten-kopf">
    <div>
        <a href="{{ route('klienten.show', $klient) }}" class="btn btn-sekundaer" style="margin-bottom:0.5rem;">← Klient</a>
        <h1>Bedarfsanalyse — {{ $klient->vorname }} {{ $klient->nachname }}</h1>
        <p style="color:var(--cs-text-gedaempft); margin:0.25rem 0 0;">
            Erfasst am {{ $analyse->datum_analyse?->format('d.m.Y') ?? $analyse->created_at->format('d.m.Y') }}
            @if($analyse->ersteller) · {{ $analyse->ersteller->vorname }} {{ $analyse->ersteller->nachname }} @endif
        </p>
    </div>
</div>

@php
$sektionen = [
    1 => 'Personalien & Ansprechpersonen',
    2 => 'Versicherung & Details',
    3 => 'Medizin & Pflegestufe',
    4 => 'Verpflegung & Pflegedienst',
    5 => 'Wohnverhältnisse & Abschluss',
];
$pflegestufen = [
    'selbstaendig' => 'Selbstständig',
    'erheblich'    => 'Erheblich pflegebedürftig',
    'schwer'       => 'Schwerpflegebedürftig',
    'schwerst'     => 'Schwerstpflegebedürftig',
];
@endphp

@foreach($sektionen as $nr => $titel)
<details class="karte" style="margin-bottom:1rem;" open>
    <summary style="cursor:pointer; font-size:1rem; font-weight:600; padding:0.25rem 0;">
        {{ $nr }}. {{ $titel }}
    </summary>
    <div style="margin-top:1rem; border-top:1px solid var(--cs-rand); padding-top:1rem;">

    @if($nr === 1)
        <div class="detail-raster">
            @if($analyse->datum_analyse)<div><strong>Datum Analyse</strong><span>{{ $analyse->datum_analyse->format('d.m.Y') }}</span></div>@endif
            @if($analyse->ort_analyse)<div><strong>Ort</strong><span>{{ $analyse->ort_analyse }}</span></div>@endif
            @if($analyse->anrede || $analyse->vorname)<div><strong>Name</strong><span>{{ $analyse->anrede }} {{ $analyse->vorname }} {{ $analyse->nachname }}</span></div>@endif
            @if($analyse->strasse)<div><strong>Adresse</strong><span>{{ $analyse->strasse }}, {{ $analyse->plz }} {{ $analyse->ort }}</span></div>@endif
            @if($analyse->telefon)<div><strong>Telefon</strong><span>{{ $analyse->telefon }}</span></div>@endif
            @if($analyse->mobile)<div><strong>Mobile</strong><span>{{ $analyse->mobile }}</span></div>@endif
            @if($analyse->geburtsdatum)<div><strong>Geburtsdatum</strong><span>{{ $analyse->geburtsdatum->format('d.m.Y') }}</span></div>@endif
            @if($analyse->heimatort)<div><strong>Heimatort</strong><span>{{ $analyse->heimatort }}</span></div>@endif
            @if($analyse->konfession)<div><strong>Konfession</strong><span>{{ $analyse->konfession }}</span></div>@endif
            @if($analyse->zivilstand)<div><strong>Zivilstand</strong><span>{{ ucfirst($analyse->zivilstand) }}</span></div>@endif
            @if($analyse->nationalitaet)<div><strong>Nationalität</strong><span>{{ $analyse->nationalitaet }}</span></div>@endif
            @if($analyse->ahv_nr)<div><strong>AHV/SVN-Nr.</strong><span>{{ $analyse->ahv_nr }}</span></div>@endif
        </div>
        @if($analyse->ap1_name || $analyse->ap1_vorname)
        <h4 style="margin:1rem 0 0.5rem;">Ansprechperson 1</h4>
        <div class="detail-raster">
            <div><strong>Name</strong><span>{{ $analyse->ap1_vorname }} {{ $analyse->ap1_name }}</span></div>
            @if($analyse->ap1_beziehung)<div><strong>Beziehung</strong><span>{{ $analyse->ap1_beziehung }}</span></div>@endif
            @if($analyse->ap1_telefon)<div><strong>Telefon</strong><span>{{ $analyse->ap1_telefon }}</span></div>@endif
            @if($analyse->ap1_mobile)<div><strong>Mobile</strong><span>{{ $analyse->ap1_mobile }}</span></div>@endif
            @if($analyse->ap1_vormund)<div><strong>Vormund</strong><span>Ja</span></div>@endif
            @if($analyse->ap1_erreichbarkeit)<div><strong>Erreichbarkeit</strong><span>{{ $analyse->ap1_erreichbarkeit === '24h' ? '24 Stunden' : 'Tagsüber ' . $analyse->ap1_erreichbarkeit_von . '–' . $analyse->ap1_erreichbarkeit_bis }}</span></div>@endif
        </div>
        @endif
        @if($analyse->ap2_name || $analyse->ap2_vorname)
        <h4 style="margin:1rem 0 0.5rem;">Ansprechperson 2</h4>
        <div class="detail-raster">
            <div><strong>Name</strong><span>{{ $analyse->ap2_vorname }} {{ $analyse->ap2_name }}</span></div>
            @if($analyse->ap2_beziehung)<div><strong>Beziehung</strong><span>{{ $analyse->ap2_beziehung }}</span></div>@endif
            @if($analyse->ap2_telefon)<div><strong>Telefon</strong><span>{{ $analyse->ap2_telefon }}</span></div>@endif
            @if($analyse->ap2_mobile)<div><strong>Mobile</strong><span>{{ $analyse->ap2_mobile }}</span></div>@endif
        </div>
        @endif

    @elseif($nr === 2)
        <div class="detail-raster">
            @if($analyse->kvg_krankenkasse)<div><strong>KVG</strong><span>{{ $analyse->kvg_krankenkasse }}</span></div>@endif
            @if($analyse->kvg_anschrift)<div><strong>KVG Anschrift</strong><span>{{ $analyse->kvg_anschrift }}</span></div>@endif
            @if($analyse->vvg_vorhanden)<div><strong>VVG</strong><span>Ja — {{ ucfirst($analyse->vvg_deckungstyp ?? '—') }}</span></div>@endif
            @if($analyse->pflegeversicherung)<div><strong>Pflegeversicherung</strong><span>{{ $analyse->pflegeversicherung_name ?? 'Ja' }}</span></div>@endif
            @if($analyse->zweite_krankenkasse)<div><strong>Zweite KK</strong><span>{{ $analyse->zweite_krankenkasse }}</span></div>@endif
            @if($analyse->aufnahmegrund)<div><strong>Aufnahmegrund</strong><span>{{ ucfirst($analyse->aufnahmegrund) }}</span></div>@endif
            @if($analyse->hilflosenentschaedigung)<div><strong>Hilflosenentsch.</strong><span>{{ ucfirst($analyse->hilflosenentschaedigung) }}</span></div>@endif
            @if($analyse->vorauszahlung)<div><strong>Vorauszahlung</strong><span>Ja</span></div>@endif
            @if($analyse->zustaendiger_arzt)<div><strong>Zuständiger Arzt</strong><span>{{ $analyse->zustaendiger_arzt }}</span></div>@endif
            @if($analyse->personen_haushalt)<div><strong>Personen im Haushalt</strong><span>{{ $analyse->personen_haushalt }}</span></div>@endif
            @if($analyse->personen_betreuungsbed)<div><strong>Davon betreuungsbed.</strong><span>{{ $analyse->personen_betreuungsbed }}</span></div>@endif
            @if($analyse->gewicht_kg)<div><strong>Gewicht</strong><span>{{ $analyse->gewicht_kg }} kg</span></div>@endif
        </div>

    @elseif($nr === 3)
        <div class="detail-raster">
            @if($analyse->diagnosen_text)<div style="grid-column:1/-1"><strong>Diagnosen</strong><span style="white-space:pre-wrap;">{{ $analyse->diagnosen_text }}</span></div>@endif
            @if($analyse->medikamente_liste)<div><strong>Medikamentenliste</strong><span>Vorhanden</span></div>@endif
            @if($analyse->mobilitaet)<div style="grid-column:1/-1"><strong>Mobilität</strong><span style="white-space:pre-wrap;">{{ $analyse->mobilitaet }}</span></div>@endif
            @if($analyse->hilfsmittel)<div><strong>Hilfsmittel</strong><span>{{ $analyse->hilfsmittel }}</span></div>@endif
            @if($analyse->hobbies)<div><strong>Hobbies & Interessen</strong><span>{{ $analyse->hobbies }}</span></div>@endif
            @if($analyse->pflegestufe)<div><strong>Pflegestufe</strong><span>{{ $pflegestufen[$analyse->pflegestufe] ?? ucfirst($analyse->pflegestufe) }}</span></div>@endif
        </div>

    @elseif($nr === 4)
        <div class="detail-raster">
            @if($analyse->wunschkost)<div><strong>Wunschkost</strong><span>{{ $analyse->wunschkost_details ?? 'Ja' }}</span></div>@endif
            @if($analyse->pflegedienst_aktuell)
            <div><strong>Pflegedienst</strong><span>{{ $analyse->pflegedienst_name ?? 'Vorhanden' }}</span></div>
            @if($analyse->pflegedienst_frequenz)<div><strong>Frequenz</strong><span>{{ $analyse->pflegedienst_frequenz }}</span></div>@endif
            @if($analyse->pflegedienst_aufgaben)<div style="grid-column:1/-1"><strong>Aufgaben</strong><span>{{ $analyse->pflegedienst_aufgaben }}</span></div>@endif
            @if($analyse->pflegedienst_abbestellen)<div><strong>Abbestellen</strong><span>Ja</span></div>@endif
            @endif
            @if(isset($analyse->raucher))<div><strong>Raucher</strong><span>{{ $analyse->raucher ? 'Ja' : 'Nein' }}</span></div>@endif
        </div>

    @elseif($nr === 5)
        <div class="detail-raster">
            @if($analyse->wohntyp)<div><strong>Wohntyp</strong><span>{{ ucfirst($analyse->wohntyp) }}</span></div>@endif
            @if($analyse->anzahl_zimmer)<div><strong>Zimmer</strong><span>{{ $analyse->anzahl_zimmer }}</span></div>@endif
            <div><strong>Lift</strong><span>{{ $analyse->lift ? 'Ja' : 'Nein' }}</span></div>
            @if($analyse->treppe)<div><strong>Treppe</strong><span>{{ $analyse->treppe_stufen ? $analyse->treppe_stufen . ' Stufen' : 'Ja' }}</span></div>@endif
            @if($analyse->klinik)<div><strong>Klinik</strong><span>{{ $analyse->klinik }}</span></div>@endif
            <div><strong>Patientenverfügung</strong><span>{{ $analyse->patientenverfuegung ? 'Vorhanden' : 'Nicht vorhanden' }}</span></div>
            @if($analyse->haustiere)<div><strong>Haustiere</strong><span>{{ $analyse->haustiere_details ?? 'Ja' }}</span></div>@endif
            @if($analyse->eintrittstermin)<div><strong>Eintrittstermin</strong><span>{{ $analyse->eintrittstermin->format('d.m.Y') }}</span></div>@endif
        </div>
    @endif

        <div style="margin-top:1rem; text-align:right;">
            <a href="{{ route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => $nr]) }}"
               class="btn btn-sekundaer">Bearbeiten</a>
        </div>
    </div>
</details>
@endforeach

</x-layout>
