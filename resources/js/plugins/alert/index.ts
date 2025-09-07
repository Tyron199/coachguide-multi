import type { App } from 'vue';
import { alertService } from './AlertService';
import type { AlertService } from './types';

// Composable for use in components
export function useAlert(): AlertService {
    return {
        confirm: alertService.confirm.bind(alertService),
        info: alertService.info.bind(alertService),
        warning: alertService.warning.bind(alertService),
        error: alertService.error.bind(alertService),
    };
}

// Convenience functions for direct import
export const alertConfirm = alertService.confirm.bind(alertService);
export const alertInfo = alertService.info.bind(alertService);
export const alertWarning = alertService.warning.bind(alertService);
export const alertError = alertService.error.bind(alertService);

// Vue plugin
export default {
    install(app: App) {
        // Global properties (accessible via this.$alert in Options API)
        app.config.globalProperties.$alert = {
            confirm: alertService.confirm.bind(alertService),
            info: alertService.info.bind(alertService),
            warning: alertService.warning.bind(alertService),
            error: alertService.error.bind(alertService),
        };

        // Provide for injection (accessible via inject('alert'))
        app.provide('alert', {
            confirm: alertService.confirm.bind(alertService),
            info: alertService.info.bind(alertService),
            warning: alertService.warning.bind(alertService),
            error: alertService.error.bind(alertService),
        });
    },
};

// Export the provider component
export { default as AlertProvider } from './AlertProvider.vue';

// Export types
export type { AlertOptions, AlertInput, AlertService } from './types';
