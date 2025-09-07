<template>
    <div class="space-y-4">
        <!-- Search and Filter Controls -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <Input v-model="localFilters.search" placeholder="Search clients by name or email..."
                    @input="debouncedSearch" class="max-w-sm" />
            </div>
            <div class="flex gap-2" v-if="props.showCompanyFilter">
                <!-- Company Filter -->
                <DropdownMenu v-if="props.companies.length > 0">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="min-w-[140px] justify-between">
                            {{ selectedCompanyName }}
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-[200px]">
                        <DropdownMenuItem @click="selectCompany(null, false)">
                            All Companies
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="selectCompany(null, true)">
                            No Company
                        </DropdownMenuItem>
                        <DropdownMenuSeparator v-if="props.companies.length > 0" />
                        <DropdownMenuItem v-for="company in props.companies" :key="company.id"
                            @click="selectCompany(Number(company.id), false)">
                            {{ company.name }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Coach Filter (only for admins) -->
                <DropdownMenu v-if="props.coaches && props.coaches.length > 0">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="min-w-[140px] justify-between">
                            {{ selectedCoachName }}
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-[200px]">
                        <DropdownMenuItem @click="selectCoach(null)">
                            All Coaches
                        </DropdownMenuItem>
                        <DropdownMenuSeparator v-if="props.coaches.length > 0" />
                        <DropdownMenuItem v-for="coach in props.coaches" :key="coach.id"
                            @click="selectCoach(Number(coach.id))">
                            {{ coach.name }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
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
                        <SortableTableHead label="Company" sort-key="company" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                        <SortableTableHead v-if="props.canSeeCoachColumn" label="Coach" sort-key="coach"
                            :current-sort="localFilters.sort_by" :current-direction="localFilters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Date Added" sort-key="created_at" :current-sort="localFilters.sort_by"
                            :current-direction="localFilters.sort_direction" @sort="handleSort" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="client in props.clients.data" :key="client.id">
                        <TableCell v-if="props.selectable">
                            <Checkbox :model-value="selectedRows.includes(client.id)"
                                @update:model-value="(checked) => toggleRowSelection(client.id, checked)" />
                        </TableCell>
                        <TableCell class="font-medium">
                            <Link :href="clientRoutes.show(client.id).url" class="hover:underline">
                            {{ client.name }}
                            </Link>
                        </TableCell>
                        <TableCell>{{ client.email }}</TableCell>
                        <TableCell>
                            <Link :href="companyRoutes.show(client.company.id).url" v-if="client.company"
                                class="hover:underline">
                            {{ client.company?.name || 'No Company' }}
                            </Link>
                            <span v-else>No Company</span>
                        </TableCell>
                        <TableCell v-if="props.canSeeCoachColumn">{{ client.assigned_coach?.name || 'No Coach' }}
                        </TableCell>
                        <TableCell>{{ formatDate(client.created_at) }}</TableCell>
                    </TableRow>
                    <TableEmpty v-if="props.clients.data.length === 0" :colspan="getColspan()">
                        <div class="text-center py-8">
                            <p class="text-muted-foreground">No clients found</p>
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
            <Card v-for="client in props.clients.data" :key="client.id">
                <CardHeader>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <CardTitle class="truncate">{{ client.name }}</CardTitle>
                            <p class="text-sm text-muted-foreground truncate">{{ client.email }}</p>
                        </div>
                        <div v-if="props.selectable" class="pt-1 shrink-0">
                            <Checkbox :model-value="selectedRows.includes(client.id)"
                                @update:model-value="(checked) => toggleRowSelection(client.id, checked)" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Company</span>
                            <div class="font-medium text-right truncate">
                                <Link v-if="client.company" :href="companyRoutes.show(client.company.id).url"
                                    class="hover:underline">
                                {{ client.company?.name || 'No Company' }}
                                </Link>
                                <span v-else>No Company</span>
                            </div>
                        </div>

                        <div v-if="props.canSeeCoachColumn" class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Coach</span>
                            <span class="font-medium text-right truncate">{{ client.assigned_coach?.name || 'No Coach'
                                }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Date Added</span>
                            <span class="font-medium text-right">{{ formatDate(client.created_at) }}</span>
                        </div>

                        <div class="pt-2">
                            <Link :href="clientRoutes.show(client.id).url" class="text-primary hover:underline">
                            View details
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="props.clients.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.clients.total" :items-per-page="props.clients.per_page"
                :page="props.clients.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, withDefaults } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import clientRoutes from '@/routes/tenant/clients';
import companyRoutes from '@/routes/tenant/companies';
import { type PaginatedClients, type Company, type ClientFilters } from '@/types';


interface Props {
    clients: PaginatedClients;
    companies: Company[];
    coaches?: { id: number; name: string }[];
    filters: ClientFilters;
    showCompanyFilter?: boolean;
    canSeeCoachColumn?: boolean;
    selectable?: boolean;
    modelValue?: number[];
}

const props = withDefaults(defineProps<Props>(), {
    showCompanyFilter: true,
    selectable: false,
    modelValue: () => [],
    coaches: () => []
});

const emit = defineEmits<{
    'update:modelValue': [value: number[]]
}>();

const localFilters = ref({
    search: props.filters.search || '',
    company_id: props.filters.company_id || null,
    no_company: props.filters.no_company || false,
    coach_id: props.filters.coach_id || null,
    sort_by: props.filters.sort_by || 'name',
    sort_direction: props.filters.sort_direction || 'asc',
});

// Selection state management - use computed to avoid recursive updates
const selectedRows = computed({
    get: () => props.modelValue,
    set: (value: number[]) => {
        emit('update:modelValue', [...value]);
    }
});

const selectedCompanyName = computed(() => {
    if (localFilters.value.no_company) return 'No Company';
    if (!localFilters.value.company_id) return 'All Companies';
    const company = props.companies.find(c => c.id === Number(localFilters.value.company_id));
    return company?.name || 'All Companies';
});

const selectedCoachName = computed(() => {
    if (!localFilters.value.coach_id) return 'All Coaches';
    const coach = props.coaches?.find(c => c.id === Number(localFilters.value.coach_id));
    return coach?.name || 'All Coaches';
});

// Selection computed properties
const isAllSelected = computed(() => {
    return props.clients.data.length > 0 && selectedRows.value.length === props.clients.data.length;
});

const isIndeterminate = computed(() => {
    return selectedRows.value.length > 0 && selectedRows.value.length < props.clients.data.length;
});

// Helper function to get correct colspan
const getColspan = () => {
    let colspan = props.canSeeCoachColumn ? 5 : 4;
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
        company_id: localFilters.value.company_id || undefined,
        no_company: localFilters.value.no_company || undefined,
        coach_id: localFilters.value.coach_id || undefined,
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

const selectCompany = (companyId: number | null, noCompany: boolean = false) => {
    localFilters.value.company_id = noCompany ? null : companyId;
    localFilters.value.no_company = noCompany;
    updateFilters();
};

const selectCoach = (coachId: number | null) => {
    localFilters.value.coach_id = coachId;
    updateFilters();
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
const toggleRowSelection = (clientId: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedRows.value];

    if (isChecked) {
        if (!currentSelection.includes(clientId)) {
            currentSelection.push(clientId);
        }
    } else {
        const index = currentSelection.indexOf(clientId);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedRows.value = currentSelection;
};

const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        selectedRows.value = props.clients.data.map(client => client.id);
    } else {
        selectedRows.value = [];
    }
};
</script>
