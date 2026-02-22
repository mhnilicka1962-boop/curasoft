const CACHE = 'curasoft-v1';
const OFFLINE_PAGES = ['/dashboard', '/einsaetze', '/klienten'];

// Install: Cache wichtige Seiten
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(cache =>
            cache.addAll(['/offline.html', ...OFFLINE_PAGES]).catch(() => {})
        )
    );
    self.skipWaiting();
});

// Activate: Alten Cache löschen
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: Network-first für API-Calls, Cache-first für Assets
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Nur GET, nur gleiche Origin
    if (e.request.method !== 'GET' || url.origin !== location.origin) return;

    // CSS/JS/Bilder: Cache-first
    if (url.pathname.startsWith('/build/') || url.pathname.match(/\.(svg|png|ico|woff2?)$/)) {
        e.respondWith(
            caches.match(e.request).then(r => r || fetch(e.request).then(res => {
                const clone = res.clone();
                caches.open(CACHE).then(c => c.put(e.request, clone));
                return res;
            }))
        );
        return;
    }

    // Seiten: Network-first, Offline-Fallback
    e.respondWith(
        fetch(e.request)
            .then(res => {
                if (res.ok && e.request.headers.get('accept')?.includes('text/html')) {
                    caches.open(CACHE).then(c => c.put(e.request, res.clone()));
                }
                return res;
            })
            .catch(() => caches.match(e.request).then(r => r || caches.match('/offline.html')))
    );
});

// Background Sync: Offline Check-ins senden
self.addEventListener('sync', e => {
    if (e.tag === 'checkin-sync') {
        e.waitUntil(syncCheckins());
    }
});

async function syncCheckins() {
    const db = await openCheckinDB();
    const tx = db.transaction('queue', 'readonly');
    const entries = await getAllFromStore(tx.objectStore('queue'));

    for (const entry of entries) {
        try {
            const res = await fetch(entry.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': entry.csrf },
                body: JSON.stringify(entry.data),
            });
            if (res.ok) {
                const delTx = db.transaction('queue', 'readwrite');
                delTx.objectStore('queue').delete(entry.id);
                self.clients.matchAll().then(clients =>
                    clients.forEach(c => c.postMessage({ type: 'CHECKIN_SYNCED' }))
                );
            }
        } catch {}
    }
}

function openCheckinDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('checkin-queue', 1);
        req.onupgradeneeded = e => e.target.result.createObjectStore('queue', { keyPath: 'id', autoIncrement: true });
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = reject;
    });
}

function getAllFromStore(store) {
    return new Promise((resolve, reject) => {
        const req = store.getAll();
        req.onsuccess = e => resolve(e.target.result);
        req.onerror = reject;
    });
}
