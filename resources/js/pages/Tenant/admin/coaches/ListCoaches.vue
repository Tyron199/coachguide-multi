<template>

    <Head title="Coaches" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachesLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.coaches.total)} ${props.coaches.total === 1 ? 'coach' : 'coaches'}`"
                    :badge-variant="props.filters.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Archive Selected Button -->
                        <Button v-if="selectedCoaches.length > 0 && !props.filters.archived" variant="outline"
                            @click="handleArchiveSelected">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Selected ({{ selectedCoaches.length }})
                        </Button>

                        <!-- Unarchive Selected Button -->
                        <Button v-if="selectedCoaches.length > 0 && props.filters.archived" variant="outline"
                            @click="handleUnarchiveSelected">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Selected ({{ selectedCoaches.length }})
                        </Button>

                        <!-- Delete Selected Button -->
                        <Button v-if="selectedCoaches.length > 0 && props.filters.archived" variant="destructive"
                            @click="handleDeleteSelected">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Selected ({{ selectedCoaches.length }})
                        </Button>

                        <Link :href="coachRoutes.create().url" v-if="!props.filters.archived">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Coach
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <CoachesTable :coaches="props.coaches" :filters="props.filters" :selectable="true"
                    v-model="selectedCoaches" />
            </div>
        </CoachesLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachesLayout from '@/layouts/coaches/Layout.vue';
import CoachesTable from '@/components/CoachesTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import coachRoutes from '@/routes/tenant/admin/coaches';
import { archive as archiveBatch, unarchive as unarchiveBatch, destroy as deleteBatch } from '@/routes/tenant/admin/coaches/batch';
import { Button } from '@/components/ui/button';
import { Archive, ArchiveRestore, Trash2, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatNumber } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Coach {
    id: number;
    name: string;
    email: string;
    phone?: string;
    assigned_clients_count: number;
    archived: boolean;
    status: string;
    created_at: string;
}

interface PaginatedCoaches {
    data: Coach[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface CoachFilters {
    search?: string;
    archived: boolean;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    coaches: PaginatedCoaches;
    filters: CoachFilters;
}

const props = defineProps<Props>();

// Selection state
const selectedCoaches = ref<number[]>([]);

const title = computed(() => {
    return props.filters.archived ? 'Archived Coaches' : 'Active Coaches';
});

const description = computed(() => {
    return props.filters.archived ? 'Manage your archived coaches' : 'Manage your active coaches';
});

// Archive selected coaches function
const handleArchiveSelected = async () => {

    const confirmed = await alertConfirm({
        title: 'Archive Selected Coaches',
        description: `Are you sure you want to archive ${selectedCoaches.value.length} selected ${selectedCoaches.value.length === 1 ? 'coach' : 'coaches'}?`,
        confirmText: `Archive ${selectedCoaches.value.length} Coach${selectedCoaches.value.length === 1 ? '' : 'es'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.post(archiveBatch().url, {
            coaches: selectedCoaches.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedCoaches.value = [];
            }
        });
    }
};

// Unarchive selected coaches function
const handleUnarchiveSelected = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Selected Coaches',
        description: `Are you sure you want to unarchive ${selectedCoaches.value.length} selected ${selectedCoaches.value.length === 1 ? 'coach' : 'coaches'}?`,
        confirmText: `Unarchive ${selectedCoaches.value.length} Coach${selectedCoaches.value.length === 1 ? '' : 'es'}`,
        variant: 'default'
    });

    if (confirmed) {
        router.post(unarchiveBatch().url, {
            coaches: selectedCoaches.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedCoaches.value = [];
            }
        });
    }
};

// Delete selected coaches function
const handleDeleteSelected = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Selected Coaches',
        description: `Are you sure you want to permanently delete ${selectedCoaches.value.length} selected ${selectedCoaches.value.length === 1 ? 'coach' : 'coaches'}? This action cannot be undone.`,
        confirmText: `Delete ${selectedCoaches.value.length} Coach${selectedCoaches.value.length === 1 ? '' : 'es'}`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(deleteBatch().url, {
            data: {
                coaches: selectedCoaches.value
            },
            preserveScroll: true,
            onSuccess: () => {
                selectedCoaches.value = [];
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: props.filters.archived ? 'Archived Coaches' : 'Coaches', href: '#' },
];
</script>
