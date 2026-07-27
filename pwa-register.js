(() => {
  if (!("serviceWorker" in navigator)) return;
  window.addEventListener("load", async () => {
    try {
      await navigator.serviceWorker.register("/AURA/service-worker.js", { scope: "/AURA/" });
      console.info("AURA service worker registered");
    } catch (error) {
      console.error("AURA service worker registration failed", error);
    }
  });
})();
