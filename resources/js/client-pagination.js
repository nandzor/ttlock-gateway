/**
 * Reusable Client-Side Pagination Alpine.js Component
 * Usage: x-data="clientPagination(config)"
 */
function clientPagination(config = {}) {
  return {
    // Configuration with defaults
    items: config.items || [],
    perPageOptions: config.perPageOptions || [5, 10, 20, 50],
    defaultPerPage: config.defaultPerPage || 10,
    maxVisiblePages: config.maxVisiblePages || 5,
    itemName: config.itemName || 'items',
    emptyMessage: config.emptyMessage || 'No items found',
    scrollToTop: config.scrollToTop !== false,
    storageKey: config.storageKey || 'client_pagination_per_page',

    // Pagination state
    currentPage: 1,
    perPage: config.defaultPerPage || 10,

    // Computed properties
    get totalItems() {
      return this.items.length;
    },

    get totalPages() {
      return Math.ceil(this.totalItems / this.perPage);
    },

    get startItem() {
      return this.totalItems === 0 ? 0 : ((this.currentPage - 1) * this.perPage) + 1;
    },

    get endItem() {
      return Math.min(this.currentPage * this.perPage, this.totalItems);
    },

    get paginatedItems() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.items.slice(start, end);
    },

    get visiblePages() {
      const pages = [];
      let start = Math.max(1, this.currentPage - Math.floor(this.maxVisiblePages / 2));
      let end = Math.min(this.totalPages, start + this.maxVisiblePages - 1);

      // Adjust start if we're near the end
      if (end - start + 1 < this.maxVisiblePages) {
        start = Math.max(1, end - this.maxVisiblePages + 1);
      }

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      return pages;
    },

    get isEmpty() {
      return this.totalItems === 0;
    },

    // Methods
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;

        // Scroll to top if enabled
        if (this.scrollToTop) {
          this.$nextTick(() => {
            const table = this.$el.querySelector('table');
            if (table) {
              table.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
              // Fallback to scrolling to the component itself
              this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
          });
        }
      }
    },

    updateItems(newItems) {
      this.items = newItems;
      // Reset to first page if current page is beyond available pages
      if (this.currentPage > this.totalPages && this.totalPages > 0) {
        this.currentPage = 1;
      }
    },

    refresh() {
      // Force reactivity update
      this.$nextTick(() => {
        // This will trigger reactivity
        this.items = [...this.items];
      });
    },

    // Utility methods
    formatTime(timestamp) {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleTimeString('en-US', {
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    },

    formatDate(timestamp) {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    formatDateTime(timestamp) {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      });
    },

    // Initialize
    init() {
      // Set default per page from URL parameter or localStorage
      const urlParams = new URLSearchParams(window.location.search);
      const perPageParam = urlParams.get('per_page');
      if (perPageParam && this.perPageOptions.includes(parseInt(perPageParam))) {
        this.perPage = parseInt(perPageParam);
      } else {
        const savedPerPage = localStorage.getItem(this.storageKey);
        if (savedPerPage && this.perPageOptions.includes(parseInt(savedPerPage))) {
          this.perPage = parseInt(savedPerPage);
        } else {
          // Clear invalid localStorage value and use default
          localStorage.removeItem(this.storageKey);
          this.perPage = this.defaultPerPage;
        }
      }

      // Save per page preference
      this.$watch('perPage', (value) => {
        localStorage.setItem(this.storageKey, value.toString());
      });

      // Watch for items changes
      this.$watch('items', () => {
        // Reset to first page if current page is beyond available pages
        if (this.currentPage > this.totalPages && this.totalPages > 0) {
          this.currentPage = 1;
        }
      });
    }
  }
}

// Make it globally available
window.clientPagination = clientPagination;
