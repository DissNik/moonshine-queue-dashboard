document.addEventListener('alpine:init', () => {
    Alpine.data('autoRefresh', (initialState = { autoRefresh: false, refreshInterval: 10000 }) => ({
        autoRefresh: initialState.autoRefresh,
        refreshInterval: initialState.refreshInterval,
        intervalId: null,

        init() {
            if (this.autoRefresh) {
                this.startAutoRefresh();
            }

            window.addEventListener('startAutoRefresh', () => {
                this.autoRefresh = true;
                this.startAutoRefresh();
            });

            window.addEventListener('stopAutoRefresh', () => {
                this.autoRefresh = false;
                this.stopAutoRefresh();
            });
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },

        startAutoRefresh() {
            this.stopAutoRefresh();

            this.intervalId = setInterval(() => {
                window.dispatchEvent(new CustomEvent('table_updated:index-table-failed-job-resource'));
                window.dispatchEvent(new CustomEvent('fragment_updated:queue_metrics'));
            }, this.refreshInterval);
        },

        stopAutoRefresh() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        }
    }));
});
