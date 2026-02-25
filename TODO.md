# TODO

---

## PHASE 1 — Laufende Entwicklung (aktuelle Module)

### ngrok PWA-Test auf Handy (ausstehend)

ngrok ist installiert, aber noch kein Auth-Token gesetzt.

#### Schritte:
1. Kostenlosen Account erstellen: https://dashboard.ngrok.com/signup
2. Auth-Token holen: https://dashboard.ngrok.com/get-started/your-authtoken
3. Token eintragen:
   ```
   powershell -Command "& 'C:/Users/41793/AppData/Local/Microsoft/WinGet/Packages/Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe/ngrok.exe' config add-authtoken DEIN_TOKEN"
   ```
4. Tunnel starten:
   ```
   powershell -Command "& 'C:/Users/41793/AppData/Local/Microsoft/WinGet/Packages/Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe/ngrok.exe' http --host-header=spitex.test 80"
   ```
5. URL vom Terminal (https://xxxx.ngrok-free.app) auf dem Handy öffnen
6. PWA testen: Homescreen-Installation, Offline-Modus, Check-in/out

### Offene Module (aus CLAUDE.md "Bekannte offene Punkte")

- [ ] PDF-Druck Rechnungen (Button vorhanden, aber `disabled`)
- [ ] MediData-Schnittstelle (auf Landing Page als "in Entwicklung" markiert)
- [ ] EPD Elektronisches Patientendossier (Pflicht ab 2026)
- [ ] Security Paket B: Audit-Log (wer hat was wann geändert)
- [ ] Security Paket C: 2FA (TOTP) als zweiter Faktor
- [ ] Leistungserfassung → Abrechnung (Minuten aus Checkliste → Leistungsart → Rechnung)
- [ ] Wiederkehrende Einsätze: Serie bearbeiten (alle verschieben)

---

## PHASE 2 — Multi-Tenant SaaS-Betrieb (ERST NACH PHASE 1 — alle Module fertig)

> **Zurückgestellt bis alle Module aus Phase 1 abgeschlossen sind.**
> Architektur-Entscheid und Konzept: siehe CLAUDE.md → "Multi-Tenant Architektur"

### Was zu bauen ist

- [ ] Wildcard DNS `*.curasoft.ch` beim Provider (devitjob.ch) einrichten
- [ ] Master-DB `curasoft_master` anlegen (Tabelle `tenants`: subdomain → db_name/user/pw)
- [ ] `TenantMiddleware` — liest Subdomain, schlägt in Master-DB nach, setzt DB-Connection
- [ ] `Tenant` Model + Migration für Master-DB
- [ ] Artisan-Command `tenant:create {subdomain} {name}` — legt DB an, migriert, befüllt Seeders, trägt in Master-DB ein
- [ ] Artisan-Command `tenant:migrate` — führt Migrations auf allen aktiven Tenant-DBs aus
- [ ] Login-Seite zeigt Firmenname/Logo aus jeweiliger Subdomain-DB
- [ ] `www.curasoft.ch` umbau: Landing Page (kein direkter Login mehr auf Root-Domain)
- [ ] `demo.curasoft.ch` als eigene Demo-Instanz (ersetzt aktuelles `www.curasoft.ch`)

### Reihenfolge
1. Wildcard DNS klären (Provider-Gespräch)
2. Master-DB + TenantMiddleware (Herzstück)
3. `tenant:create` Command
4. `tenant:migrate` Command
5. Landing Page + Demo-Umbau
