import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Leaflet Marker-Icon Fix (Webpack/Vite)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl:       new URL('leaflet/dist/images/marker-icon.png',    import.meta.url).href,
    shadowUrl:     new URL('leaflet/dist/images/marker-shadow.png',  import.meta.url).href,
});

window.TourenkarteInit = function(einsaetze) {
    if (!einsaetze.length) return;

    const karte = L.map('tourenkarte').setView(
        [einsaetze[0].lat, einsaetze[0].lng], 13
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(karte);

    const koordinaten = [];

    einsaetze.forEach(function(e, i) {
        // Nummerierter Marker
        const icon = L.divIcon({
            className: '',
            html: `<div style="
                background: ${e.status === 'abgeschlossen' ? '#16a34a' : e.status === 'aktiv' ? '#d97706' : '#2563eb'};
                color: white; border-radius: 50%; width: 28px; height: 28px;
                display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: 13px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                border: 2px solid white;
            ">${i + 1}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });

        const marker = L.marker([e.lat, e.lng], { icon }).addTo(karte);
        marker.bindPopup(`
            <strong>${i + 1}. ${e.klient_name}</strong><br>
            ${e.adresse ?? ''}<br>
            ${e.zeit_von ? e.zeit_von + ' – ' + e.zeit_bis : ''}
        `);

        koordinaten.push([e.lat, e.lng]);
    });

    // Route als Linie zeichnen
    if (koordinaten.length > 1) {
        L.polyline(koordinaten, { color: '#2563eb', weight: 3, opacity: 0.6 }).addTo(karte);
    }

    // Karte auf alle Marker zoomen
    karte.fitBounds(L.latLngBounds(koordinaten).pad(0.15));
};
