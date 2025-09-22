<template>
    <div class="space-y-4">
        <!-- Search and Filter Controls -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <Input v-model="localFilters.search" placeholder="Search administrators by name or email..."
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
                        <TableHead>Status</TableHead>
                        <SortableTableHead label="Date Added" sort-key="created_at" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="admin in props.administrators.data" :key="admin.id">
                        <TableCell v-if="props.selectable">
                            <Checkbox :model-value="selectedRows.includes(admin.id)"
                                @update:model-value="(checked) => toggleRowSelection(admin.id, checked)"
                                :disabled="admin.id === props.currentUserId" />
                        </TableCell>
                        <TableCell class="font-medium">
                            <Link :href="adminRoutes.show(admin.id).url" class="hover:underline">
                            {{ admin.name }}
                            </Link>
                            <span v-if="admin.id === props.currentUserId"
                                class="ml-2 text-xs text-muted-foreground">(You)</span>
                        </TableCell>
                        <TableCell>{{ admin.email }}</TableCell>
                        <TableCell>
                            <Badge :variant="admin.status === 'active' ? 'default' : 'secondary'">
                                {{ admin.status }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ formatDate(admin.created_at) }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <Link :href="adminRoutes.show(admin.id).url">
                                <Button variant="ghost" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                </Link>
                                <Link :href="adminRoutes.edit(admin.id).url">
                                <Button variant="ghost" size="sm">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                </Link>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="props.administrators.data.length === 0" :colspan="getColspan()">
                        <div class="text-center py-8">
                            <p class="text-muted-foreground">No administrators found</p>
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
            <Card v-for="admin in props.administrators.data" :key="admin.id">
                <CardHeader>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <CardTitle class="truncate">
                                {{ admin.name }}
                                <span v-if="admin.id === props.currentUserId"
                                    class="text-xs text-muted-foreground">(You)</span>
                            </CardTitle>
                            <p class="text-sm text-muted-foreground truncate">{{ admin.email }}</p>
                        </div>
                        <div v-if="props.selectable" class="pt-1 shrink-0">
                            <Checkbox :model-value="selectedRows.includes(admin.id)"
                                @update:model-value="(checked) => toggleRowSelection(admin.id, checked)"
                                :disabled="admin.id === props.currentUserId" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="admin.status === 'active' ? 'default' : 'secondary'">
                                {{ admin.status }}
                            </Badge>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Date Added</span>
                            <span class="font-medium text-right">{{ formatDate(admin.created_at) }}</span>
                        </div>

                        <div class="pt-2">
                            <Link :href="adminRoutes.show(admin.id).url" class="text-primary hover:underline">
                            View details
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="props.administrators.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.administrators.total" :items-per-page="props.administrators.per_page"
                :page="props.administrators.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, withDefaults } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
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
import adminRoutes from '@/routes/tenant/admin/administrators';
import { type PaginatedAdministrators, type AdministratorFilters, type Administrator } from '@/types';
import { Eye, Edit } from 'lucide-vue-next';

interface Props {
    administrators: PaginatedAdministrators;
    filters: AdministratorFilters;
    currentUserId: number;
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
    const selectableAdmins = props.administrators.data.filter(admin => admin.id !== props.currentUserId);
    return selectableAdmins.length > 0 && selectableAdmins.every(admin => selectedRows.value.includes(admin.id));
});

const isIndeterminate = computed(() => {
    const selectableAdmins = props.administrators.data.filter(admin => admin.id !== props.currentUserId);
    const selectedSelectableCount = selectedRows.value.filter(id =>
        selectableAdmins.some(admin => admin.id === id)
    ).length;
    return selectedSelectableCount > 0 && selectedSelectableCount < selectableAdmins.length;
});

// Helper function to get correct colspan
const getColspan = () => {
    let colspan = 5; // Name, Email, Status, Date Added, Actions
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
const toggleRowSelection = (adminId: number, checked: boolean | 'indeterminate') => {
    // Prevent selecting self
    if (adminId === props.currentUserId) return;

    const isChecked = checked === true;
    const currentSelection = [...selectedRows.value];

    if (isChecked) {
        if (!currentSelection.includes(adminId)) {
            currentSelection.push(adminId);
        }
    } else {
        const index = currentSelection.indexOf(adminId);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedRows.value = currentSelection;
};

const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        // Only select administrators that are not the current user
        const selectableAdmins = props.administrators.data.filter(admin => admin.id !== props.currentUserId);
        selectedRows.value = selectableAdmins.map((admin: Administrator) => admin.id);
    } else {
        selectedRows.value = [];
    }
};
</script>
