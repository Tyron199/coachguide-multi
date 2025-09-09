<template>
    <AlertDialog :open="alertState.isOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ alertState.options?.title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="alertState.options?.description">
                    {{ alertState.options.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel v-if="alertState.options?.showCancel" @click="handleCancel">
                    {{ alertState.options?.cancelText }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :class="alertState.options?.variant === 'destructive' ? 'bg-destructive hover:bg-destructive/90 text-destructive-foreground' : ''"
                    @click="handleConfirm">
                    {{ alertState.options?.confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { alertService } from './AlertService';

const alertState = alertService.alertState;

const handleConfirm = () => {
    alertService.handleConfirm();
};

const handleCancel = () => {
    alertService.handleCancel();
};
</script>
