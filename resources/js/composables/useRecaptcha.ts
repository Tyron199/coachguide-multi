import { ref, onMounted, onUnmounted } from 'vue';

export interface RecaptchaOptions {
    siteKey: string;
    size?: 'normal' | 'compact' | 'invisible';
    theme?: 'light' | 'dark';
    callback?: (token: string) => void;
    expiredCallback?: () => void;
    errorCallback?: () => void;
}

export function useRecaptcha(options: RecaptchaOptions) {
    const isLoaded = ref(false);
    const widgetId = ref<number | null>(null);
    const containerRef = ref<HTMLElement | null>(null);

    const loadRecaptcha = () => {
        return new Promise<void>((resolve, reject) => {
            if (window.grecaptcha) {
                isLoaded.value = true;
                resolve();
                return;
            }

            // Create script element
            const script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js';
            script.async = true;
            script.defer = true;

            script.onload = () => {
                isLoaded.value = true;
                resolve();
            };

            script.onerror = () => {
                reject(new Error('Failed to load reCAPTCHA'));
            };

            document.head.appendChild(script);
        });
    };

    let tries = 0;
    const renderRecaptcha = () => {
        tries++;
        if (!window.grecaptcha || !containerRef.value) return;
        try {
            widgetId.value = window.grecaptcha.render(containerRef.value, {
                sitekey: options.siteKey,
                size: options.size || 'normal',
                theme: options.theme || 'light',
                callback: options.callback,
                'expired-callback': options.expiredCallback,
                'error-callback': options.errorCallback,
            });
        } catch (ex) {
            if (tries > 10) return;
            setTimeout(renderRecaptcha, 250);
        }
    };

    const getResponse = (): string | null => {
        if (!window.grecaptcha || widgetId.value === null) return null;
        return window.grecaptcha.getResponse(widgetId.value);
    };

    const reset = () => {
        if (!window.grecaptcha || widgetId.value === null) return;
        window.grecaptcha.reset(widgetId.value);
    };

    const execute = () => {
        if (!window.grecaptcha || widgetId.value === null) return;
        window.grecaptcha.execute(widgetId.value);
    };

    onMounted(async () => {
        try {
            await loadRecaptcha();
            // Wait a bit for grecaptcha to be fully available
            console.log("Rendering reCAPTCHA");
            setTimeout(renderRecaptcha, 250);
        } catch (error) {
            console.error('Failed to initialize reCAPTCHA:', error);
        }
    });

    onUnmounted(() => {
        if (window.grecaptcha && widgetId.value !== null) {
            // Clean up if needed
        }
    });

    return {
        isLoaded,
        containerRef,
        getResponse,
        reset,
        execute,
    };
}

// Global types for reCAPTCHA
declare global {
    interface Window {
        grecaptcha: {
            render: (container: HTMLElement | string, options: any) => number;
            getResponse: (widgetId?: number) => string;
            reset: (widgetId?: number) => void;
            execute: (widgetId?: number) => void;
        };
    }
}
