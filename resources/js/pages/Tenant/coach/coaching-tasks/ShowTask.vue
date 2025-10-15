<template>

    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <component :is="layoutComponent" v-bind="layoutProps">
            <div class="space-y-6">
                <PageHeader :title="task.title" :description="pageDescription">
                    <template #actions>
                        <Button v-if="canMarkComplete" @click="markAsComplete">
                            <CheckCircle class="mr-2 h-4 w-4" />
                            Mark as Complete
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="taskRoutes.edit(task.id).url">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Task
                            </Link>
                        </Button>
                    </template>
                </PageHeader>

                <!-- Task Details Card -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="space-y-6">
                        <!-- Status and Badges -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <Badge :variant="getStatusVariant(task.status)">
                                {{ formatStatus(task.status) }}
                            </Badge>
                            <Badge v-if="task.evidence_required" variant="outline">
                                Evidence Required
                            </Badge>
                            <Badge v-if="isOverdue(task)" variant="destructive">
                                Overdue
                            </Badge>
                        </div>

                        <!-- Task Information -->
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Description</Label>
                                <div class="mt-1 text-foreground whitespace-pre-wrap">{{ task.description }}</div>
                            </div>
                            <div class="space-y-4">
                                <div v-if="task.deadline">
                                    <Label class="text-sm font-medium text-muted-foreground">Deadline</Label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <Clock class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(task.deadline) }}</span>
                                    </div>
                                </div>
                                <div v-if="task.reminders && task.reminders.length > 0">
                                    <Label class="text-sm font-medium text-muted-foreground">Reminders</Label>
                                    <div class="mt-1 space-y-1">
                                        <div v-for="reminder in task.reminders" :key="reminder.id"
                                            class="flex items-center gap-2 text-sm">
                                            <Bell class="h-3 w-3 text-muted-foreground" />
                                            <span>{{ reminder.label }}</span>
                                            <Badge v-if="reminder.status === 'sent'" variant="success" class="text-xs">
                                                Sent</Badge>
                                            <Badge v-else-if="reminder.status === 'failed'" variant="destructive"
                                                class="text-xs">Failed
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Context -->
                        <div v-if="task.session" class="pt-4 border-t">
                            <Label class="text-sm font-medium text-muted-foreground">Associated Session</Label>
                            <div class="mt-1 p-3 bg-muted rounded-md">
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="h-4 w-4" />
                                    <span class="font-medium">Session {{ task.session.session_number }}</span>
                                    <span class="text-muted-foreground">-</span>
                                    <span>{{ formatDate(task.session.scheduled_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Actions/Submissions -->
                <div v-if="task.actions && task.actions.length > 0" class="space-y-4">
                    <div class="flex items-center gap-2">
                        <MessageSquare class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-lg font-medium">Client Submissions</h2>
                        <Badge variant="secondary">{{ task.actions.length }}</Badge>
                    </div>

                    <div class="space-y-4">
                        <div v-for="action in task.actions" :key="action.id" class="rounded-lg border bg-card p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        <User class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ action.user.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatDate(action.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="whitespace-pre-wrap text-foreground mb-3">{{ action.content }}</div>

                            <!-- Action Attachments -->
                            <div v-if="action.attachments && action.attachments.length > 0" class="mt-3 pt-3 border-t">
                                <div class="flex items-center gap-2 mb-2">
                                    <Paperclip class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm font-medium text-muted-foreground">
                                        {{ action.attachments.length }} attachment{{ action.attachments.length === 1 ?
                                            '' : 's' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <a v-for="attachment in action.attachments" :key="attachment.id"
                                        :href="attachment.url"
                                        class="flex items-center gap-2 px-3 py-2 bg-muted hover:bg-muted/80 rounded-md text-sm transition-colors"
                                        target="_blank">
                                        <File class="h-4 w-4 flex-shrink-0" />
                                        <span class="truncate">{{ attachment.original_name }}</span>
                                        <span class="text-xs text-muted-foreground">({{ attachment.formatted_size
                                        }})</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state for no submissions -->
                <div v-else-if="task.evidence_required" class="rounded-lg border bg-card p-8 text-center">
                    <MessageSquare class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-medium mb-2">No submissions yet</h3>
                    <p class="text-muted-foreground">
                        This task requires evidence. The client will need to submit their work before it can be marked
                        as complete.
                    </p>
                </div>
            </div>
        </component>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem, type CoachingTask } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import sessions from '@/routes/tenant/coach/coaching-sessions';
import taskRoutes from '@/routes/tenant/coach/coaching-tasks';
import { updateStatus as updateTaskStatus } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingTaskController';
import { alertConfirm } from '@/plugins/alert';
import { Calendar, Clock, Bell, MessageSquare, User, Paperclip, File, Edit, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{
    task: CoachingTask & {
        actions?: Array<{
            id: number;
            content: string;
            created_at: string;
            user: { id: number; name: string };
            attachments?: Array<{
                id: number;
                original_name: string;
                url: string;
                formatted_size: string;
            }>;
        }>;
    };
}>();

// Dynamic layout selection based on context
const layoutComponent = computed(() => {
    return props.task.session ? CoachingSessionLayout : ClientLayout;
});

const layoutProps = computed(() => {
    if (props.task.session) {
        return { session: props.task.session };
    } else {
        return { client: props.task.client! }; // Non-null assertion since client is required
    }
});

const pageDescription = computed(() => {
    return props.task.session
        ? 'Task details and client submissions'
        : 'Task details and client submissions';
});

const pageTitle = computed(() => {
    return props.task.session
        ? `Task: ${props.task.title}`
        : `Task: ${props.task.title}`;
});

const breadcrumbs = computed((): BreadcrumbItem[] => {
    if (props.task.session) {
        // Session context breadcrumbs
        return [
            {
                title: 'Sessions',
                href: sessions.index().url,
            },
            {
                title: `Session ${props.task.session.session_number}`,
                href: sessions.show(props.task.session.id).url,
            },
            {
                title: 'Tasks',
                href: sessions.tasks(props.task.session.id).url,
            },
            {
                title: props.task.title,
                href: '#',
            }
        ];
    } else {
        // Client context breadcrumbs
        const clientBreadcrumbs: BreadcrumbItem[] = [
            {
                title: 'Clients',
                href: clients.index().url,
            },
            {
                title: props.task.client?.name || 'Client',
                href: clients.show(props.task.client?.id || 0).url,
            },
            {
                title: 'Tasks',
                href: clients.tasks(props.task.client?.id || 0).url,
            },
            {
                title: props.task.title,
                href: '#',
            }
        ];

        // If client is archived, insert archived clients breadcrumb
        if (props.task.client?.archived) {
            clientBreadcrumbs.splice(1, 0, {
                title: 'Archived Clients',
                href: clients.archived().url,
            });
        }

        return clientBreadcrumbs;
    }
});

// Format date for display
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Format status for display
const formatStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

// Get status badge variant
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending':
            return 'secondary';
        case 'review':
            return 'default';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

// Check if task is overdue
const isOverdue = (task: CoachingTask) => {
    if (!task.deadline) return false;
    const deadline = new Date(task.deadline);
    const now = new Date();
    return deadline < now && !['completed', 'cancelled'].includes(task.status);
};

// Check if task can be marked as complete
const canMarkComplete = computed(() => {
    return props.task.status !== 'completed' && props.task.status !== 'cancelled';
});

// Mark task as complete
const markAsComplete = async () => {
    const confirmed = await alertConfirm({
        title: 'Mark Task as Complete',
        description: `Are you sure you want to mark "${props.task.title}" as completed?`,
        confirmText: 'Mark as Complete',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(updateTaskStatus(props.task.id).url, {
            status: 'completed',
        }, {
            preserveScroll: true,
        });
    }
};


</script>
