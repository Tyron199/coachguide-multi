import { ref } from 'vue';
import type { AlertState, AlertOptions, AlertInput } from './types';

class AlertService {
    private state = ref<AlertState>({
        isOpen: false,
        options: null,
        resolve: null,
    });

    get alertState() {
        return this.state;
    }

    private normalizeInput(input: AlertInput, variant?: AlertOptions['variant']): AlertOptions {
        if (typeof input === 'string') {
            return {
                title: 'Confirm',
                description: input,
                variant: variant || 'default',
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                showCancel: true,
            };
        }

        return {
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            showCancel: true,
            variant: 'default',
            ...input,
        };
    }

    confirm(input: AlertInput, variant?: AlertOptions['variant']): Promise<boolean> {
        return new Promise((resolve) => {
            const options = this.normalizeInput(input, variant);

            this.state.value = {
                isOpen: true,
                options,
                resolve,
            };
        });
    }

    info(message: string): Promise<void> {
        return new Promise((resolve) => {
            const options: AlertOptions = {
                title: 'Information',
                description: message,
                variant: 'default',
                confirmText: 'OK',
                showCancel: false,
            };

            this.state.value = {
                isOpen: true,
                options,
                resolve: () => resolve(),
            };
        });
    }

    warning(message: string): Promise<void> {
        return new Promise((resolve) => {
            const options: AlertOptions = {
                title: 'Warning',
                description: message,
                variant: 'default',
                confirmText: 'OK',
                showCancel: false,
            };

            this.state.value = {
                isOpen: true,
                options,
                resolve: () => resolve(),
            };
        });
    }

    error(message: string): Promise<void> {
        return new Promise((resolve) => {
            const options: AlertOptions = {
                title: 'Error',
                description: message,
                variant: 'destructive',
                confirmText: 'OK',
                showCancel: false,
            };

            this.state.value = {
                isOpen: true,
                options,
                resolve: () => resolve(),
            };
        });
    }

    handleConfirm() {
        const { resolve, options } = this.state.value;
        this.close();

        if (resolve) {
            if (options?.showCancel === false) {
                resolve();
            } else {
                resolve(true);
            }
        }
    }

    handleCancel() {
        const { resolve } = this.state.value;
        this.close();

        if (resolve) {
            resolve(false);
        }
    }

    private close() {
        this.state.value = {
            isOpen: false,
            options: null,
            resolve: null,
        };
    }
}

export const alertService = new AlertService();
