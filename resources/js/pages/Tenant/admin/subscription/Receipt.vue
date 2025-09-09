<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Processing Payment" />

        <section class="py-20">
            <div class="container mx-auto max-w-4xl px-4 md:px-6">
                <div class="text-center space-y-8">
                    <!-- Success checkmark animation when subscribed -->
                    <div v-if="subscribed" class="space-y-6">
                        <div
                            class="mx-auto w-20 h-20 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                            <CheckCircle class="w-12 h-12 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="space-y-2">
                            <h1 class="text-3xl font-bold text-green-600 dark:text-green-400">Payment Successful!</h1>
                            <p class="text-lg text-muted-foreground">Your subscription has been activated successfully.
                            </p>
                        </div>
                        <Button @click="redirectToManage" class="mt-6">
                            Go to Subscription Management
                        </Button>
                    </div>

                    <!-- Loading state while waiting for webhook -->
                    <div v-else class="space-y-8">
                        <LoadingSpinner title="Processing Your Payment"
                            description="We're confirming your payment with our payment processor. This usually takes just a few seconds." />

                        <div class="bg-muted/50 rounded-lg p-6 max-w-md mx-auto">
                            <div class="flex items-start space-x-3">
                                <Info class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                                <div class="text-sm text-muted-foreground text-left">
                                    <p class="font-medium text-foreground mb-1">What's happening?</p>
                                    <p>Your payment is being processed securely. Once confirmed, you'll have immediate
                                        access to all subscription features.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback message if taking too long -->
                        <div v-if="showFallbackMessage"
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 max-w-md mx-auto">
                            <div class="flex items-start space-x-3">
                                <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                                <div class="text-sm">
                                    <p class="font-medium text-yellow-800 dark:text-yellow-200 mb-1">Taking longer than
                                        expected?</p>
                                    <p class="text-yellow-700 dark:text-yellow-300">Payment processing can sometimes
                                        take up to 2-3 minutes. If you continue to see this message, please contact
                                        support.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { usePoll } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import { Button } from '@/components/ui/button';
import { BreadcrumbItem } from '@/types';
import { receipt, manage } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
import { CheckCircle, Info, Clock } from 'lucide-vue-next';

interface Props {
    subscribed: boolean;
}

const props = defineProps<Props>();
const page = usePage();

// Get the current subscription status from the page props (this will be updated by polling)
const subscribed = computed(() => page.props.subscribed as boolean);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Processing payment',
        href: receipt().url,
    }
];

// Show fallback message after 30 seconds
const showFallbackMessage = ref(false);
let fallbackTimer: ReturnType<typeof setTimeout>;

// Set up polling to check subscription status every second
const { start, stop } = usePoll(3000, {
    only: ['subscribed'], // Only reload the subscribed prop
}, {
    autoStart: !subscribed.value // Only start polling if not already subscribed
});

// Redirect to manage subscription when subscribed
const redirectToManage = () => {
    router.visit(manage().url);
};

// Watch for subscription status changes and redirect when subscribed
watch(subscribed, (newValue) => {
    if (newValue) {
        // Stop polling once subscribed
        stop();
        // Give user a moment to see the success message, then redirect
        setTimeout(() => {
            redirectToManage();
        }, 2000);
    }
});

// If already subscribed on initial load, redirect immediately
if (subscribed.value) {
    setTimeout(() => {
        redirectToManage();
    }, 2000);
}

onMounted(() => {
    // Show fallback message after 30 seconds
    fallbackTimer = setTimeout(() => {
        showFallbackMessage.value = true;
    }, 30000);
});

onUnmounted(() => {
    // Clean up timer
    if (fallbackTimer) {
        clearTimeout(fallbackTimer);
    }
    // Stop polling when component unmounts
    stop();
});
</script>