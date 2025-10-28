// Format tanggal
export function formatDate(date, format = 'Y-m-d H:i:s') {
    if (!date) return null;
    const d = new Date(date);
    
    const map = {
        'Y': d.getFullYear(),
        'm': String(d.getMonth() + 1).padStart(2, '0'),
        'd': String(d.getDate()).padStart(2, '0'),
        'H': String(d.getHours()).padStart(2, '0'),
        'i': String(d.getMinutes()).padStart(2, '0'),
        's': String(d.getSeconds()).padStart(2, '0')
    };
    
    return format.replace(/Y|m|d|H|i|s/g, match => map[match]);
}

// Format tanggal Indonesia
export function formatDateId(date) {
    if (!date) return null;
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hour = String(d.getHours()).padStart(2, '0');
    const minute = String(d.getMinutes()).padStart(2, '0');
    
    return `${day}-${month}-${year} ${hour}:${minute}`;
}

// Format rupiah
export function rupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Format file size
export function formatFileSize(bytes, precision = 2) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    
    while (bytes > 1024 && i < units.length - 1) {
        bytes /= 1024;
        i++;
    }
    
    return bytes.toFixed(precision) + ' ' + units[i];
}

// Truncate text
export function truncate(text, length = 100, suffix = '...') {
    if (!text || text.length <= length) return text;
    return text.substring(0, length) + suffix;
}

// Get initials
export function getInitials(name) {
    if (!name) return '';
    const words = name.trim().split(' ');
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

// Time ago
export function timeAgo(datetime) {
    const timestamp = new Date(datetime).getTime();
    const now = Date.now();
    const difference = Math.floor((now - timestamp) / 1000);
    
    const periods = [
        { name: 'tahun', seconds: 31536000 },
        { name: 'bulan', seconds: 2592000 },
        { name: 'minggu', seconds: 604800 },
        { name: 'hari', seconds: 86400 },
        { name: 'jam', seconds: 3600 },
        { name: 'menit', seconds: 60 },
        { name: 'detik', seconds: 1 }
    ];
    
    for (const period of periods) {
        if (difference >= period.seconds) {
            const count = Math.floor(difference / period.seconds);
            return `${count} ${period.name} yang lalu`;
        }
    }
    
    return 'baru saja';
}

// Generate random color
export function randomColor() {
    return '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0');
}

// Copy to clipboard
export function copyToClipboard(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
    }
    
    // Fallback
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    return Promise.resolve();
}

// Debounce function
export function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
export function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Parse query string
export function parseQuery(queryString) {
    const query = {};
    const pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
    
    for (const pair of pairs) {
        const [key, value] = pair.split('=');
        query[decodeURIComponent(key)] = decodeURIComponent(value || '');
    }
    
    return query;
}

// Build query string
export function buildQuery(params) {
    return Object.keys(params)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
        .join('&');
}

// Validate email
export function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Validate phone
export function isValidPhone(phone) {
    return /^[0-9]{10,15}$/.test(phone.replace(/[^0-9]/g, ''));
}

// Clean phone number
export function cleanPhone(phone) {
    return phone.replace(/[^0-9]/g, '');
}

// Percentage
export function percentage(part, total, precision = 2) {
    if (total === 0) return 0;
    return ((part / total) * 100).toFixed(precision);
}

// Show toast notification (requires Tailwind)
export function showToast(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Storage helpers
export const storage = {
    set(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    },
    get(key, defaultValue = null) {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : defaultValue;
    },
    remove(key) {
        localStorage.removeItem(key);
    },
    clear() {
        localStorage.clear();
    }
};

// HTTP helper
export async function http(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const token = storage.get('token');
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }
    
    const response = await fetch(url, { ...defaultOptions, ...options });
    const data = await response.json();
    
    if (!response.ok) {
        throw new Error(data.message || 'Request failed');
    }
    
    return data;
}

