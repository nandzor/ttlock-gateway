// Toast notification system
window.toast = {
    show(message, type = 'success', duration = 3000) {
        const colors = {
            success: 'bg-gradient-to-r from-green-500 to-green-600',
            error: 'bg-gradient-to-r from-red-500 to-red-600',
            warning: 'bg-gradient-to-r from-yellow-500 to-yellow-600',
            info: 'bg-gradient-to-r from-blue-500 to-blue-600',
        };

        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        };

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center space-x-3 transform transition-all duration-300 ease-out`;
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        
        toast.innerHTML = `
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type]}
            </svg>
            <span class="font-medium">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 hover:opacity-75 transition-opacity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
        
        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(message, duration) {
        this.show(message, 'success', duration);
    },

    error(message, duration) {
        this.show(message, 'error', duration);
    },

    warning(message, duration) {
        this.show(message, 'warning', duration);
    },

    info(message, duration) {
        this.show(message, 'info', duration);
    }
};

// Auto show toasts from session
document.addEventListener('DOMContentLoaded', () => {
    const successMsg = document.querySelector('[data-toast-success]');
    const errorMsg = document.querySelector('[data-toast-error]');
    
    if (successMsg) {
        toast.success(successMsg.getAttribute('data-toast-success'));
    }
    if (errorMsg) {
        toast.error(errorMsg.getAttribute('data-toast-error'));
    }
});

