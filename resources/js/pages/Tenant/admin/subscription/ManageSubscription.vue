<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Manage Subscription" />

        <BillingLayout>

        </BillingLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BillingLayout from '@/layouts/billing/Layout.vue';
import { BreadcrumbItem } from '@/types';
import { manage } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: manage().url,
    }
];
</script>