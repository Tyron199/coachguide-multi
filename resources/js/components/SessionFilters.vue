<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
            <Input v-model="localFilters.search" placeholder="Search sessions by client name..."
                @input="debouncedSearch" class="max-w-sm" />
        </div>

        <div class="flex gap-2" v-if="props.showFilters">
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

            <!-- Session Type Filter -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[140px] justify-between">
                        {{ selectedSessionTypeName }}
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem @click="selectSessionType(null)">
                        All Types
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="selectSessionType('in_person')">
                        In Person
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectSessionType('online')">
                        Online
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectSessionType('hybrid')">
                        Hybrid
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Attendance Filter -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[140px] justify-between">
                        {{ selectedAttendanceName }}
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem @click="selectAttendance(null)">
                        All Sessions
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="selectAttendance(true)">
                        Attended
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="selectAttendance(false)">
                        Not Attended
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, withDefaults } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface SessionFilters {
    search?: string;
    client_id?: number | null;
    coach_id?: number | null;
    session_type?: string | null;
    client_attended?: boolean | null;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    view?: 'table' | 'calendar';
    upcoming?: boolean;
    date_from?: string;
    date_to?: string;
}

interface Props {
    clients?: { id: number; name: string }[];
    coaches?: { id: number; name: string }[];
    filters: SessionFilters;
    showFilters?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showFilters: true,
    clients: () => [],
    coaches: () => []
});

const localFilters = ref<{
    search: string;
    client_id: number | null;
    coach_id: number | null;
    session_type: string | null;
    client_attended: boolean | null;
}>({
    search: props.filters.search || '',
    client_id: props.filters.client_id || null,
    coach_id: props.filters.coach_id || null,
    session_type: props.filters.session_type || null,
    client_attended: props.filters.client_attended ?? null,
});

const selectedClientName = computed(() => {
    if (!localFilters.value.client_id) return 'All Clients';
    const client = props.clients?.find(c => c.id === Number(localFilters.value.client_id));
    return client?.name || 'All Clients';
});

const selectedCoachName = computed(() => {
    if (!localFilters.value.coach_id) return 'All Coaches';
    const coach = props.coaches?.find(c => c.id === Number(localFilters.value.coach_id));
    return coach?.name || 'All Coaches';
});

const selectedSessionTypeName = computed(() => {
    if (!localFilters.value.session_type) return 'All Types';
    return formatSessionType(localFilters.value.session_type);
});

const selectedAttendanceName = computed(() => {
    if (localFilters.value.client_attended === null) return 'All Sessions';
    return localFilters.value.client_attended ? 'Attended' : 'Not Attended';
});

const formatSessionType = (type: string) => {
    switch (type) {
        case 'in_person':
            return 'In Person';
        case 'online':
            return 'Online';
        case 'hybrid':
            return 'Hybrid';
        default:
            return type;
    }
};

const updateFilters = (additionalParams = {}) => {
    const baseParams = {
        search: localFilters.value.search || undefined,
        client_id: localFilters.value.client_id || undefined,
        coach_id: localFilters.value.coach_id || undefined,
        session_type: localFilters.value.session_type || undefined,
        client_attended: localFilters.value.client_attended === null ? undefined : localFilters.value.client_attended,
    };

    // Preserve existing query parameters like date_from, date_to, etc.
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    // Preserve calendar-specific params
    if (currentParams.get('date_from')) preservedParams.date_from = currentParams.get('date_from')!;
    if (currentParams.get('date_to')) preservedParams.date_to = currentParams.get('date_to')!;

    router.get(window.location.pathname, {
        ...preservedParams,
        ...baseParams,
        ...additionalParams
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

const selectClient = (clientId: number | null) => {
    localFilters.value.client_id = clientId;
    updateFilters();
};

const selectCoach = (coachId: number | null) => {
    localFilters.value.coach_id = coachId;
    updateFilters();
};

const selectSessionType = (sessionType: string | null) => {
    localFilters.value.session_type = sessionType;
    updateFilters();
};

const selectAttendance = (attended: boolean | null) => {
    localFilters.value.client_attended = attended;
    updateFilters();
};
</script>
