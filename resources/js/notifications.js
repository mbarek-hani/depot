function notificationBell() {
    return {
        open: false,
        unread: 0,
        endpoint: null,
        timer: null,

        init() {
            this.endpoint = this.$el.dataset.unreadUrl;
            this.unread = Number(this.$el.dataset.unreadCount || 0);
            this.refresh();

            this.timer = window.setInterval(() => this.refresh(), 60000);

            this.$watch('open', (open) => {
                if (open) {
                    this.refresh();
                }
            });
        },

        destroy() {
            window.clearInterval(this.timer);
        },

        async refresh() {
            if (!this.endpoint) {
                return;
            }

            try {
                const response = await fetch(this.endpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                this.unread = Number(data.unread || 0);
            } catch {
                //
            }
        },

        label(count) {
            return count > 99 ? '99+' : String(count);
        },
    };
}

function copyToClipboard() {
    return {
        copied: false,

        async copy(value) {
            try {
                await navigator.clipboard.writeText(value);
                this.copied = true;
                window.setTimeout(() => (this.copied = false), 1800);
            } catch {
                //
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('notificationBell', notificationBell);
    window.Alpine.data('copyToClipboard', copyToClipboard);
});
