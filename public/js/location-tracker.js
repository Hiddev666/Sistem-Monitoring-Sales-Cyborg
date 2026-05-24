/**
 * Location Tracker for Sales Devices
 * Tracks GPS location every 30 seconds and sends to server
 *
 * Phase 5: Dashboard & Monitoring
 */

class LocationTracker {
    constructor() {
        this.trackingInterval = null;
        this.lastPosition = null;
        this.isTracking = false;
    }

    /**
     * Start tracking location
     */
    startTracking() {
        if (this.isTracking) {
            console.warn('Location tracking already started');
            return;
        }

        this.isTracking = true;

        // Track location every 30 seconds (as per PRD requirement)
        this.trackingInterval = setInterval(() => {
            this.captureAndSend();
        }, 30000);

        // Capture immediately
        this.captureAndSend();

        console.log('Location tracking started');
    }

    /**
     * Stop tracking location
     */
    stopTracking() {
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }

        this.isTracking = false;
        console.log('Location tracking stopped');
    }

    /**
     * Capture GPS position and send to server
     */
    async captureAndSend() {
        if (!navigator.geolocation) {
            console.error('Geolocation is not supported by this browser');
            return;
        }

        try {
            const position = await this.getCurrentPosition();
            await this.sendLocation(position);
            this.lastPosition = position;
            console.log('Location sent successfully');
        } catch (error) {
            console.error('Error capturing or sending location:', error);
        }
    }

    /**
     * Get current GPS position
     */
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => resolve(position),
                (error) => reject(error),
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,  // Always fresh location
                    timeout: 10000   // 10 second timeout
                }
            );
        });
    }

    /**
     * Send location data to server
     */
    async sendLocation(position) {
        const data = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            recorded_at: new Date().toISOString()
        };

        try {
            const response = await fetch('/api/location/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Failed to send location:', error);
            throw error;
        }
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Check if tracking is active
     */
    isActive() {
        return this.isTracking;
    }

    /**
     * Get last captured position
     */
    getLastPosition() {
        return this.lastPosition;
    }
}

// Initialize tracker when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if this page should track location
    const shouldTrack = document.body.dataset.isTracking === 'true';

    if (shouldTrack) {
        // Create global tracker instance
        window.locationTracker = new LocationTracker();

        // Auto-start tracking
        window.locationTracker.startTracking();

        // Stop tracking when user logs out or leaves page
        window.addEventListener('beforeunload', () => {
            window.locationTracker.stopTracking();
        });
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocationTracker;
}
