// Import OneSignal service worker
importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');

// Add message event handler on initial evaluation
self.addEventListener('message', function(event) {
    console.log('Service Worker Updater received message:', event.data);
});
