<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
            <Input v-model="localFilters.search" placeholder="Search tasks by title..."
                @input="debouncedSearch" class="max-w-sm" />
        </div>

        <div class="flex gap-2">
            <!-- Client Filter -->
            <DropdownMenu v-if="props.clients && props.clients.length > 0">
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[140px] justify-between">
                        {{ selectedClientName }}
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem @click="selectClient(null)">
                        All Clients
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="props.clients.length > 0" />
                    <DropdownMenuItem v-for="client in props.clients" :key="client.id"
                        @click="selectClient(Number(client.id))">
                        {{ client.name }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Status Filter -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[140px] justify-between">
                        {{ selectedStatusName }}
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem @click="selectStatus(null)">
                        All Statuses
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="selectStatus('pending')">
                        Pending
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectStatus('in_progress')">
                        In Progress
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectStatus('review')">
                        In Review
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectStatus('completed')">
                        Completed
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectStatus('cancelled')">
                        Cancelled
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Evidence Required Filter -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[140px] justify-between">
                        {{ selectedEvidenceName }}
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem @click="selectEvidence(null)">
                        All Tasks
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="selectEvidence(true)">
                        Evidence Required
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectEvidence(false)">
                        No Evidence Required
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';

interface TaskFilters {
    search?: string;
    client_id?: number | null;
    status?: string | null;
    evidence_required?: boolean | null;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    view?: 'pending' | 'overdue' | 'completed';
}

interface Props {
    clients?: { id: number; name: string }[];
    filters: TaskFilters;
}

const props = defineProps<Props>();

// Local state for immediate UI updates
const localFilters = ref({
    search: props.filters.search || '',
    client_id: props.filters.client_id || null,
    status: props.filters.status || null,
    evidence_required: props.filters.evidence_required || null,
});

// Computed display names
const selectedClientName = computed(() => {
    if (!localFilters.value.client_id) return 'All Clients';
    const client = props.clients?.find(c => c.id === localFilters.value.client_id);
    return client?.name || 'All Clients';
});

const selectedStatusName = computed(() => {
    if (!localFilters.value.status) return 'All Statuses';
    const statusMap: Record<string, string> = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'review': 'In Review',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
    };
    return statusMap[localFilters.value.status] || 'All Statuses';
});

const selectedEvidenceName = computed(() => {
    if (localFilters.value.evidence_required === null) return 'All Tasks';
    return localFilters.value.evidence_required ? 'Evidence Required' : 'No Evidence Required';
});

// Apply filters
const applyFilters = () => {
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    // Preserve sort and view parameters
    currentParams.forEach((value, key) => {
        if (key === 'sort_by' || key === 'sort_direction' || key === 'view') {
            preservedParams[key] = value;
        }
    });

    const filters: Record<string, string | number> = {
        ...preservedParams,
    };

    if (localFilters.value.search) {
        filters.search = localFilters.value.search;
    }
    if (localFilters.value.client_id) {
        filters.client_id = localFilters.value.client_id;
    }
    if (localFilters.value.status) {
        filters.status = localFilters.value.status;
    }
    if (localFilters.value.evidence_required !== null) {
        filters.evidence_required = localFilters.value.evidence_required ? '1' : '0';
    }

    router.get(window.location.pathname, filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Debounced search
const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

// Filter selection handlers
const selectClient = (clientId: number | null) => {
    localFilters.value.client_id = clientId;
    applyFilters();
};

const selectStatus = (status: string | null) => {
    localFilters.value.status = status;
    applyFilters();
};

const selectEvidence = (required: boolean | null) => {
    localFilters.value.evidence_required = required;
    applyFilters();
};

// Watch for external filter changes (e.g., back button navigation)
watch(() => props.filters, (newFilters) => {
    localFilters.value = {
        search: newFilters.search || '',
        client_id: newFilters.client_id || null,
        status: newFilters.status || null,
        evidence_required: newFilters.evidence_required || null,
    };
}, { deep: true });
</script>

