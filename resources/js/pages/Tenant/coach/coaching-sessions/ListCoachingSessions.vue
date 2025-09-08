<template>

    <Head title="Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionsLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.sessions.total)} ${props.sessions.total === 1 ? 'session' : 'sessions'}`"
                    :badge-variant="'default'">
                    <template #actions>
                        <!-- Selection Actions -->
                        <Button v-if="selectedSessions.length > 0" variant="outline"
                            @click="handleMarkAttendance(true)">
                            <CheckCircle class="mr-2 h-4 w-4" />
                            Mark Attended ({{ selectedSessions.length }})
                        </Button>

                        <Button v-if="selectedSessions.length > 0" variant="outline"
                            @click="handleMarkAttendance(false)">
                            <XCircle class="mr-2 h-4 w-4" />
                            Mark No Show ({{ selectedSessions.length }})
                        </Button>

                        <Button v-if="selectedSessions.length > 0" variant="destructive" @click="handleDeleteSessions">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete ({{ selectedSessions.length }})
                        </Button>

                        <Link :href="coachingSessions.create().url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Schedule Session
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <!-- Table View -->
                <SessionsTable v-if="props.filters.view === 'table'" :sessions="props.sessions" :clients="props.clients"
                    :coaches="props.coaches" :filters="props.filters" :can-see-coach-column="props.canSeeCoachColumn"
                    :selectable="true" v-model="selectedSessions" />

                <!-- Calendar View -->
                <SessionsCalendar v-else-if="props.filters.view === 'calendar'" :sessions="props.sessions.data"
                    :clients="props.clients" :coaches="props.coaches" :filters="props.filters"
                    :can-see-coach-column="props.canSeeCoachColumn" :selectable="true" v-model="selectedSessions"
                    @week-changed="handleWeekChanged" />
            </div>
        </CoachingSessionsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionsLayout from '@/layouts/coaching-sessions/Layout.vue';
import SessionsTable from '@/components/SessionsTable.vue';
import SessionsCalendar from '@/components/SessionsCalendar.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import coachingSessions from '@/routes/tenant/coach/coaching-sessions';
import { Button } from '@/components/ui/button';
import { CheckCircle, XCircle, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { formatNumber } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';
import CoachingSessionController from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
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
    upcoming?: boolean;
    view?: 'table' | 'calendar';
    date_from?: string;
    date_to?: string;
}

interface Props {
    sessions: PaginatedSessions;
    clients?: { id: number; name: string }[];
    coaches?: { id: number; name: string }[];
    filters: SessionFilters;
    canSeeCoachColumn?: boolean;
}

const props = defineProps<Props>();

// Selection state
const selectedSessions = ref<number[]>([]);

const title = computed(() => {
    if (props.filters.view === 'calendar') {
        return 'Sessions Calendar';
    }
    return props.filters.upcoming ? 'Upcoming Sessions' : 'Past Sessions';
});

const description = computed(() => {
    if (props.filters.view === 'calendar') {
        return 'View your coaching sessions in a calendar view';
    }
    return props.filters.upcoming ? 'Manage your upcoming coaching sessions' : 'Review your past coaching sessions';
});



// Mark attendance function
const handleMarkAttendance = async (attended: boolean) => {


    const action = attended ? 'mark as attended' : 'mark as no show';
    const actionTitle = attended ? 'Mark Sessions as Attended' : 'Mark Sessions as No Show';

    const confirmed = await alertConfirm({
        title: actionTitle,
        description: `Are you sure you want to ${action} ${selectedSessions.value.length} selected ${selectedSessions.value.length === 1 ? 'session' : 'sessions'}?`,
        confirmText: `${attended ? 'Mark Attended' : 'Mark No Show'} (${selectedSessions.value.length})`,
        variant: 'default'
    });

    if (confirmed) {
        router.patch('/coaching-sessions/attendance', {
            sessions: selectedSessions.value,
            attended: attended
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedSessions.value = [];
            },
            onError: (errors) => {
                console.error('Error updating attendance:', errors);
            }
        });
    }
};

// Delete sessions function
const handleDeleteSessions = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Sessions',
        description: `Are you sure you want to delete ${selectedSessions.value.length} selected ${selectedSessions.value.length === 1 ? 'session' : 'sessions'}? This action cannot be undone.`,
        confirmText: `Delete (${selectedSessions.value.length})`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.post('/coach/coaching-sessions/delete', {
            sessions: selectedSessions.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedSessions.value = [];
            },
            onError: (errors) => {
                console.error('Error deleting sessions:', errors);
            }
        });
    }
};

// Handle week changes in calendar view
const handleWeekChanged = (weekStart: Date) => {
    const endDate = new Date(weekStart);
    endDate.setDate(weekStart.getDate() + 6);

    const formatDateForAPI = (date: Date) => {
        return date.toISOString().split('T')[0];
    };

    // Navigate with new date range - always use the unified calendar route
    router.get(CoachingSessionController.calendar().url, {
        date_from: formatDateForAPI(weekStart),
        date_to: formatDateForAPI(endDate)
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: coachingSessions.index().url
    },
    {
        title: title.value,
        href: coachingSessions.index().url
    },
];
</script>
