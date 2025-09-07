<template>
    <div class="space-y-4">
        <!-- Empty state -->
        <div v-if="tasks.length === 0" class="text-center py-12">
            <CheckSquare class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-foreground">No tasks yet</h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Tasks created during coaching sessions will appear here.
            </p>
        </div>

        <!-- Tasks list -->
        <div v-else class="space-y-4">
            <div v-for="task in tasks" :key="task.id"
                class="rounded-lg border bg-card p-4 sm:p-6 hover:shadow-sm transition-shadow">
                <!-- Task header -->
                <div class="space-y-3 sm:space-y-0 sm:flex sm:items-start sm:justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <h3 class="text-lg font-medium text-foreground break-words">
                                <Link :href="taskRoutes.show(task.id).url"
                                    class="text-left hover:text-primary transition-colors hover:underline">
                                {{ task.title }}
                                </Link>
                            </h3>
                            <Badge :variant="getStatusVariant(task.status)">
                                {{ formatStatus(task.status) }}
                            </Badge>
                            <Badge v-if="task.evidence_required" variant="outline" class="text-xs">
                                Evidence Required
                            </Badge>
                            <Badge v-if="isOverdue(task)" variant="destructive" class="text-xs">
                                Overdue
                            </Badge>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-muted-foreground">
                            <span>Created {{ formatDate(task.created_at) }}</span>
                            <span v-if="task.deadline" class="flex items-center gap-1">
                                <Clock class="h-3 w-3 flex-shrink-0" />
                                <span>Due: {{ formatDate(task.deadline) }}</span>
                            </span>
                            <span v-if="task.reminders && task.reminders.length > 0" class="flex items-center gap-1">
                                <Bell class="h-3 w-3 flex-shrink-0" />
                                <span>{{ task.reminders.length }} reminder{{ task.reminders.length === 1 ? '' : 's'
                                }}</span>
                            </span>
                            <span v-if="task.actions_count && task.actions_count > 0" class="flex items-center gap-1">
                                <MessageSquare class="h-3 w-3 flex-shrink-0" />
                                <span>{{ task.actions_count }} submission{{ task.actions_count === 1 ? '' : 's'
                                }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:ml-4">
                        <Button variant="ghost" size="sm" @click="toggleExpanded(task.id)" class="min-w-0 px-2">
                            <ChevronDown class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': expandedTasks.has(task.id) }" />
                        </Button>
                        <Button variant="ghost" size="sm" as-child class="flex-shrink-0">
                            <Link :href="taskRoutes.show(task.id).url">
                            <Eye class="mr-1 h-3 w-3" />
                            <span class="hidden xs:inline">View</span>
                            </Link>
                        </Button>
                        <Button v-if="canEdit" variant="outline" size="sm" as-child class="flex-shrink-0">
                            <Link :href="taskRoutes.edit(task.id).url">
                            <Edit class="mr-1 h-3 w-3" />
                            <span class="hidden xs:inline">Edit</span>
                            </Link>
                        </Button>
                        <Button v-if="canDelete" variant="destructive" size="sm" @click="deleteTask(task)"
                            class="flex-shrink-0">
                            <Trash2 class="mr-1 h-3 w-3" />
                            <span class="hidden xs:inline">Delete</span>
                        </Button>
                    </div>
                </div>

                <!-- Task description -->
                <div class="max-w-none">
                    <div v-if="!expandedTasks.has(task.id)" class="text-muted-foreground break-words">
                        {{ truncateContent(task.description) }}
                        <button v-if="task.description.length > contentPreviewLength" @click="toggleExpanded(task.id)"
                            class="text-primary hover:underline ml-1 text-sm font-medium">
                            Show more
                        </button>
                    </div>
                    <div v-else class="text-foreground whitespace-pre-wrap break-words">
                        {{ task.description }}
                        <button @click="toggleExpanded(task.id)"
                            class="text-primary hover:underline ml-2 text-sm font-medium">
                            Show less
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type CoachingTask } from '@/types';
import { CheckSquare, Clock, Bell, MessageSquare, ChevronDown, Edit, Trash2, Eye } from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';
import taskRoutes from '@/routes/tenant/coaching-tasks';

interface Props {
    tasks: CoachingTask[];
    canEdit?: boolean;
    canDelete?: boolean;
}

withDefaults(defineProps<Props>(), {
    canEdit: true,
    canDelete: true
});

// State for expanded tasks
const expandedTasks = ref(new Set<number>());
const contentPreviewLength = 300;

// Toggle expanded state for a task
const toggleExpanded = (taskId: number) => {
    if (expandedTasks.value.has(taskId)) {
        expandedTasks.value.delete(taskId);
    } else {
        expandedTasks.value.add(taskId);
    }
};

// Truncate content for preview
const truncateContent = (content: string) => {
    if (content.length <= contentPreviewLength) {
        return content;
    }
    return content.substring(0, contentPreviewLength) + '...';
};

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



// Handle delete task
const deleteTask = async (task: CoachingTask) => {
    const confirmed = await alertConfirm({
        title: 'Delete Task',
        description: `Are you sure you want to delete "${task.title}"? This action cannot be undone.`,
        confirmText: 'Delete Task',
        variant: 'destructive'
    });

    if (confirmed) {
        router.visit(taskRoutes.destroy(task.id), {
            preserveScroll: true,
        });
    }
};
</script>
