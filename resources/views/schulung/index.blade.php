<x-layouts.app titel="Schulung">

<div style="max-width: 860px;">

<div class="seiten-kopf">
    <h1>Schulung & Einführung</h1>
    <a href="{{ route('hilfe') }}" class="btn btn-sekundaer">Hilfe & Scripts →</a>
</div>

{{-- Intro --}}
<div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-left: 4px solid var(--cs-primaer); border-radius: var(--cs-radius); padding: 1rem 1.25rem; margin-bottom: 1.75rem;">
    <div class="text-fett" style="margin-bottom: 0.3rem;">Wie funktioniert die Einführung?</div>
    <div class="text-klein" style="color: var(--cs-text-hell); line-height: 1.6;">
        Ein neuer Kunde ist in <strong>2–3 Stunden</strong> produktiv — das sind keine Wochen mit Handbüchern.
        Wir empfehlen: <strong>1 Live-Schulung (60 min)</strong> + diese Unterlagen zum Nachschlagen.
        Die Schritt-für-Schritt-Scripts in der <a href="{{ route('hilfe') }}" class="link-primaer">Hilfe-Seite</a> decken alle Alltagsprozesse ab.
    </div>
</div>

{{-- Lernpfad --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Empfohlener Lernpfad — Neue Spitex</div>

    <div style="display: flex; flex-direction: column; gap: 0;">

        @php
        $module = [
            ['nr' => 1, 'dauer' => '15 min', 'titel' => 'Ersteinrichtung', 'beschreibung' => 'Firma, Kanton/Region, Tarife — einmalig, dann nie wieder', 'links' => [
                ['label' => 'Firma einrichten', 'route' => 'firma.index'],
                ['label' => 'Region anlegen', 'route' => 'regionen.index'],
            ]],
            ['nr' => 2, 'dauer' => '10 min', 'titel' => 'Erster Klient', 'beschreibung' => 'Name, Adresse, Krankenkasse — 3 Pflichtfelder, Rest optional', 'links' => [
                ['label' => 'Klient anlegen', 'route' => 'klienten.index'],
            ]],
            ['nr' => 3, 'dauer' => '10 min', 'titel' => 'Mitarbeiterin einladen', 'beschreibung' => 'E-Mail eingeben → Einladungslink automatisch versendet → fertig', 'links' => [
                ['label' => 'Mitarbeitende', 'route' => 'mitarbeiter.index'],
            ]],
            ['nr' => 4, 'dauer' => '10 min', 'titel' => 'Einsatz planen', 'beschreibung' => 'Klient + Mitarbeiterin + Datum + Zeit → Einsatz erscheint im Kalender. Tipp: Doppelklick auf eine freie Zeitstelle im Kalender erstellt direkt einen neuen Einsatz — Mitarbeiter und Zeit werden automatisch übernommen. Wiederkehrende Einsätze als Serie anlegen mit automatischer Verlängerung — kein manuelles Nacherfassen.', 'links' => [
                ['label' => 'Einsatz erstellen', 'route' => 'einsaetze.create'],
                ['label' => 'Kalender', 'route' => 'kalender.index'],
            ]],
            ['nr' => 5, 'dauer' => '10 min', 'titel' => 'Rapport schreiben', 'beschreibung' => 'Nach dem Einsatz: Vor-Ort-Seite → Rapport → in beliebiger Sprache einsprechen → KI schreibt automatisch einen sauberen deutschen Bericht', 'links' => [
                ['label' => 'Rapporte', 'route' => 'rapporte.index'],
            ]],
            ['nr' => 6, 'dauer' => '15 min', 'titel' => 'Monatliche Abrechnung', 'beschreibung' => 'Rechnungslauf → alle Klienten mit einem Klick abrechnen → PDF/XML → Versand', 'links' => [
                ['label' => 'Rechnungsläufe', 'route' => 'rechnungslauf.index'],
                ['label' => 'Personalabrechnung', 'route' => 'personalabrechnung.index'],
            ]],
        ];
        @endphp

        @foreach($module as $i => $m)
        <div style="display: flex; gap: 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--cs-border);' : '' }} padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">{{ $m['nr'] }}</div>
                @if(!$loop->last)
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
                @endif
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.2rem;">
                    <span class="text-fett">{{ $m['titel'] }}</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">{{ $m['dauer'] }}</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.5rem;">{{ $m['beschreibung'] }}</div>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    @foreach($m['links'] as $link)
                    <a href="{{ route($link['route']) }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">{{ $link['label'] }} →</a>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>

{{-- Rollen-Übersicht --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Wer sieht was? — Rollenübersicht</div>
    <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Funktion</th>
                    <th class="text-mitte">Admin</th>
                    <th class="text-mitte">Pflege</th>
                    <th class="text-mitte">Buchhaltung</th>
                </tr>
            </thead>
            <tbody>
                @php
                $zugaenge = [
                    ['Dashboard, Nachrichten', true, true, true],
                    ['Klienten ansehen', true, true, false],
                    ['Einsätze ansehen / Vor-Ort', true, true, false],
                    ['Rapporte schreiben', true, true, false],
                    ['Touren / Kalender', true, 'nur eigene', false],
                    ['Meine Arbeitszeit (Zeitnachweis)', true, true, false],
                    ['Rechnungen, Rechnungsläufe', true, false, true],
                    ['Personalabrechnung (alle MA)', true, false, true],
                    ['Mitarbeitende verwalten', true, false, false],
                    ['Stammdaten, Einstellungen', true, false, false],
                    ['Angehörigenpflege-Übersicht', true, false, false],
                    ['Audit-Log', true, false, false],
                ];
                @endphp
                @foreach($zugaenge as $z)
                <tr>
                    <td class="text-klein">{{ $z[0] }}</td>
                    <td class="text-mitte">
                        @if($z[1] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[1] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[1] }}</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($z[2] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[2] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[2] }}</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($z[3] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[3] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[3] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Tagesabläufe --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tagesablauf Admin</div>
        <ol style="margin: 0 0 0 1.1rem; padding: 0; line-height: 2; font-size: 0.875rem;">
            <li>Dashboard → Einsätze heute prüfen</li>
            <li>Kalender → Lücken / Doppelbelegungen</li>
            <li>Nachrichten prüfen</li>
            <li>Neue Klienten / Rapporte bearbeiten</li>
            <li><em>Monatlich:</em> Rechnungslauf starten</li>
        </ol>
    </div>

    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tagesablauf Pflege</div>
        <ol style="margin: 0 0 0 1.1rem; padding: 0; line-height: 2; font-size: 0.875rem;">
            <li>Dashboard → Einsätze heute</li>
            <li>Vor Ort → Check-in beim Klienten</li>
            <li>Leistungen erfassen (Checkliste)</li>
            <li>Rapport schreiben (KI hilft)</li>
            <li>Check-out → nächster Einsatz</li>
        </ol>
    </div>

</div>


{{-- FAQ --}}
<div class="karte">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Häufige Fragen — Kurzantworten</div>

    @php
    $faq = [
        ['F', 'Wie loggt sich eine neue Pflegerin ein?', 'Sie erhält automatisch einen Einladungslink per E-Mail — einfach anklicken, Passwort setzen, fertig. Kein IT-Aufwand.'],
        ['F', 'Kann ich das Handy verwenden?', 'Ja — die Vor-Ort-Ansicht ist für Mobile optimiert. Check-in, Rapport, Leistungen — alles geht am Telefon.'],
        ['F', 'Was ist der Unterschied Einsatz / Tour?', 'Einsatz = ein Besuch bei einem Klienten. Tour = Tagesroute einer Pflegerin mit mehreren Einsätzen. Eine Pflegerin hat eine Tour, darin mehrere Einsätze.'],
        ['F', 'Wie funktioniert die Abrechnung?', 'Monatsende: Rechnungslauf → alle Einsätze des Monats automatisch zu Rechnungen → PDF ausdrucken oder per E-Mail versenden.'],
        ['F', 'Was ist eine Einsatzserie?', 'Eine Serie generiert automatisch alle wiederkehrenden Einsätze — z.B. jeden Montag und Mittwoch. Mit «Automatisch verlängern» läuft sie unbegrenzt weiter, ohne dass jemand manuell eingreifen muss.'],
        ['F', 'Kann die Pflegerin in einer anderen Sprache diktieren?', 'Ja — die KI versteht beliebige Sprachen (Serbisch, Albanisch, Portugiesisch usw.) und schreibt den Bericht immer auf Deutsch.'],
        ['F', 'Was ist Tiers payant / Tiers garant?', 'Tiers payant: Krankenkasse bezahlt direkt. Tiers garant: Klient bezahlt, KK erstattet. Wird pro Klient/KK eingestellt.'],
        ['F', 'Wie ändere ich Tarife?', 'Einstellungen → Regionen → Kanton wählen → Tarif für Leistungsart anpassen. Alte Tarife bleiben als Historie gespeichert.'],
        ['F', 'Was ist Angehörigenpflege?', 'Familienangehörige werden als Mitarbeitende angestellt und pflegen ihre Verwandten gegen Lohn (CHF ~37.90/h). Die Spitex übernimmt Aufsicht und Lohnabrechnung.'],
    ];
    @endphp

    @foreach($faq as $f)
    <div style="{{ !$loop->last ? 'border-bottom: 1px solid var(--cs-border);' : '' }} padding: 0.7rem 0;">
        <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
            <span class="badge badge-primaer" style="flex-shrink: 0; margin-top: 0.1rem; font-size: 0.7rem;">{{ $f[0] }}</span>
            <div>
                <div class="text-fett text-klein">{{ $f[1] }}</div>
                <div class="text-klein text-hell" style="margin-top: 0.15rem; line-height: 1.5;">{{ $f[2] }}</div>
            </div>
        </div>
    </div>
    @endforeach

</div>

</div>

@push('styles')
<style>
@media (max-width: 768px) {
    .schulung-grid { grid-template-columns: 1fr !important; }
}
</style>
@endpush

</x-layouts.app>
