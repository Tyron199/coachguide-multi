<script setup lang="ts">
import { Toaster as SonnerToaster } from "@/components/ui/sonner"
import { toast } from "vue-sonner"
import { usePage } from "@inertiajs/vue3";
import { onMounted, watch } from 'vue'
import { useAppearance } from '@/composables/useAppearance'
import 'vue-sonner/style.css'
const page = usePage();
const { appearance } = useAppearance();

// Function to display flash messages
const showFlashMessages = (message: any) => {
    console.log("showFlashMessages", message)
    if (message?.success) {
        toast.success(message.success);
    }
    if (message?.error) {
        toast.error(message.error);
    }
    if (message?.info) {
        toast.info(message.info);
    }
    if (message?.warning) {
        toast.warning(message.warning);
    }
};

// Ensure the Toaster is mounted before firing toasts (important on navigation)
onMounted(() => {
    // Show any existing flash messages immediately on mount
    if (page.props.flash) {
        console.log("Showing flash onMounted")
        showFlashMessages(page.props.flash);
    }

    // Watch for changes to the Inertia flash props for subsequent navigations
    watch(
        () => page.props.flash,
        (message: any) => {
            console.log("Showing flash on watch")
            showFlashMessages(message);
        },
        { deep: true }
    );
});


</script>
<template>
    <SonnerToaster richColors closeButton :theme="appearance" />
</template>