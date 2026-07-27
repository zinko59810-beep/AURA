const CACHE_NAME = "aura-static-v1";
const STATIC_ASSETS = [
  "/AURA/offline.html",
  "/AURA/manifest.webmanifest",
  "/AURA/01_front/assets/icons/icon-192.png",
  "/AURA/01_front/assets/icons/icon-512.png",
  "/AURA/01_front/assets/icons/apple-touch-icon.png",
  "/AURA/01_front/assets/icons/favicon-32.png",
  "/AURA/01_front/css/style.css"
];

self.addEventListener("install", event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS)));
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
  );
  self.clients.claim();
});

self.addEventListener("fetch", event => {
  const request = event.request;
  if (request.method !== "GET") return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  if (request.mode === "navigate") {
    event.respondWith(fetch(request).catch(() => caches.match("/AURA/offline.html")));
    return;
  }

  if (["style", "script", "image", "font"].includes(request.destination)) {
    event.respondWith(
      caches.match(request).then(cached => cached || fetch(request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
        return response;
      }))
    );
  }
});
