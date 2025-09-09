import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';
type Layout = 'sidebar' | 'header';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const getStoredLayout = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('layout') as Layout | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<Appearance>('system');
const layout = ref<Layout>('sidebar');

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        const savedLayout = localStorage.getItem('layout') as Layout | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }

        if (savedLayout) {
            layout.value = savedLayout;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    function updateLayout(value: Layout) {
        layout.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('layout', value);

        // Store in cookie for SSR...
        setCookie('layout', value);
    }

    function toggleLayout() {
        const newLayout = layout.value === 'sidebar' ? 'header' : 'sidebar';
        updateLayout(newLayout);
    }

    function toggleAppearance() {
        const newAppearance = appearance.value === 'light' ? 'dark' : 'light';
        updateAppearance(newAppearance);
    }

    return {
        appearance,
        updateAppearance,
        layout,
        updateLayout,
        toggleLayout,
        toggleAppearance,
    };
}

export function isDarkMode() {
    if (typeof window === 'undefined') {
        return false;
    }
    //First check local
    const local = localStorage.getItem('appearance') as Appearance | null
    if (local && local !== 'system') {
        return local === 'dark';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}