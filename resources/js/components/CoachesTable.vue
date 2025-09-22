<template>
    <div class="space-y-4">
        <!-- Search and Filter Controls -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <Input v-model="localFilters.search" placeholder="Search coaches by name or email..."
                    @input="debouncedSearch" class="max-w-sm" />
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-md border hidden md:block">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-if="props.selectable" class="w-12">
                            <Checkbox :model-value="isAllSelected" @update:model-value="toggleSelectAll"
                                :indeterminate="isIndeterminate" />
                        </TableHead>
                        <SortableTableHead label="Name" sort-key="name" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                        <SortableTableHead label="Email" sort-key="email" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                        <SortableTableHead label="Clients" sort-key="clients_count" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                        <TableHead>Status</TableHead>
                        <SortableTableHead label="Date Added" sort-key="created_at" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="coach in props.coaches.data" :key="coach.id">
                        <TableCell v-if="props.selectable">
                            <Checkbox :model-value="selectedRows.includes(coach.id)"
                                @update:model-value="(checked) => toggleRowSelection(coach.id, checked)" />
                        </TableCell>
                        <TableCell class="font-medium">
                            <Link :href="coachRoutes.show(coach.id).url" class="hover:underline">
                            {{ coach.name }}
                            </Link>
                        </TableCell>
                        <TableCell>{{ coach.email }}</TableCell>
                        <TableCell>
                            {{ coach.assigned_clients_count }} client{{ coach.assigned_clients_count === 1 ? '' : 's' }}
                        </TableCell>
                        <TableCell>
                            <Badge :variant="coach.status === 'active' ? 'default' : 'secondary'">
                                {{ coach.status }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ formatDate(coach.created_at) }}</TableCell>
                    </TableRow>
                    <TableEmpty v-if="props.coaches.data.length === 0" :colspan="getColspan()">
                        <div class="text-center py-8">
                            <p class="text-muted-foreground">No coaches found</p>
                            <p class="text-sm text-muted-foreground mt-1">
                                Try adjusting your search or filter criteria
                            </p>
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <!-- Grid for mobile -->
        <div class="md:hidden space-y-4">
            <Card v-for="coach in props.coaches.data" :key="coach.id">
                <CardHeader>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <CardTitle class="truncate">{{ coach.name }}</CardTitle>
                            <p class="text-sm text-muted-foreground truncate">{{ coach.email }}</p>
                        </div>
                        <div v-if="props.selectable" class="pt-1 shrink-0">
                            <Checkbox :model-value="selectedRows.includes(coach.id)"
                                @update:model-value="(checked) => toggleRowSelection(coach.id, checked)" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Clients</span>
                            <span class="font-medium text-right">
                                {{ coach.assigned_clients_count }} client{{ coach.assigned_clients_count === 1 ? '' :
                                    's' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="coach.status === 'active' ? 'default' : 'secondary'">
                                {{ coach.status }}
                            </Badge>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Date Added</span>
                            <span class="font-medium text-right">{{ formatDate(coach.created_at) }}</span>
                        </div>

                        <div class="pt-2">
                            <Link :href="coachRoutes.show(coach.id).url" class="text-primary hover:underline">
                            View details
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="props.coaches.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.coaches.total" :items-per-page="props.coaches.per_page"
                :page="props.coaches.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, withDefaults } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
    SortableTableHead,
} from '@/components/ui/table';
import { PaginationComplete } from '@/components/ui/pagination';
import coachRoutes from '@/routes/tenant/admin/coaches';
import { type PaginatedCoaches, type CoachFilters, type Coach } from '@/types';

interface Props {
    coaches: PaginatedCoaches;
    filters: CoachFilters;
    selectable?: boolean;
    modelValue?: number[];
}

const props = withDefaults(defineProps<Props>(), {
    selectable: false,
    modelValue: () => [],
});

const emit = defineEmits<{
    'update:modelValue': [value: number[]]
}>();

const localFilters = ref({
    search: props.filters.search || '',
    sort_by: props.filters.sort_by || 'name',
    sort_direction: (props.filters.sort_direction as 'asc' | 'desc') || 'asc',
});

// Selection state management - use computed to avoid recursive updates
const selectedRows = computed({
    get: () => props.modelValue,
    set: (value: number[]) => {
        emit('update:modelValue', [...value]);
    }
});

// Selection computed properties
const isAllSelected = computed(() => {
    return props.coaches.data.length > 0 && selectedRows.value.length === props.coaches.data.length;
});

const isIndeterminate = computed(() => {
    return selectedRows.value.length > 0 && selectedRows.value.length < props.coaches.data.length;
});

// Helper function to get correct colspan
const getColspan = () => {
    let colspan = 5; // Name, Email, Clients, Status, Date Added
    if (props.selectable) colspan += 1;
    return colspan;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const updateFilters = () => {
    router.get(window.location.pathname, {
        search: localFilters.value.search || undefined,
        archived: props.filters.archived,
        sort_by: localFilters.value.sort_by,
        sort_direction: localFilters.value.sort_direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Simple debounce function
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 300);
};

const handleSort = (sortKey: string, direction: 'asc' | 'desc') => {
    localFilters.value.sort_by = sortKey;
    localFilters.value.sort_direction = direction;
    updateFilters();
};

const updatePage = (page: number) => {
    router.get(window.location.pathname, {
        ...localFilters.value,
        page,
        archived: props.filters.archived,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Selection functions
const toggleRowSelection = (coachId: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedRows.value];

    if (isChecked) {
        if (!currentSelection.includes(coachId)) {
            currentSelection.push(coachId);
        }
    } else {
        const index = currentSelection.indexOf(coachId);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedRows.value = currentSelection;
};

const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        selectedRows.value = props.coaches.data.map((coach: Coach) => coach.id);
    } else {
        selectedRows.value = [];
    }
};
</script>