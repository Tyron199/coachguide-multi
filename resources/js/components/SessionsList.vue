<template>
    <div class="space-y-4">
        <!-- Empty state -->
        <div v-if="sessions.length === 0" class="text-center py-12">
            <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                <Calendar1Icon class="h-6 w-6 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-medium mb-2">No sessions yet</h3>
            <p class="text-muted-foreground mb-4">
                Coaching sessions for this client will appear here.
            </p>
            <slot name="empty-actions"></slot>
        </div>

        <!-- Sessions list -->
        <div v-else class="space-y-6">
            <!-- Upcoming Sessions -->
            <div v-if="upcomingSessions.length > 0">
                <h3 class="text-sm font-medium text-muted-foreground mb-3 uppercase tracking-wider">
                    Upcoming Sessions ({{ upcomingSessions.length }})
                </h3>
                <div class="space-y-3">
                    <div v-for="session in upcomingSessions" :key="session.id"
                        class="rounded-lg border bg-card p-4 sm:p-5 hover:shadow-sm transition-shadow"
                        :class="{ 'border-primary bg-primary/5': session.is_active }">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <!-- Session title and badges -->
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <Link :href="sessionRoutes.show(session.id).url"
                                        class="text-lg font-medium hover:text-primary transition-colors hover:underline">
                                    Session #{{ session.session_number }}
                                    </Link>
                                    <Badge v-if="session.is_active" variant="default" class="text-xs animate-pulse">
                                        LIVE
                                    </Badge>
                                    <Badge :variant="getSessionTypeVariant(session.session_type)">
                                        {{ formatSessionType(session.session_type) }}
                                    </Badge>
                                </div>

                                <!-- Session details -->
                                <div class="flex flex-col gap-2 text-sm text-muted-foreground">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ formatDate(session.scheduled_at) }} at {{
                                            formatTime(session.scheduled_at) }}</span>
                                    </div>
                                    <div v-if="session.duration" class="flex items-center gap-2">
                                        <Timer class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ session.formatted_duration }}</span>
                                    </div>
                                    <div v-if="session.coach" class="flex items-center gap-2">
                                        <User class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ session.coach.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <Button v-if="getMeetingUrl(session)" variant="default" size="sm" as-child
                                    class="flex-shrink-0">
                                    <a :href="getMeetingUrl(session)" target="_blank" rel="noopener noreferrer">
                                        <ExternalLink class="mr-1 h-3 w-3" />
                                        <span class="hidden xs:inline">Join Meeting</span>
                                    </a>
                                </Button>
                                <Button variant="outline" size="sm" as-child class="flex-shrink-0">
                                    <Link :href="sessionRoutes.show(session.id).url">
                                    <Eye class="mr-1 h-3 w-3" />
                                    <span class="hidden xs:inline">View</span>
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Past Sessions -->
            <div v-if="pastSessions.length > 0">
                <h3 class="text-sm font-medium text-muted-foreground mb-3 uppercase tracking-wider">
                    Past Sessions ({{ pastSessions.length }})
                </h3>
                <div class="space-y-3">
                    <div v-for="session in pastSessions" :key="session.id"
                        class="rounded-lg border bg-card p-4 sm:p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <!-- Session title and badges -->
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <Link :href="sessionRoutes.show(session.id).url"
                                        class="text-lg font-medium hover:text-primary transition-colors hover:underline">
                                    Session #{{ session.session_number }}
                                    </Link>
                                    <Badge :variant="getSessionTypeVariant(session.session_type)">
                                        {{ formatSessionType(session.session_type) }}
                                    </Badge>
                                    <Badge v-if="session.client_attended !== null"
                                        :variant="session.client_attended ? 'default' : 'destructive'">
                                        {{ session.client_attended ? 'Attended' : 'No Show' }}
                                    </Badge>
                                </div>

                                <!-- Session details -->
                                <div class="flex flex-col gap-2 text-sm text-muted-foreground">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ formatDate(session.scheduled_at) }} at {{
                                            formatTime(session.scheduled_at) }}</span>
                                    </div>
                                    <div v-if="session.duration" class="flex items-center gap-2">
                                        <Timer class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ session.formatted_duration }}</span>
                                    </div>
                                    <div v-if="session.coach" class="flex items-center gap-2">
                                        <User class="h-4 w-4 flex-shrink-0" />
                                        <span>{{ session.coach.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <Button v-if="getMeetingUrl(session)" variant="default" size="sm" as-child
                                    class="flex-shrink-0">
                                    <a :href="getMeetingUrl(session)" target="_blank" rel="noopener noreferrer">
                                        <ExternalLink class="mr-1 h-3 w-3" />
                                        <span class="hidden xs:inline">Join Meeting</span>
                                    </a>
                                </Button>
                                <Button variant="outline" size="sm" as-child class="flex-shrink-0">
                                    <Link :href="sessionRoutes.show(session.id).url">
                                    <Eye class="mr-1 h-3 w-3" />
                                    <span class="hidden xs:inline">View</span>
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type CoachingSession } from '@/types';
import { Calendar1Icon, Clock, Timer, User, Eye, ExternalLink } from 'lucide-vue-next';
import sessionRoutes from '@/routes/tenant/coach/coaching-sessions';

interface Props {
    sessions: CoachingSession[];
    canEdit?: boolean;
    canDelete?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canEdit: true,
    canDelete: true
});

// Separate sessions into upcoming and past
const upcomingSessions = computed(() => {
    const now = new Date();
    return props.sessions.filter(session => {
        const sessionDate = new Date(session.scheduled_at);
        return sessionDate > now;
    }).sort((a, b) => {
        // Sort ascending (earliest first) for upcoming
        return new Date(a.scheduled_at).getTime() - new Date(b.scheduled_at).getTime();
    });
});

const pastSessions = computed(() => {
    const now = new Date();
    return props.sessions.filter(session => {
        const sessionDate = new Date(session.scheduled_at);
        return sessionDate <= now;
    }).sort((a, b) => {
        // Sort descending (most recent first) for past
        return new Date(b.scheduled_at).getTime() - new Date(a.scheduled_at).getTime();
    });
});

// Format date for display
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Format time for display
const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Format session type for display
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

// Get session type badge variant
const getMeetingUrl = (session: CoachingSession): string | null => {
    // Only show meeting URL for online or hybrid sessions
    if (session.session_type !== 'online' && session.session_type !== 'hybrid') {
        return null;
    }

    // Get the first calendar event with a meeting URL
    const eventWithUrl = session.calendar_events?.find(event => event.meeting_url);
    return eventWithUrl?.meeting_url || null;
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
</script>
