<template>

    <Head :title="`${company.name} - Employees`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CompanyLayout :company="company">
            <div class="space-y-6">
                <PageHeader title="Company Employees" :description="`Manage clients from ${company.name}`">
                    <template #actions>
                        <Link :href="createClientAction({ query: { company_id: company.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Client
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <ClientsTable :clients="props.clients" :companies="props.companies" :filters="props.filters"
                    :show-company-filter="false" :can-see-coach-column="props.canSeeCoachColumn" />
            </div>
        </CompanyLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CompanyLayout from '@/layouts/company/Layout.vue';
import ClientsTable from '@/components/ClientsTable.vue';
import { type BreadcrumbItem, type PaginatedClients, type Company } from '@/types';

import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import { Plus } from 'lucide-vue-next';
import { create as createClientAction } from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import companiesRoutes from '@/routes/tenant/coach/companies';
import clientsRoutes from '@/routes/tenant/coach/clients';
interface Props {
    company: Company;
    clients: PaginatedClients;
    companies: Company[];
    filters: {
        search?: string;
        company_id?: number;
        archived?: boolean;
    };
    canSeeCoachColumn?: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Companies',
        href: companiesRoutes.index().url,
    },
    {
        title: props.company.name,
        href: companiesRoutes.show(props.company.id).url,
    },
    {
        title: 'Employees',
        href: companiesRoutes.employees(props.company.id).url,
    }
];
</script>
