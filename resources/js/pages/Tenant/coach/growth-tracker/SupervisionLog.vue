<template>

    <Head title="Growth Tracker - Supervision Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Supervision Log" description="Manage your supervision log" :badge="`0 entries`">
                    <template #actions>

                    </template>
                </PageHeader>

                <div
                    class="relative flex justify-center items-center min-h-96 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-700">
                    <PlaceholderPattern />
                    <div class="relative z-10 text-center">
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                            No supervision log entries yet. Start tracking your supervision sessions.
                        </p>
                    </div>
                </div>

            </div>
        </GrowthTrackerLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
// import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem, type PaginatedCompanies, type CompanyFilters } from '@/types';
import { index } from '@/actions/App/Http/Controllers/Tenant/Coach/SupervisionLogController';

import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

interface Props {
    companies: PaginatedCompanies;
    filters: CompanyFilters;
}

defineProps<Props>();

// Selection state
// const selectedCompanies = ref<number[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Growth Tracker',
        href: index().url
    },
    {
        title: 'Supervision Log',
        href: index().url
    },
];
</script>