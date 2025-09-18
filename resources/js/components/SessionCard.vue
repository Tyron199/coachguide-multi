<template>
    <div class="relative">
        <!-- Selection checkbox (positioned in top-right corner when selectable) -->
        <div v-if="props.selectable" class="absolute -top-1 -right-1 z-10">
            <Checkbox :model-value="props.selected" @update:model-value="(checked) => emit('toggle-selection', checked)"
                class="bg-background border-2 shadow-sm scale-75" />
        </div>

        <Link :href="coachingSessions.show(session.id).url"
            class="block p-2 rounded border hover:bg-accent/50 transition-colors group bg-background text-xs"
            :class="{ 'border-primary bg-primary/10': session.is_active }">

        <!-- Time badge (primary highlight) -->
        <div class="flex items-center justify-between gap-1 mb-1">
            <Badge :variant="isSessionWithinHour(session.scheduled_at) ? 'destructive' : 'default'" :class="[
                'text-[11px] px-2 py-1 font-semibold',
                isSessionWithinHour(session.scheduled_at) ? 'animate-pulse' : ''
            ]">
                {{ formatTime(session.scheduled_at) }}
            </Badge>
            <div class="flex items-center gap-1">
                <Badge v-if="session.is_active" variant="default" class="text-[10px] px-1 py-0 animate-pulse">
                    LIVE
                </Badge>
                <Badge :variant="getSessionTypeVariant(session.session_type)" class="text-[10px] px-1 py-0">
                    {{ getSessionTypeShort(session.session_type) }}
                </Badge>
            </div>
        </div>

        <!-- Client name -->
        <div class="font-medium text-foreground mb-1 truncate">
            {{ session.client.name }}
        </div>

        <!-- Session number and duration -->
        <div class="text-muted-foreground mb-1 truncate">
            #{{ session.session_number }} • {{ session.formatted_duration || '30m' }}
        </div>

        <!-- Coach (if visible) -->
        <div v-if="props.canSeeCoachColumn" class="text-muted-foreground mb-1 truncate">
            {{ session.coach.name }}
        </div>

        <!-- Bottom row: Attendance (only for past sessions) and meeting link -->
        <div class="flex items-center justify-between gap-1">
            <div>
                <Badge v-if="isSessionInPast(session.scheduled_at)"
                    :variant="getAttendanceVariant(session.client_attended)" class="text-[10px] px-1 py-0">
                    {{ getAttendanceShort(session.client_attended) }}
                </Badge>
            </div>

            <Button variant="ghost" size="sm" class="h-4 px-1 text-[10px] text-muted-foreground hover:text-foreground"
                disabled>
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                </svg>
            </Button>
        </div>
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import coachingSessions from '@/routes/tenant/coach/coaching-sessions';

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

interface Props {
    session: CoachingSession;
    canSeeCoachColumn?: boolean;
    selectable?: boolean;
    selected?: boolean;
    isActive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canSeeCoachColumn: false,
    selectable: false,
    selected: false,
    isActive: false,
});

const emit = defineEmits<{
    'toggle-selection': [checked: boolean | 'indeterminate']
}>();

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
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



const getAttendanceVariant = (attended: boolean | null) => {
    if (attended === null) return 'secondary';
    return attended ? 'default' : 'destructive';
};



const getSessionTypeShort = (type: string) => {
    switch (type) {
        case 'in_person':
            return 'In Person';
        case 'online':
            return 'Online';
        case 'hybrid':
            return 'Hybrid';
        default:
            return type.charAt(0).toUpperCase() + type.slice(1);
    }
};

const getAttendanceShort = (attended: boolean | null) => {
    if (attended === null) return 'No Show';
    return attended ? 'Attended' : 'No Show';
};

const isSessionInPast = (scheduledAt: string) => {
    const sessionDate = new Date(scheduledAt);
    const now = new Date();
    return sessionDate < now;
};

const isSessionWithinHour = (scheduledAt: string) => {
    const sessionDate = new Date(scheduledAt);
    const now = new Date();
    const oneHourFromNow = new Date(now.getTime() + (60 * 60 * 1000)); // Add 1 hour in milliseconds

    // Session is within the next hour if it's after now and before one hour from now
    return sessionDate >= now && sessionDate <= oneHourFromNow;
};
</script>
