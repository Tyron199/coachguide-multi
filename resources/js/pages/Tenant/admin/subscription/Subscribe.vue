<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Subscribe" />

        <section class="py-10" id="pricing">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <SubscriptionPlans :plans="plans" title="Choose a plan to get started"
                    description="Select the perfect plan to elevate your coaching practice"
                    :can-have-trial="canHaveTrial" @plan-selected="handlePlanSelected" />
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { subscribe } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
import SubscriptionPlans from '@/components/SubscriptionPlans.vue';
import { initializePaddle, Paddle } from '@paddle/paddle-js';
import { ref, onMounted } from 'vue';
import { isDarkMode } from '@/composables/useAppearance';
import { alertError, alertInfo } from '@/plugins/alert';
const props = defineProps({
    token: {
        type: String,
        required: true
    },
    plans: {
        type: Object,
        required: true
    },
    sandbox: {
        type: Boolean,
        required: true
    },
    customer: {
        type: Object,
        required: true
    },
    canHaveTrial: {
        type: Boolean,
        required: true
    },
    success_url: {
        type: String,
        required: true
    }
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscribe',
        href: subscribe().url,
    },
];

// Create a ref to store the Paddle instance
const paddle = ref<Paddle | undefined>(undefined);

// Initialize Paddle when component is mounted
onMounted(async () => {
    // Check if we're in sandbox/test mode
    const environment = props.sandbox ? 'sandbox' : 'production';
    try {
        // Initialize Paddle with your client-side token
        paddle.value = await initializePaddle({
            environment,
            token: props.token
        });
    } catch (error) {
        console.error('Failed to initialize Paddle:', error);
        alertError("There was an error initializing Paddle. Please try again later.");
    }
});


const handlePlanSelected = (priceId: string) => {
    if (!paddle.value) {
        console.error('Paddle is not initialized');
        return;
    }
    try {
        paddle.value.Checkout.open({
            settings: {
                displayMode: "overlay",
                theme: isDarkMode() ? 'dark' : 'light',
                successUrl: props.success_url,
            },
            items: [{
                priceId: priceId,
                quantity: 1
            }],
            customer: props.customer.paddle_id ?
                { id: props.customer.paddle_id } :
                undefined
        });
    } catch (error) {
        console.error('Failed to open Paddle checkout:', error);
        alertError("There was an error opening Paddle checkout. Please try again later.");
    }
};
</script>