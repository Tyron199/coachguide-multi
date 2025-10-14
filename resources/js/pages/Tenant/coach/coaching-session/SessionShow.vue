<template>

    <Head :title="`Session with ${session.client?.name} - Session Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader title="Session Details" description="Overview of session information and details"
                    :badge="session.client_attended === true ? 'Attended' : session.client_attended === false ? 'Not Attended' : undefined"
                    :badge-variant="session.client_attended === true ? 'default' : session.client_attended === false ? 'destructive' : 'secondary'">
                    <template #actions>
                        <Link v-if="can.update" :href="sessions.edit(session.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Session
                        </Button>
                        </Link>

                        <Button v-if="can.delete" variant="destructive" @click="handleDeleteSession">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Session
                        </Button>
                    </template>
                </PageHeader>

                <!-- Single Session Overview Card -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-medium mb-4">Session Overview</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Client</Label>
                            <p class="mt-1">
                                <Link v-if="session.client" :href="clients.show(session.client.id).url"
                                    class="font-medium text-primary hover:underline">
                                {{ session.client.name }}
                                </Link>
                                <span v-else>No client assigned</span>
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Date & Time</Label>
                            <p class="mt-1 font-medium">{{ formatDateTime(session.scheduled_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Duration</Label>
                            <p class="mt-1">{{ session.formatted_duration || 'Not specified' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Session Type</Label>
                            <div class="mt-1 flex items-center gap-2">
                                <Badge>{{ formatSessionType(session.session_type) }}</Badge>
                                <Button v-if="getMeetingUrl()" variant="default" size="sm" as-child>
                                    <a :href="getMeetingUrl()" target="_blank" rel="noopener noreferrer">
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        Join Meeting
                                    </a>
                                </Button>
                            </div>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Session Number</Label>
                            <p class="mt-1">Session #{{ session.session_number }}</p>
                        </div>
                        <div v-if="isSessionInPast">
                            <Label class="text-sm font-medium text-muted-foreground">Attendance</Label>
                            <div class="mt-1">
                                <Badge v-if="session.client_attended === true" variant="default">
                                    Attended
                                </Badge>
                                <Badge v-else-if="session.client_attended === false" variant="destructive">
                                    Not Attended
                                </Badge>
                                <Badge v-else variant="secondary">
                                    Not Recorded
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Integration Card -->
                <div v-if="session.calendar_events && session.calendar_events.length > 0"
                    class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-medium mb-4">Calendar Sync Status</h2>
                    <div class="space-y-4">
                        <div v-for="event in session.calendar_events" :key="event.id"
                            class="flex items-start justify-between p-4 rounded-lg border bg-muted/30">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span class="font-medium">{{ formatProvider(event.provider) }}</span>
                                    <Badge v-if="event.user" variant="outline" class="text-xs">
                                        {{ event.user.name }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <div class="flex items-center gap-1">
                                        <span>Status:</span>
                                        <Badge :variant="getSyncStatusVariant(event.sync_status)" class="text-xs">
                                            {{ formatSyncStatus(event.sync_status) }}
                                        </Badge>
                                    </div>
                                    <div v-if="event.last_synced_at" class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" />
                                        <span>{{ formatRelativeTime(event.last_synced_at) }}</span>
                                    </div>
                                </div>
                                <div v-if="event.sync_error" class="text-sm text-destructive">
                                    Error: {{ event.sync_error }}
                                </div>
                                <div v-if="event.meeting_url" class="flex items-center gap-2">
                                    <a :href="event.meeting_url" target="_blank" rel="noopener noreferrer"
                                        class="text-sm text-primary hover:underline inline-flex items-center gap-1">
                                        <ExternalLink class="h-3 w-3" />
                                        <span>Meeting Link</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </CoachingSessionLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem, type CoachingSession } from '@/types';
import sessions from '@/routes/tenant/coach/coaching-sessions';
import clients from '@/routes/tenant/coach/clients';
import { Edit, Trash2, ExternalLink, Calendar, Clock } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { alertConfirm } from '@/plugins/alert';

const props = defineProps<{
    session: CoachingSession;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.session.is_past ? 'Past Sessions' : 'Sessions',
        href: props.session.is_past ? sessions.past().url : sessions.index().url,
    },
    {
        title: `Session #${props.session.session_number} with ${props.session.client?.name}`,
        href: sessions.show(props.session.id).url,
    }
];

const formatDateTime = (dateString: string | undefined) => {
    if (!dateString) return 'Not specified';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
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

const isSessionInPast = computed(() => {
    if (!props.session.scheduled_at) return false;
    return new Date(props.session.scheduled_at) < new Date();
});

const getMeetingUrl = (): string | null => {
    // Only show meeting URL for online or hybrid sessions
    if (props.session.session_type !== 'online' && props.session.session_type !== 'hybrid') {
        return null;
    }

    // Get the first calendar event with a meeting URL
    const eventWithUrl = props.session.calendar_events?.find(event => event.meeting_url);
    return eventWithUrl?.meeting_url || null;
};

const formatProvider = (provider: string): string => {
    switch (provider.toLowerCase()) {
        case 'google':
            return 'Google Calendar';
        case 'microsoft':
        case 'outlook':
            return 'Microsoft Outlook';
        default:
            return provider;
    }
};

const formatSyncStatus = (status: string): string => {
    switch (status) {
        case 'created':
            return 'Synced';
        case 'updated':
            return 'Updated';
        case 'failed':
            return 'Failed';
        case 'deleted':
            return 'Deleted';
        default:
            return status;
    }
};

const getSyncStatusVariant = (status: string) => {
    switch (status) {
        case 'created':
        case 'updated':
            return 'default';
        case 'failed':
            return 'destructive';
        case 'deleted':
            return 'secondary';
        default:
            return 'outline';
    }
};

const formatRelativeTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

// Delete session function
const handleDeleteSession = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Session',
        description: `Are you sure you want to delete the session with ${props.session.client?.name}? This action cannot be undone.`,
        confirmText: 'Delete Session',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(sessions.destroy(props.session.id).url, {
            onSuccess: () => {
                // Redirect to sessions list after successful deletion
                router.get(sessions.index().url);
            },
            onError: (errors) => {
                console.error('Error deleting session:', errors);
            }
        });
    }
};
</script>
