const STORAGE_KEY = 'theme';
const root = document.documentElement;
const media = window.matchMedia('(prefers-color-scheme: dark)');

function storedTheme() {
    const value = localStorage.getItem(STORAGE_KEY);

    if (['light', 'dark', 'system'].includes(value)) {
        return value;
    }

    const server = root.dataset.theme;

    return ['light', 'dark', 'system'].includes(server) ? server : 'system';
}

function prefersDark() {
    return media.matches;
}

function resolvedTheme(theme = storedTheme()) {
    return theme === 'system' ? (prefersDark() ? 'dark' : 'light') : theme;
}

function applyTheme(theme = storedTheme()) {
    const resolved = resolvedTheme(theme);

    root.classList.toggle('dark', resolved === 'dark');
    root.style.colorScheme = resolved;
    root.dataset.theme = theme;
    root.dataset.resolvedTheme = resolved;

    window.dispatchEvent(
        new CustomEvent('theme-changed', {
            detail: { theme, resolved },
        }),
    );
}

function persistTheme(theme, { remote = true } = {}) {
    localStorage.setItem(STORAGE_KEY, theme);
    applyTheme(theme);

    if (!remote) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const endpoint = root.dataset.themeEndpoint;

    if (!token || !endpoint) {
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            Accept: 'application/json',
        },
        body: JSON.stringify({ theme }),
    }).catch(() => {});
}

export function initTheme() {
    applyTheme();

    media.addEventListener('change', () => {
        if (storedTheme() === 'system') {
            applyTheme('system');
        }
    });

    window.themeManager = {
        current: storedTheme,
        resolved: () => resolvedTheme(),
        set: persistTheme,
        toggle() {
            persistTheme(resolvedTheme() === 'dark' ? 'light' : 'dark');
        },
    };
}

export { resolvedTheme, storedTheme };
