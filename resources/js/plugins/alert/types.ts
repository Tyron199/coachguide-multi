export interface AlertOptions {
    title: string;
    description?: string;
    variant?: 'default' | 'destructive';
    confirmText?: string;
    cancelText?: string;
    showCancel?: boolean;
}

export interface AlertState {
    isOpen: boolean;
    options: AlertOptions | null;
    resolve: ((value: boolean) => void) | null;
}

export type AlertInput = string | AlertOptions;

export interface AlertService {
    confirm(input: AlertInput, variant?: AlertOptions['variant']): Promise<boolean>;
    info(message: string): Promise<void>;
    warning(message: string): Promise<void>;
    error(message: string): Promise<void>;
    success(message: string): Promise<void>;
}
