<template>

    <Head title="Companies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientsLayout>
            <div class="space-y-6">
                <PageHeader title="Companies" description="Manage your client companies"
                    :badge="`${formatNumber(props.companies.total)} ${props.companies.total === 1 ? 'company' : 'companies'}`">
                    <template #actions>
                        <Link :href="companiesRoutes.create().url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Company
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <CompaniesTable :companies="props.companies" :filters="props.filters" :selectable="false"
                    v-model="selectedCompanies" />
            </div>
        </ClientsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientsLayout from '@/layouts/clients/Layout.vue';
import CompaniesTable from '@/components/CompaniesTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem, type PaginatedCompanies, type CompanyFilters } from '@/types';
import { Button } from '@/components/ui/button';

import { formatNumber } from '@/lib/utils';
import { Plus } from 'lucide-vue-next';
import companiesRoutes from '@/routes/tenant/coach/companies';

interface Props {
    companies: PaginatedCompanies;
    filters: CompanyFilters;
}

const props = defineProps<Props>();

// Selection state
const selectedCompanies = ref<number[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Companies',
        href: companiesRoutes.index().url
    }
];
</script>