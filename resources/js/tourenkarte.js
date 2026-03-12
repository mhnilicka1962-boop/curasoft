import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl:       new URL('leaflet/dist/images/marker-icon.png',    import.meta.url).href,
    shadowUrl:     new URL('leaflet/dist/images/marker-shadow.png',  import.meta.url).href,
});

let _karte = null;
let _layer = null;

function markerHtml(nr, status) {
    const farbe = status === 'abgeschlossen' ? '#16a34a' : status === 'aktiv' ? '#d97706' : '#2563eb';
    return `<div style="background:${farbe};color:white;border-radius:50%;width:28px;height:28px;
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:13px;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid white;">${nr}</div>`;
}

function zeichneKarte(punkte, warnSegmente) {
    if (_layer) _layer.clearLayers();
    else { _layer = L.layerGroup(); _layer.addTo(_karte); }

    const koords = [];

    punkte.forEach(function(e, i) {
        const icon = L.divIcon({
            className: '',
            html: markerHtml(i + 1, e.status),
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
        const marker = L.marker([e.lat, e.lng], { icon }).addTo(_layer);
        marker.bindPopup(`<strong>${i + 1}. ${e.klient_name}</strong><br>${e.adresse ?? ''}<br>${e.zeit_von ? e.zeit_von + ' – ' + (e.zeit_bis ?? '') : ''}`);
        koords.push([e.lat, e.lng]);
    });

    for (let i = 0; i < koords.length - 1; i++) {
        const warnung = warnSegmente && warnSegmente[i];
        L.polyline([koords[i], koords[i + 1]], {
            color:     warnung ? '#dc2626' : '#2563eb',
            weight:    3,
            opacity:   0.7,
            dashArray: warnung ? '6 4' : null,
        }).addTo(_layer);
    }

    if (koords.length >= 1) {
        _karte.fitBounds(L.latLngBounds(koords).pad(0.15));
    }
}

function haversineM(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

window.berechneWarnungen = function(punkte) {
    return punkte.slice(0, -1).map((p, i) => {
        const next = punkte[i + 1];
        if (!p.lat || !next.lat || !p.zeit_bis || !next.zeit_von) return false;
        const [h1, m1] = p.zeit_bis.split(':').map(Number);
        const [h2, m2] = next.zeit_von.split(':').map(Number);
        const verfuegbar = (h2 * 60 + m2) - (h1 * 60 + m1);
        const fahrtMin   = Math.ceil(haversineM(p.lat, p.lng, next.lat, next.lng) / 1000 / 25 * 60) + 2;
        return verfuegbar < fahrtMin;
    });
};

window.TourenkarteInit = function(einsaetze) {
    if (!einsaetze.length) return;

    _karte = L.map('tourenkarte').setView([einsaetze[0].lat, einsaetze[0].lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(_karte);

    zeichneKarte(einsaetze, window.berechneWarnungen(einsaetze));
};

window.TourenkarteUpdate = function(punkte, warnSegmente) {
    if (!_karte) return;
    zeichneKarte(punkte, warnSegmente);
};
