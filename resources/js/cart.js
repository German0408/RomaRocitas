// Cart persistence utility
class CartPersistence {
    constructor() {
        this.storageKey = 'cart_data';
        this.init();
    }

    init() {
        // Load cart from localStorage on page load
        this.loadFromLocalStorage();

        // Save to localStorage before page unload
        window.addEventListener('beforeunload', () => {
            this.saveToLocalStorage();
        });

        // Sync with server periodically
        setInterval(() => {
            this.syncWithServer();
        }, 30000); // Every 30 seconds
    }

    loadFromLocalStorage() {
        try {
            const cartData = localStorage.getItem(this.storageKey);
            if (cartData) {
                const cart = JSON.parse(cartData);
                // Send to server if user is logged in
                if (window.Laravel && window.Laravel.user) {
                    this.syncCartWithServer(cart);
                }
            }
        } catch (error) {
            console.error('Error loading cart from localStorage:', error);
        }
    }

    saveToLocalStorage() {
        try {
            // Get current cart data from Livewire components
            const cartComponents = document.querySelectorAll('[wire\\:id]');
            cartComponents.forEach(component => {
                const componentId = component.getAttribute('wire:id');
                if (componentId && window.livewire.components[componentId]) {
                    const componentData = window.livewire.components[componentId].data;
                    if (componentData.items) {
                        localStorage.setItem(this.storageKey, JSON.stringify(componentData.items));
                        return;
                    }
                }
            });
        } catch (error) {
            console.error('Error saving cart to localStorage:', error);
        }
    }

    syncWithServer() {
        // This would typically make an AJAX call to sync cart
        // For now, just ensure localStorage is up to date
        this.saveToLocalStorage();
    }

    syncCartWithServer(cart) {
        // Make AJAX request to merge localStorage cart with server cart
        fetch('/cart/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ cart: cart })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear localStorage after successful sync
                localStorage.removeItem(this.storageKey);
                // Refresh cart components
                window.livewire.rescan();
            }
        })
        .catch(error => {
            console.error('Error syncing cart with server:', error);
        });
    }

    clearLocalStorage() {
        localStorage.removeItem(this.storageKey);
    }
}

// Initialize cart persistence when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new CartPersistence();
});

// Also initialize on Livewire updates
document.addEventListener('livewire:loaded', () => {
    new CartPersistence();
});