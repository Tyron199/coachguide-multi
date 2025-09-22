<template>

    <Head title="Administrators" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdministratorsLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.administrators.total)} ${props.administrators.total === 1 ? 'administrator' : 'administrators'}`"
                    :badge-variant="props.filters.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Archive Selected Button -->
                        <Button v-if="selectedAdmins.length > 0 && !props.filters.archived" variant="outline"
                            @click="handleArchiveSelected">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Selected ({{ selectedAdmins.length }})
                        </Button>

                        <!-- Unarchive Selected Button -->
                        <Button v-if="selectedAdmins.length > 0 && props.filters.archived" variant="outline"
                            @click="handleUnarchiveSelected">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Selected ({{ selectedAdmins.length }})
                        </Button>

                        <!-- Delete Selected Button -->
                        <Button v-if="selectedAdmins.length > 0 && props.filters.archived" variant="destructive"
                            @click="handleDeleteSelected">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Selected ({{ selectedAdmins.length }})
                        </Button>

                        <Link :href="adminRoutes.create().url" v-if="!props.filters.archived">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Administrator
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <AdministratorsTable :administrators="props.administrators" :filters="props.filters"
                    :current-user-id="props.currentUserId" :selectable="true" v-model="selectedAdmins" />
            </div>
        </AdministratorsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AdministratorsLayout from '@/layouts/administrators/Layout.vue';
import AdministratorsTable from '@/components/AdministratorsTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import adminRoutes from '@/routes/tenant/admin/administrators';
import { archive as archiveBatch, unarchive as unarchiveBatch, destroy as deleteBatch } from '@/routes/tenant/admin/administrators/batch';
import { Button } from '@/components/ui/button';
import { Archive, ArchiveRestore, Trash2, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatNumber } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Administrator {
    id: number;
    name: string;
    email: string;
    phone?: string;
    archived: boolean;
    status: string;
    created_at: string;
}

interface PaginatedAdministrators {
    data: Administrator[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface AdministratorFilters {
    search?: string;
    archived: boolean;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    administrators: PaginatedAdministrators;
    filters: AdministratorFilters;
    currentUserId: number;
}

const props = defineProps<Props>();

// Selection state
const selectedAdmins = ref<number[]>([]);

const title = computed(() => {
    return props.filters.archived ? 'Archived Administrators' : 'Active Administrators';
});

const description = computed(() => {
    return props.filters.archived ? 'Manage your archived administrators' : 'Manage your active administrators';
});

// Archive selected administrators function
const handleArchiveSelected = async () => {

    const confirmed = await alertConfirm({
        title: 'Archive Selected Administrators',
        description: `Are you sure you want to archive ${selectedAdmins.value.length} selected ${selectedAdmins.value.length === 1 ? 'administrator' : 'administrators'}?`,
        confirmText: `Archive ${selectedAdmins.value.length} Administrator${selectedAdmins.value.length === 1 ? '' : 's'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.post(archiveBatch().url, {
            admins: selectedAdmins.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedAdmins.value = [];
            }
        });
    }
};

// Unarchive selected administrators function
const handleUnarchiveSelected = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Selected Administrators',
        description: `Are you sure you want to unarchive ${selectedAdmins.value.length} selected ${selectedAdmins.value.length === 1 ? 'administrator' : 'administrators'}?`,
        confirmText: `Unarchive ${selectedAdmins.value.length} Administrator${selectedAdmins.value.length === 1 ? '' : 's'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.post(unarchiveBatch().url, {
            admins: selectedAdmins.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedAdmins.value = [];
            }
        });
    }
};

// Delete selected administrators function
const handleDeleteSelected = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Selected Administrators',
        description: `Are you sure you want to permanently delete ${selectedAdmins.value.length} selected ${selectedAdmins.value.length === 1 ? 'administrator' : 'administrators'}? This action cannot be undone.`,
        confirmText: `Delete ${selectedAdmins.value.length} Administrator${selectedAdmins.value.length === 1 ? '' : 's'}`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(deleteBatch().url, {
            data: {
                admins: selectedAdmins.value
            },
            preserveScroll: true,
            onSuccess: () => {
                selectedAdmins.value = [];
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: props.filters.archived ? 'Archived Administrators' : 'Administrators', href: '#' },
];
</script>
