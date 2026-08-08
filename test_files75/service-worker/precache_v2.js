// offline fallback — typical precache list from a PWA
const PRECACHE = "precache-v3";
self.addEventListener("install", (e) => {
  e.waitUntil(
    caches.open(PRECACHE).then((c) => c.addAll([
      "/assets/chunk-7a1k.css",
      "/assets/bundle.app.js"
    ]))
  );
  self.skipWaiting();
});
self.addEventListener("activate", (e) => e.waitUntil(self.clients.claim()));
self.addEventListener("fetch", (e) => {
  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
