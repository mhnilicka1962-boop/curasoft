# TODO

## ngrok PWA-Test auf Handy (ausstehend)

ngrok ist installiert, aber noch kein Auth-Token gesetzt.

### Schritte:
1. Kostenlosen Account erstellen: https://dashboard.ngrok.com/signup
2. Auth-Token holen: https://dashboard.ngrok.com/get-started/your-authtoken
3. Token eintragen:
   ```
   powershell -Command "& 'C:/Users/41793/AppData/Local/Microsoft/WinGet/Packages/Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe/ngrok.exe' config add-authtoken DEIN_TOKEN"
   ```
4. Tunnel starten:
   ```
   powershell -Command "& 'C:/Users/41793/AppData/Local/Microsoft/WinGet/Packages/Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe/ngrok.exe' http --host-header=curasoft.test 80"
   ```
5. URL vom Terminal (https://xxxx.ngrok-free.app) auf dem Handy öffnen
6. PWA testen: Homescreen-Installation, Offline-Modus, Check-in/out
