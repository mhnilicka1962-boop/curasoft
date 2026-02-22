/**
 * CuraSoft Service Worker
 * - Cacht statische Assets (CSS/JS) und besuchte Seiten
 * - Offline-Fallback für Navigation
 * - Queued Check-in/out: schlägt das Netz fehl → lokal speichern, bei Reconnect senden
 */

const CACHE_VERSION  = 'v1';
const STATIC_CACHE   = `curasoft-static-${CACHE_VERSION}`;
const PAGE_CACHE     = `curasoft-pages-${CACHE_VERSION}`;
const OFFLINE_URL    = '/offline.html';

// URL-Muster für Check-in / Check-out POSTs
const CHECKIN_PATTERNS = [
    /^\/checkin\/[^/]+(\/gps|\/manuell|\/anonym)?$/,
    /^\/checkout\/\d+(\/gps|\/manuell)$/,
];

// ─── Install ─────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll([OFFLINE_URL, '/icon-192.svg', '/icon-512.svg', '/manifest.json']))
            .then(() => self.skipWaiting())
    );
});

// ─── Activate ────────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(k => k !== STATIC_CACHE && k !== PAGE_CACHE)
                    .map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // Nur Same-Origin
    if (url.origin !== self.location.origin) return;

    // Check-in / Check-out POSTs → offline-queue
    if (req.method === 'POST' && CHECKIN_PATTERNS.some(p => p.test(url.pathname))) {
        event.respondWith(handleCheckinPost(req));
        return;
    }

    // Vite-Build-Assets (gehashte Dateinamen → unveränderlich → cache-first)
    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(req, STATIC_CACHE));
        return;
    }

    // Statische PWA-Dateien
    if (['/manifest.json', '/icon-192.svg', '/icon-512.svg'].includes(url.pathname)) {
        event.respondWith(cacheFirst(req, STATIC_CACHE));
        return;
    }

    // Navigation (HTML-Seiten) → network-first, Fallback auf Cache, dann Offline-Seite
    if (req.mode === 'navigate') {
        event.respondWith(networkFirst(req));
        return;
    }
});

// ─── Background Sync ──────────────────────────────────────────────────────────
self.addEventListener('sync', event => {
    if (event.tag === 'checkin-queue') {
        event.waitUntil(replayQueuedCheckins());
    }
});

// ─── Strategien ───────────────────────────────────────────────────────────────
async function cacheFirst(req, cacheName) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(req);
    if (cached) return cached;

    try {
        const response = await fetch(req);
        if (response.ok) cache.put(req, response.clone());
        return response;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}

async function networkFirst(req) {
    const cache = await caches.open(PAGE_CACHE);
    try {
        const response = await fetch(req);
        if (response.ok) cache.put(req, response.clone());
        return response;
    } catch {
        const cached = await cache.match(req);
        if (cached) return cached;
        const offline = await caches.match(OFFLINE_URL);
        return offline || new Response('Offline', { status: 503 });
    }
}

async function handleCheckinPost(req) {
    try {
        return await fetch(req.clone());
    } catch {
        // Netz nicht verfügbar → in IndexedDB speichern
        const body    = await req.clone().text();
        const headers = {};
        req.headers.forEach((val, key) => { headers[key] = val; });
        await dbQueueAdd({ url: req.url, method: req.method, body, headers, ts: Date.now() });

        // Background-Sync registrieren (wenn Browser unterstützt)
        try { await self.registration.sync.register('checkin-queue'); } catch {}

        // Alle offenen Tabs informieren
        const clients = await self.clients.matchAll({ type: 'window' });
        clients.forEach(c => c.postMessage({ type: 'CHECKIN_QUEUED', url: req.url }));

        // Offline-Bestätigungsseite zurückgeben (navigiert nicht weg)
        return new Response(offlineCheckinHtml(), {
            headers: { 'Content-Type': 'text/html; charset=utf-8' }
        });
    }
}

async function replayQueuedCheckins() {
    const items = await dbQueueGetAll();
    for (const item of items) {
        try {
            const resp = await fetch(item.url, {
                method:      item.method,
                body:        item.body,
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'include',
            });
            // Auch bei Redirect (3xx) oder Client-Fehler (4xx) aus Queue entfernen
            // — nur bei Netzwerkfehler drinlassen
            if (resp.status < 500) {
                await dbQueueDelete(item.id);
                const clients = await self.clients.matchAll({ type: 'window' });
                clients.forEach(c => c.postMessage({ type: 'CHECKIN_SYNCED', url: item.url }));
            }
        } catch {
            // Immer noch offline — beim nächsten Sync erneut versuchen
        }
    }
}

// ─── IndexedDB Helpers ────────────────────────────────────────────────────────
function dbOpen() {
    return new Promise((res, rej) => {
        const req = indexedDB.open('curasoft-offline', 1);
        req.onupgradeneeded = e => e.target.result.createObjectStore('checkin-queue', { keyPath: 'id', autoIncrement: true });
        req.onsuccess = e => res(e.target.result);
        req.onerror   = e => rej(e.target.error);
    });
}

async function dbQueueAdd(data) {
    const db = await dbOpen();
    return new Promise((res, rej) => {
        const tx = db.transaction('checkin-queue', 'readwrite');
        tx.objectStore('checkin-queue').add(data);
        tx.oncomplete = res;
        tx.onerror    = e => rej(e.target.error);
    });
}

async function dbQueueGetAll() {
    const db = await dbOpen();
    return new Promise((res, rej) => {
        const req = db.transaction('checkin-queue', 'readonly').objectStore('checkin-queue').getAll();
        req.onsuccess = e => res(e.target.result);
        req.onerror   = e => rej(e.target.error);
    });
}

async function dbQueueDelete(id) {
    const db = await dbOpen();
    return new Promise((res, rej) => {
        const tx = db.transaction('checkin-queue', 'readwrite');
        tx.objectStore('checkin-queue').delete(id);
        tx.oncomplete = res;
        tx.onerror    = e => rej(e.target.error);
    });
}

// ─── Offline-Bestätigungsseite (bei gequeutem Check-in) ──────────────────────
function offlineCheckinHtml() {
    return `<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Offline gespeichert — CuraSoft</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:system-ui,sans-serif;background:#f0fdf4;min-height:100vh;
         display:flex;align-items:center;justify-content:center;padding:1.5rem}
    .box{background:#fff;border-radius:1rem;padding:2rem;text-align:center;
         max-width:380px;width:100%;box-shadow:0 4px 16px rgba(0,0,0,.08)}
    .icon{font-size:3rem;margin-bottom:1rem}
    h1{color:#166534;font-size:1.25rem;margin-bottom:.5rem}
    p{color:#6b7280;font-size:.9375rem;margin-bottom:1.25rem;line-height:1.5}
    .info{background:#dcfce7;color:#166534;border-radius:.5rem;padding:.75rem;
          font-size:.875rem;margin-bottom:1.5rem}
    a{background:#2563eb;color:#fff;text-decoration:none;padding:.75rem 1.5rem;
      border-radius:.5rem;font-size:.9375rem;font-weight:600;display:inline-block}
  </style>
  <script>setTimeout(()=>history.back(), 4000);</script>
</head>
<body>
  <div class="box">
    <div class="icon">✅</div>
    <h1>Check-in gespeichert</h1>
    <p>Keine Internetverbindung — der Check-in wurde lokal gespeichert.</p>
    <div class="info">Wird automatisch gesendet, sobald du wieder online bist.</div>
    <a href="javascript:history.back()">← Zurück</a>
  </div>
</body>
</html>`;
}
