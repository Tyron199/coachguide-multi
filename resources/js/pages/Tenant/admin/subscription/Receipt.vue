<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Subscribe" />

        <BillingLayout>
            <template #header>
                <h1 class="text-2xl font-semibold text-foreground">Subscribe</h1>
                <p class="text-muted-foreground">Subscribe to the platform to continue using it.</p>
            </template>
        </BillingLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BillingLayout from '@/layouts/billing/Layout.vue';
import { BreadcrumbItem } from '@/types';
import { manage, receipt } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: manage().url,
    },
    {
        title: 'Receipt',
        href: receipt().url,
    },
];
</script>