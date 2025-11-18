import "./bootstrap";
import Alpine from "alpinejs";

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Alpine.js stores for global state management
document.addEventListener("alpine:init", () => {
    // Search store
    Alpine.store("search", {
        query: "",
        results: 0,
        total: 0,

        updateResults(results, total) {
            this.results = results;
            this.total = total;
        },

        clear() {
            this.query = "";
        },
    });

    // Tree explorer store
    Alpine.store("tree", {
        expanded: new Set(),

        toggle(id) {
            if (this.expanded.has(id)) {
                this.expanded.delete(id);
            } else {
                this.expanded.add(id);
            }
        },

        isExpanded(id) {
            return this.expanded.has(id);
        },

        expandAll() {
            // Will be populated with IDs dynamically
        },

        collapseAll() {
            this.expanded.clear();
        },
    });

    // Toast notification store
    Alpine.store("toast", {
        messages: [],

        show(message, type = "info", duration = 3000) {
            const id = Date.now();
            this.messages.push({ id, message, type });

            setTimeout(() => {
                this.remove(id);
            }, duration);
        },

        remove(id) {
            this.messages = this.messages.filter((m) => m.id !== id);
        },
    });

    // Edit Jabatan store
    Alpine.store("editJabatan", null);

    // Edit ASN store
    Alpine.store("editAsn", null);
});

// Alpine.js components
document.addEventListener("DOMContentLoaded", function () {
    // Smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll(".alert-dismissible").forEach((alert) => {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});

// Utility functions
window.utils = {
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    formatNumber(num) {
        return new Intl.NumberFormat("id-ID").format(num);
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Alpine.store("toast").show(
                "Berhasil disalin ke clipboard",
                "success",
            );
        });
    },
};
