<template>

    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientsLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.clients.total)} ${props.clients.total === 1 ? 'client' : 'clients'}`"
                    :badge-variant="props.filters.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Archive Selected Button -->
                        <Button v-if="selectedClients.length > 0 && !props.filters.archived" variant="outline"
                            @click="handleArchiveSelected">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Selected ({{ selectedClients.length }})
                        </Button>

                        <!-- Unarchive Selected Button -->
                        <Button v-if="selectedClients.length > 0 && props.filters.archived" variant="outline"
                            @click="handleUnarchiveSelected">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Selected ({{ selectedClients.length }})
                        </Button>

                        <!-- Delete Selected Button -->
                        <Button v-if="selectedClients.length > 0 && props.filters.archived" variant="destructive"
                            @click="handleDeleteSelected">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Selected ({{ selectedClients.length }})
                        </Button>

                        <Link :href="clientRoutes.create().url" v-if="!props.filters.archived">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Client
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <ClientsTable :clients="props.clients" :companies="props.companies" :coaches="props.coaches"
                    :filters="props.filters" :can-see-coach-column="props.canSeeCoachColumn" :selectable="true"
                    v-model="selectedClients" />
            </div>
        </ClientsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientsLayout from '@/layouts/clients/Layout.vue';
import ClientsTable from '@/components/ClientsTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem, type PaginatedClients, type Company, type ClientFilters } from '@/types';
import clientRoutes from '@/routes/tenant/coach/clients';
import { batch as archiveBatch } from '@/routes/tenant/coach/clients/archive';
import { batch as unarchiveBatch } from '@/routes/tenant/coach/clients/unarchive';
import { batch as deleteBatch } from '@/routes/tenant/coach/clients/delete';
import { Button } from '@/components/ui/button';
import { Archive, ArchiveRestore, Trash2, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatNumber } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';


interface Props {
    clients: PaginatedClients;
    companies: Company[];
    coaches?: { id: number; name: string }[];
    filters: ClientFilters;
    canSeeCoachColumn?: boolean;
}

const props = defineProps<Props>();

// Selection state
const selectedClients = ref<number[]>([]);

const title = computed(() => {
    return props.filters.archived ? 'Archived Clients' : 'Active Clients';
});

const description = computed(() => {
    return props.filters.archived ? 'Manage your archived coaching clients' : 'Manage your active coaching clients';
});



// Archive selected clients function
const handleArchiveSelected = async () => {

    const confirmed = await alertConfirm({
        title: 'Archive Selected Clients',
        description: `Are you sure you want to archive ${selectedClients.value.length} selected ${selectedClients.value.length === 1 ? 'client' : 'clients'}?`,
        confirmText: `Archive ${selectedClients.value.length} Client${selectedClients.value.length === 1 ? '' : 's'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.visit(archiveBatch(), {
            data: {
                clients: selectedClients.value
            },
            preserveScroll: true,
            onSuccess: () => {
                selectedClients.value = [];
            },
            onError: (errors) => {
                console.error('Error archiving clients:', errors);
            }
        });
    }
};

// Unarchive selected clients function
const handleUnarchiveSelected = async () => {

    const confirmed = await alertConfirm({
        title: 'Unarchive Selected Clients',
        description: `Are you sure you want to unarchive ${selectedClients.value.length} selected ${selectedClients.value.length === 1 ? 'client' : 'clients'}?`,
        confirmText: `Unarchive ${selectedClients.value.length} Client${selectedClients.value.length === 1 ? '' : 's'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.visit(unarchiveBatch(), {
            data: {
                clients: selectedClients.value
            },
            preserveScroll: true,
            onSuccess: () => {
                selectedClients.value = [];
            },
            onError: (errors) => {
                console.error('Error unarchiving clients:', errors);
            }
        });
    }
};

// Delete selected clients function
const handleDeleteSelected = async () => {

    const confirmed = await alertConfirm({
        title: 'Delete Selected Clients',
        description: `Are you sure you want to delete ${selectedClients.value.length} selected ${selectedClients.value.length === 1 ? 'client' : 'clients'}?`,
        confirmText: `Delete ${selectedClients.value.length} Client${selectedClients.value.length === 1 ? '' : 's'}`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.visit(deleteBatch(), {
            data: {
                clients: selectedClients.value
            },
            preserveScroll: true,
            onSuccess: () => {
                selectedClients.value = [];
            },
            onError: (errors) => {
                console.error('Error deleting clients:', errors);
            }
        });
    }
};


const breadcrumbs: BreadcrumbItem[] = [

];

if (props.filters.archived) {
    breadcrumbs.splice(1, 0, {
        title: 'Archived Clients',
        href: clientRoutes.archived().url
    });
} else {
    breadcrumbs.splice(1, 0, {
        title: 'Active Clients',
        href: clientRoutes.index().url
    });
}
</script>