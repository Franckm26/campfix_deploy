/**
 * CampFix Push Notifications
 * Handles browser push notification subscriptions
 */

const PushNotifications = {
    // Replace this with your actual VAPID public key from .env
    vapidPublicKey: null,
    
    /**
     * Initialize push notifications
     */
    async init(vapidPublicKey) {
        this.vapidPublicKey = vapidPublicKey;
        
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            console.warn('This browser does not support notifications');
            return false;
        }

        // Check if service workers are supported
        if (!('serviceWorker' in navigator)) {
            console.warn('This browser does not support service workers');
            return false;
        }

        // Check if push is supported
        if (!('PushManager' in window)) {
            console.warn('This browser does not support push notifications');
            return false;
        }

        // Register service worker
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('Service Worker registered:', registration);
            return true;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            return false;
        }
    },

    /**
     * Request notification permission
     */
    async requestPermission() {
        const permission = await Notification.requestPermission();
        console.log('Notification permission:', permission);
        return permission === 'granted';
    },

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;
            
            // Check if already subscribed
            let subscription = await registration.pushManager.getSubscription();
            
            if (!subscription) {
                // Convert VAPID key
                const convertedVapidKey = this.urlBase64ToUint8Array(this.vapidPublicKey);
                
                // Subscribe
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            console.log('Push subscription:', subscription);

            // Send subscription to server
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(subscription.toJSON())
            });

            const data = await response.json();
            
            if (data.success) {
                console.log('Successfully subscribed to push notifications');
                return true;
            } else {
                console.error('Failed to subscribe:', data);
                return false;
            }
        } catch (error) {
            console.error('Error subscribing to push notifications:', error);
            return false;
        }
    },

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            
            if (subscription) {
                // Unsubscribe from browser
                await subscription.unsubscribe();
                
                // Tell server
                const response = await fetch('/api/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint })
                });

                const data = await response.json();
                console.log('Unsubscribed from push notifications:', data);
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Error unsubscribing from push notifications:', error);
            return false;
        }
    },

    /**
     * Check if user is subscribed
     */
    async isSubscribed() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            return subscription !== null;
        } catch (error) {
            console.error('Error checking subscription status:', error);
            return false;
        }
    },

    /**
     * Convert VAPID key from base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
};

// Make it globally available
window.PushNotifications = PushNotifications;
