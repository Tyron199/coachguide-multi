<template>
    <div class="space-y-4">
        <!-- Filter Controls -->
        <SessionFilters :clients="props.clients" :coaches="props.coaches" :filters="props.filters"
            :show-filters="props.showFilters" />

        <!-- Table View -->
        <div class="rounded-md border hidden md:block">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-if="props.selectable" class="w-12">
                            <Checkbox :model-value="isAllSelected" @update:model-value="toggleSelectAll"
                                :indeterminate="isIndeterminate" />
                        </TableHead>
                        <SortableTableHead label="Session #" sort-key="session_number"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Client" sort-key="client" :current-sort="props.filters.sort_by"
                            :current-direction="props.filters.sort_direction" @sort="handleSort" />
                        <SortableTableHead v-if="props.canSeeCoachColumn" label="Coach" sort-key="coach"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Scheduled" sort-key="scheduled_at"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Duration" sort-key="duration" :current-sort="props.filters.sort_by"
                            :current-direction="props.filters.sort_direction" @sort="handleSort" />
                        <SortableTableHead label="Type" sort-key="session_type" :current-sort="props.filters.sort_by"
                            :current-direction="props.filters.sort_direction" @sort="handleSort" />
                        <SortableTableHead v-if="showAttendanceColumn" label="Attended" sort-key="client_attended"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="session in props.sessions.data" :key="session.id"
                        :class="{ 'bg-primary/10 border-primary/20': session.is_active }">
                        <TableCell v-if="props.selectable">
                            <Checkbox :model-value="selectedRows.includes(session.id)"
                                @update:model-value="(checked) => toggleRowSelection(session.id, checked)" />
                        </TableCell>
                        <TableCell class="font-medium">
                            <div class="flex items-center gap-2">
                                <Link :href="sessionRoutes.show(session.id).url" class="hover:underline">
                                Session #{{ session.session_number }}
                                </Link>
                                <Badge v-if="session.is_active" variant="default" class="text-xs animate-pulse">
                                    LIVE
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Link v-if="session.client" :href="clientRoutes.show(session.client.id).url"
                                class="hover:underline">
                            {{ session.client.name }}
                            </Link>
                        </TableCell>
                        <TableCell v-if="props.canSeeCoachColumn">{{ session.coach.name }}</TableCell>
                        <TableCell>
                            <div class="space-y-1">
                                <div class="font-medium">{{ formatDate(session.scheduled_at) }}</div>
                                <div class="text-sm text-muted-foreground">{{ formatTime(session.scheduled_at) }}</div>
                            </div>
                        </TableCell>
                        <TableCell>{{ session.formatted_duration || '-' }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getSessionTypeVariant(session.session_type)">
                                    {{ formatSessionType(session.session_type) }}
                                </Badge>
                                <a v-if="getMeetingUrl(session)" :href="getMeetingUrl(session)" target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-primary/80 inline-flex items-center"
                                    title="Join Meeting">
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </TableCell>
                        <TableCell v-if="showAttendanceColumn">
                            <Badge v-if="isSessionInPast(session.scheduled_at)"
                                :variant="session.client_attended === null ? 'secondary' : session.client_attended ? 'default' : 'destructive'">
                                {{ session.client_attended === null ? 'Pending' : session.client_attended ? 'Attended' :
                                    'No Show' }}
                            </Badge>
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="props.sessions.data.length === 0" :colspan="getColspan()">
                        <div class="text-center py-8">
                            <p class="text-muted-foreground">No sessions found</p>
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
            <Card v-for="session in props.sessions.data" :key="session.id"
                :class="{ 'border-primary bg-primary/5': session.is_active }">
                <CardHeader>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <CardTitle class="truncate">
                                <div class="flex items-center gap-2">
                                    <Link :href="sessionRoutes.show(session.id).url" class="hover:underline">
                                    Session #{{ session.session_number }}
                                    </Link>
                                    <Badge v-if="session.is_active" variant="default" class="text-xs animate-pulse">
                                        LIVE
                                    </Badge>
                                </div>
                            </CardTitle>
                            <p class="text-sm text-muted-foreground truncate">
                                <Link v-if="session.client" :href="clientRoutes.show(session.client.id).url"
                                    class="hover:underline">
                                {{ session.client.name }}
                                </Link>
                            </p>
                        </div>
                        <div v-if="props.selectable" class="pt-1 shrink-0">
                            <Checkbox :model-value="selectedRows.includes(session.id)"
                                @update:model-value="(checked) => toggleRowSelection(session.id, checked)" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Scheduled</span>
                            <div class="font-medium text-right">
                                <div>{{ formatDate(session.scheduled_at) }}</div>
                                <div class="text-xs text-muted-foreground">{{ formatTime(session.scheduled_at) }}</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Duration</span>
                            <span class="font-medium text-right">{{ session.formatted_duration || '-' }}</span>
                        </div>

                        <div v-if="props.canSeeCoachColumn" class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Coach</span>
                            <span class="font-medium text-right truncate">{{ session.coach.name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Type</span>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getSessionTypeVariant(session.session_type)">
                                    {{ formatSessionType(session.session_type) }}
                                </Badge>
                                <a v-if="getMeetingUrl(session)" :href="getMeetingUrl(session)" target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-primary/80 inline-flex items-center"
                                    title="Join Meeting">
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </div>

                        <div v-if="showAttendanceColumn && isSessionInPast(session.scheduled_at)"
                            class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Attended</span>
                            <Badge
                                :variant="session.client_attended === null ? 'secondary' : session.client_attended ? 'default' : 'destructive'">
                                {{ session.client_attended === null ? 'Pending' : session.client_attended ? 'Attended' :
                                    'No Show' }}
                            </Badge>
                        </div>

                        <div class="pt-2">
                            <Link :href="sessionRoutes.show(session.id).url" class="text-primary hover:underline">
                            View details
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="props.sessions.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.sessions.total" :items-per-page="props.sessions.per_page"
                :page="props.sessions.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, withDefaults } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Checkbox } from '@/components/ui/checkbox';

import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { ExternalLink } from 'lucide-vue-next';

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
import SessionFilters from '@/components/SessionFilters.vue';
import clientRoutes from '@/routes/tenant/coach/clients';
import sessionRoutes from '@/routes/tenant/coach/coaching-sessions';

interface CalendarEvent {
    id: number;
    meeting_url?: string;
    sync_status: string;
}

interface CoachingSession {
    id: number;
    session_number: number;
    client: {
        id: number;
        name: string;
    };
    coach: {
        id: number;
        name: string;
    };
    scheduled_at: string;
    duration: number;
    formatted_duration: string;
    session_type: 'in_person' | 'online' | 'hybrid';
    client_attended: boolean | null;
    created_at: string;
    is_active: boolean;
    calendar_events?: CalendarEvent[];
}

interface PaginatedSessions {
    data: CoachingSession[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

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
}

interface Props {
    sessions: PaginatedSessions;
    clients?: { id: number; name: string }[];
    coaches?: { id: number; name: string }[];
    filters: SessionFilters;
    showFilters?: boolean;
    canSeeCoachColumn?: boolean;
    selectable?: boolean;
    modelValue?: number[];
}

const props = withDefaults(defineProps<Props>(), {
    showFilters: true,
    canSeeCoachColumn: false,
    selectable: false,
    modelValue: () => [],
    clients: () => [],
    coaches: () => []
});

const emit = defineEmits<{
    'update:modelValue': [value: number[]]
}>();

// Table view is the only view for this component

// Selection state management - use computed to avoid recursive updates
const selectedRows = computed({
    get: () => props.modelValue,
    set: (value: number[]) => {
        emit('update:modelValue', [...value]);
    }
});



// Selection computed properties
const isAllSelected = computed(() => {
    return props.sessions.data.length > 0 && selectedRows.value.length === props.sessions.data.length;
});

const isIndeterminate = computed(() => {
    return selectedRows.value.length > 0 && selectedRows.value.length < props.sessions.data.length;
});

// Helper function to get correct colspan
const getColspan = () => {
    let colspan = 6; // Base columns: Session #, Client, Scheduled, Duration, Type
    if (props.canSeeCoachColumn) colspan += 1;
    if (props.selectable) colspan += 1;
    if (showAttendanceColumn.value) colspan += 1; // Add attendance column if not viewing upcoming sessions
    return colspan;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

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

const getSessionTypeVariant = (type: string) => {
    switch (type) {
        case 'in_person':
            return 'default';
        case 'online':
            return 'secondary';
        case 'hybrid':
            return 'outline';
        default:
            return 'secondary';
    }
};

const isSessionInPast = (scheduledAt: string) => {
    const sessionDate = new Date(scheduledAt);
    const now = new Date();
    return sessionDate < now;
};

const showAttendanceColumn = computed(() => {
    // Don't show attendance column if we're specifically viewing upcoming sessions
    return !props.filters.upcoming;
});



const handleSort = (sortKey: string, direction: 'asc' | 'desc') => {
    // Preserve existing query parameters
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    currentParams.forEach((value, key) => {
        if (key !== 'sort_by' && key !== 'sort_direction') {
            preservedParams[key] = value;
        }
    });

    router.get(window.location.pathname, {
        ...preservedParams,
        sort_by: sortKey,
        sort_direction: direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updatePage = (page: number) => {
    // Preserve existing query parameters
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    currentParams.forEach((value, key) => {
        if (key !== 'page') {
            preservedParams[key] = value;
        }
    });

    router.get(window.location.pathname, {
        ...preservedParams,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Selection functions
const toggleRowSelection = (sessionId: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedRows.value];

    if (isChecked) {
        if (!currentSelection.includes(sessionId)) {
            currentSelection.push(sessionId);
        }
    } else {
        const index = currentSelection.indexOf(sessionId);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedRows.value = currentSelection;
};

const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        selectedRows.value = props.sessions.data.map(session => session.id);
    } else {
        selectedRows.value = [];
    }
};

const getMeetingUrl = (session: CoachingSession): string | null => {
    // Only show meeting URL for online or hybrid sessions
    if (session.session_type !== 'online' && session.session_type !== 'hybrid') {
        return null;
    }

    // Get the first calendar event with a meeting URL
    const eventWithUrl = session.calendar_events?.find(event => event.meeting_url);
    return eventWithUrl?.meeting_url || null;
};



</script>
