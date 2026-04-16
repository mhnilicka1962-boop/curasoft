<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9.5pt;
    color: #1a1a2e;
    line-height: 1.55;
    padding: 18mm 18mm 15mm 18mm;
  }

  /* Header */
  .header {
    border-bottom: 3px solid #2563eb;
    padding-bottom: 10px;
    margin-bottom: 16px;
  }
  .header-title {
    font-size: 17pt;
    font-weight: bold;
    color: #1e3a8a;
    letter-spacing: -0.3px;
  }
  .header-meta {
    font-size: 8.5pt;
    color: #64748b;
    margin-top: 3px;
  }
  .header-zoom {
    font-size: 8.5pt;
    color: #2563eb;
    margin-top: 2px;
  }

  /* Vorbereitung Tab-Tabelle */
  .prep-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 5px;
    padding: 10px 12px;
    margin-bottom: 16px;
  }
  .prep-box h2 {
    font-size: 9.5pt;
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .prep-table { width: 100%; border-collapse: collapse; }
  .prep-table td {
    padding: 3px 8px 3px 0;
    font-size: 8.5pt;
    vertical-align: top;
  }
  .prep-table td:first-child { width: 20px; color: #94a3b8; font-weight: bold; }
  .prep-table td:nth-child(2) { width: 140px; font-weight: bold; color: #1e3a8a; }
  .prep-table td:nth-child(3) { color: #2563eb; font-size: 7.8pt; }

  /* Blöcke */
  .block {
    margin-bottom: 13px;
    page-break-inside: avoid;
  }
  .block-header {
    display: table;
    width: 100%;
    margin-bottom: 5px;
  }
  .block-badge {
    display: inline-block;
    background: #1e3a8a;
    color: #fff;
    font-size: 7pt;
    font-weight: bold;
    padding: 2px 7px;
    border-radius: 10px;
    vertical-align: middle;
    margin-right: 6px;
    letter-spacing: 0.3px;
  }
  .block-title {
    font-size: 10.5pt;
    font-weight: bold;
    color: #1e3a8a;
    vertical-align: middle;
  }
  .block-duration {
    font-size: 7.5pt;
    color: #94a3b8;
    margin-left: 6px;
    vertical-align: middle;
  }
  .block-frage {
    background: #fef9c3;
    border-left: 3px solid #f59e0b;
    padding: 4px 8px;
    font-size: 8pt;
    color: #78350f;
    margin-bottom: 6px;
    font-style: italic;
    border-radius: 0 3px 3px 0;
  }
  .link-pill {
    display: inline-block;
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 7.8pt;
    color: #1d4ed8;
    margin-bottom: 5px;
    font-weight: bold;
  }
  .step { margin-bottom: 4px; padding-left: 14px; position: relative; font-size: 8.8pt; }
  .step::before { content: "→"; position: absolute; left: 0; color: #2563eb; font-weight: bold; }
  .quote {
    background: #f8fafc;
    border-left: 3px solid #cbd5e1;
    padding: 5px 9px;
    font-size: 8pt;
    color: #475569;
    font-style: italic;
    margin: 5px 0 5px 14px;
    border-radius: 0 3px 3px 0;
  }
  .substep { margin: 2px 0 2px 26px; font-size: 8pt; color: #334155; }
  .substep::before { content: "·"; margin-right: 5px; color: #94a3b8; }

  /* Nächste Schritte */
  .next-box {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 5px;
    padding: 9px 12px;
    margin-bottom: 13px;
  }
  .next-box h2 {
    font-size: 9.5pt;
    font-weight: bold;
    color: #14532d;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .next-item { margin-bottom: 4px; padding-left: 14px; position: relative; font-size: 8.8pt; }
  .next-item::before { content: "✓"; position: absolute; left: 0; color: #16a34a; font-weight: bold; }
  .next-blank { border-bottom: 1px dotted #86efac; display: inline-block; width: 150px; height: 11px; margin-left: 4px; vertical-align: bottom; }

  /* Zeitpuffer */
  .puffer-box {
    background: #fafafa;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 8px 12px;
  }
  .puffer-box h2 {
    font-size: 8.5pt;
    font-weight: bold;
    color: #475569;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .puffer-item { font-size: 8pt; color: #64748b; margin-bottom: 3px; padding-left: 12px; position: relative; }
  .puffer-item::before { content: "·"; position: absolute; left: 0; }

  /* Footer */
  .footer { margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 7pt; color: #94a3b8; text-align: center; }

  /* Seitenumbruch-Kontrolle */
  .no-break { page-break-inside: avoid; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
  <div class="header-title">Demo-Script &mdash; Jessica Osmanoska</div>
  <div class="header-meta">Dauer: ~30 Minuten &nbsp;|&nbsp; Format: Zoom &nbsp;|&nbsp; Datum: 16. oder 17. April 2026</div>
  <div class="header-zoom">Zoom: https://zoom.us/j/6781624030</div>
</div>

<!-- VORBEREITUNG -->
<div class="prep-box no-break">
  <h2>Vorbereitung &mdash; Diese Tabs vorgeöffnet halten</h2>
  <table class="prep-table">
    <tr>
      <td>1</td>
      <td>Admin-Dashboard</td>
      <td>https://curasoft.ch/demo/admin</td>
    </tr>
    <tr>
      <td>2</td>
      <td>Rapporte-Liste</td>
      <td>https://curasoft.ch/demo/admin?goto=/rapporte</td>
    </tr>
    <tr>
      <td>3</td>
      <td>Rapport erstellen</td>
      <td>https://curasoft.ch/demo/admin?goto=/rapporte/create</td>
    </tr>
    <tr>
      <td>4</td>
      <td>Rechnungsläufe</td>
      <td>https://curasoft.ch/demo/admin?goto=/rechnungen/lauf</td>
    </tr>
    <tr>
      <td>5</td>
      <td>Kalender</td>
      <td>https://curasoft.ch/demo/admin?goto=/kalender</td>
    </tr>
    <tr>
      <td>6</td>
      <td>Pflege-Ansicht</td>
      <td>https://curasoft.ch/demo/pflege &nbsp;(Smartphone!)</td>
    </tr>
  </table>
</div>

<!-- BLOCK 1 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 1</span>
    <span class="block-title">Begrüssung &amp; Fahrplan</span>
    <span class="block-duration">2 Min.</span>
  </div>
  <div class="quote">„Frau Osmanoska, ich zeige Ihnen heute das System anhand Ihrer konkreten Fragen — Pflegedokumentation, Abrechnung und Einsatzplanung. Alles live in unserer Demo-Umgebung mit echten Testdaten. Am Schluss schauen wir noch die Kantonsfrage an und besprechen die nächsten Schritte."</div>
</div>

<!-- BLOCK 2 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 2</span>
    <span class="block-title">Überblick Admin-Ansicht</span>
    <span class="block-duration">2 Min.</span>
  </div>
  <div class="link-pill">Tab 1 → https://curasoft.ch/demo/admin</div>
  <div class="step">Dashboard: heutige Einsätze, Schnellzugriff</div>
  <div class="step">Navigation kurz durchfahren: Klienten, Einsätze, Rapporte, Touren, Rechnungen</div>
  <div class="quote">„Alles browserbasiert — kein Install, läuft auf PC, Tablet und Handy."</div>
</div>

<!-- BLOCK 3 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 3</span>
    <span class="block-title">Pflegedokumentation &amp; Pflegeplanung</span>
    <span class="block-duration">8 Min.</span>
  </div>
  <div class="block-frage">Ihre Frage: „NANDA/ATL/Diagnose-Ziel-Massnahmen habe ich nicht gefunden"</div>
  <div class="link-pill">Tab 2 → https://curasoft.ch/demo/admin?goto=/rapporte</div>
  <div class="step">Rapport-Typen zeigen: Pflege, Verlauf, Medikament, Zwischenfall, Information</div>
  <div class="step">Einen Verlaufsbericht öffnen → Diagnose/Ziel/Massnahmen im Freitext</div>
  <div class="quote">„Wir schreiben kein starres Schema vor. Ob NANDA, ATL oder problemorientiert — Sie strukturieren den Text selbst. Kein Formular-Korsett."</div>
  <div class="link-pill">Tab 3 → https://curasoft.ch/demo/admin?goto=/rapporte/create</div>
  <div class="step">Neuen Rapport: Typ, Klient, Datum wählen</div>
  <div class="step">KI-Demo: Stichworte eingeben → „Bericht generieren" → fertiger Text</div>
  <div class="substep">Beispiel: „Blutdruck erhöht, Klientin unruhig, Arzt informiert"</div>
  <div class="step">Mikrofon-Diktat erwähnen (Schweizerdeutsch, kein Tippen nötig)</div>
  <div class="step">Sammel-PDF: zurück Tab 2, Filter setzen → Knopfdruck → PDF für Arzt/KK</div>
  <div class="step">Vertraulich-Flag + automatische Admin-Benachrichtigung bei Zwischenfällen</div>
</div>

<!-- BLOCK 4 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 4</span>
    <span class="block-title">Abrechnung &amp; KK-Direktversand</span>
    <span class="block-duration">8 Min.</span>
  </div>
  <div class="block-frage">Ihre Frage: „Direktversand der Rechnungen an Gemeinden/KK habe ich nicht gefunden"</div>
  <div class="link-pill">Tab 4 → https://curasoft.ch/demo/admin?goto=/rechnungen/lauf</div>
  <div class="step">Rechnungslauf-Übersicht: Status, Betrag, Zeitraum — filterbar nach Klient &amp; Versicherungsart</div>
  <div class="step">Lauf öffnen → Rechnung öffnen → <strong>3. Seite zeigen</strong></div>
  <div class="quote">„Der Klient erhält die vollständige Rechnung. Mit der 3. Seite kann er alle Forderungen direkt bei Krankenkasse und Gemeinde einreichen — kein separates Formular, alles aufbereitet."</div>
  <div class="step">XML-Export (450.100) für elektronische KK-Einreichung ebenfalls dabei</div>
  <div class="step">Rapportblatt: automatisch beigelegt (gesetzliche Anforderung, vollständig integriert)</div>
  <div class="step">„E-Mail versenden" → PDF inkl. 3. Seite geht direkt an den Klienten</div>
</div>

<!-- BLOCK 5 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 5</span>
    <span class="block-title">Einsatzplanung &amp; Kalender</span>
    <span class="block-duration">4 Min.</span>
  </div>
  <div class="link-pill">Tab 5 → https://curasoft.ch/demo/admin?goto=/kalender</div>
  <div class="step">Wochenkalender: Einsätze farbig nach Mitarbeiter</div>
  <div class="step">Serien: einmal anlegen → läuft automatisch über Monate</div>
  <div class="step">„Nicht eingeplant"-Bereich: Admin sieht sofort Lücken, direkt zuweisen</div>
  <div class="step">Automatische Routenoptimierung (Touren) erwähnen</div>
</div>

<!-- BLOCK 6 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 6</span>
    <span class="block-title">Mobile Pflege-Ansicht</span>
    <span class="block-duration">4 Min.</span>
  </div>
  <div class="link-pill">Tab 6 → https://curasoft.ch/demo/pflege &nbsp;(bitte auf Smartphone öffnen!)</div>
  <div class="quote">„Schauen Sie das kurz auf Ihrem Handy an — öffnen Sie bitte: curasoft.ch/demo/pflege"</div>
  <div class="step">Heutige Einsätze der Pflegerin Sandra</div>
  <div class="step">Einsatz öffnen → Check-in, Leistungen erfassen, Check-out</div>
  <div class="step">Rapport aus dem Einsatz: Mikrofon → sprechen → KI-Text, sofort fertig</div>
  <div class="quote">„Kein App-Install, kein separates Login — einfach der Browser auf dem Smartphone."</div>
</div>

<!-- BLOCK 7 -->
<div class="block no-break">
  <div class="block-header">
    <span class="block-badge">Block 7</span>
    <span class="block-title">Kantone &amp; offene Fragen</span>
    <span class="block-duration">2 Min.</span>
  </div>
  <div class="block-frage">Ihre Frage: „Bei der Kantonwahl sind nur 5 Kantone wählbar"</div>
  <div class="quote">„Aktuell sind die häufigsten Kantone voreingestellt (AG, BE, ZH, ZG, LU). Weitere werden auf Anfrage innert 1–2 Tagen freigeschaltet — welche Kantone brauchen Sie?"</div>
  <div class="step">Notieren: <span style="border-bottom: 1px dotted #94a3b8; display: inline-block; width: 180px;">&nbsp;</span></div>
</div>

<!-- NÄCHSTE SCHRITTE -->
<div class="next-box no-break">
  <h2>Block 8 &mdash; Nächste Schritte (2 Min.)</h2>
  <div class="next-item">Welche Kantone nachschalten? <span class="next-blank"></span></div>
  <div class="next-item">Altsystem vorhanden? Datenmigration möglich</div>
  <div class="next-item">Pilotbetrieb: Zeitplan, Anzahl Mitarbeitende + Klienten? <span class="next-blank"></span></div>
  <div class="next-item">Preismodell besprechen</div>
  <div class="quote" style="margin-left: 0; margin-top: 6px;">„Wir können Ihnen eine Testumgebung mit Ihren eigenen Daten einrichten — dann sehen Sie das System im Kontext Ihrer Organisation."</div>
</div>

<!-- ZEITPUFFER -->
<div class="puffer-box no-break">
  <h2>Zeitpuffer — falls Zeit übrig</h2>
  <div class="puffer-item">Klienten-Detail: Dokumente, Arzt, KK, Einsatzhistorie</div>
  <div class="puffer-item">Audit-Log / Berechtigungsrollen (admin, pflege, buchhaltung)</div>
  <div class="puffer-item">Passwortloser Login (Passkey / Magic Link)</div>
</div>

<div class="footer">CuraSoft &mdash; www.itjob.ch &mdash; {{ date('d.m.Y') }}</div>

</body>
</html>
