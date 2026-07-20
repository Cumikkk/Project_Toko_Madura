// Nama cache dan versi
const CACHE_NAME = 'rrfx-v3';

// App shell minimal + offline fallback
const URLS_TO_CACHE = [
  '/offline.html'
];

// Install: cache app shell
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(URLS_TO_CACHE))
  );
  self.skipWaiting();
});

// Activate: bersihkan cache lama
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((names) =>
      Promise.all(names.map((name) => (name !== CACHE_NAME ? caches.delete(name) : undefined)))
    )
  );
  return self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Hanya GET
  if (req.method !== 'GET') return;

  // 1) Bypass untuk range requests (video/audio/PDF, dsb.)
  if (req.headers.has('range')) {
    event.respondWith(fetch(req));
    return;
  }

  // 2) (Opsional) Bypass untuk media besar agar tak bikin 206
  const dest = req.destination; // 'document', 'script', 'style', 'image', 'font', 'video', 'audio', 'iframe', 'worker'
  if (dest === 'video' || dest === 'audio') {
    event.respondWith(fetch(req).catch(() => caches.match(req)));
    return;
  }

  // Network First dengan fallback ke cache dan offline.html untuk navigasi
  event.respondWith(
    fetch(req)
      .then((netRes) => {
        // Jangan cache jika bukan 200 OK, jika parsial, atau kalau response 'opaque'
        const isCacheable =
          netRes &&
          netRes.ok &&
          netRes.status === 200 &&
          netRes.type === 'basic' &&
          !netRes.headers.has('Content-Range'); // tanda response parsial

        if (isCacheable) {
          const resClone = netRes.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(req, resClone).catch(() => {
              // diamkan saja jika gagal (mis. quota)
            });
          });
        }
        return netRes;
      })
      .catch(async () => {
        // Offline: coba cache dulu
        const cached = await caches.match(req);
        if (cached) return cached;

        // Jika ini navigation request (buka halaman), pakai offline.html
        if (req.mode === 'navigate') {
          const offline = await caches.match('/offline.html');
          if (offline) return offline;
        }

        // Fallback terakhir
        return new Response('Offline', { status: 503, statusText: 'Offline' });
      })
  );
});
