<template>

    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingTasksLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.tasks.total)} ${props.tasks.total === 1 ? 'task' : 'tasks'}`"
                    :badge-variant="'default'">
                    <template #actions>
                        <!-- Future: Bulk actions or create task button -->
                    </template>
                </PageHeader>

                <!-- Table View -->
                <TasksTable :tasks="props.tasks" :clients="props.clients" :filters="props.filters" />
            </div>
        </CoachingTasksLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingTasksLayout from '@/layouts/coaching-tasks/Layout.vue';
import TasksTable from '@/components/TasksTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import coachingTasks from '@/routes/tenant/coach/coaching-tasks';
import { computed } from 'vue';
import { formatNumber } from '@/lib/utils';

interface CoachingTask {
    id: number;
    title: string;
    description: string | null;
    client: {
        id: number;
        name: string;
    };
    session_id: number | null;
    deadline: string | null;
    status: 'pending' | 'in_progress' | 'review' | 'completed' | 'cancelled';
    evidence_required: boolean;
    completed_at: string | null;
    created_at: string;
}

interface PaginatedTasks {
    data: CoachingTask[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface TaskFilters {
    search?: string;
    client_id?: number | null;
    status?: string | null;
    evidence_required?: boolean | null;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    view: 'pending' | 'overdue' | 'review' | 'completed';
}

interface Props {
    tasks: PaginatedTasks;
    clients?: { id: number; name: string }[];
    filters: TaskFilters;
}

const props = defineProps<Props>();

const title = computed(() => {
    switch (props.filters.view) {
        case 'pending': return 'Pending Tasks';
        case 'overdue': return 'Overdue Tasks';
        case 'review': return 'Tasks in Review';
        case 'completed': return 'Completed Tasks';
        default: return 'Tasks';
    }
});

const description = computed(() => {
    switch (props.filters.view) {
        case 'pending': return 'Tasks that are pending or in progress';
        case 'overdue': return 'Tasks that are past their deadline';
        case 'review': return 'Tasks that require your review';
        case 'completed': return 'Tasks that have been completed';
        default: return 'Manage your coaching tasks';
    }
});

const breadcrumbs: BreadcrumbItem[] = [];

if (props.filters.view === 'pending') {
    breadcrumbs.push({
        title: 'Pending Tasks',
        href: coachingTasks.index().url
    });
} else if (props.filters.view === 'overdue') {
    breadcrumbs.push({
        title: 'Overdue Tasks',
        href: coachingTasks.overdue().url
    });
} else if (props.filters.view === 'review') {
    breadcrumbs.push({
        title: 'Tasks in Review',
        href: coachingTasks.review().url
    });
} else if (props.filters.view === 'completed') {
    breadcrumbs.push({
        title: 'Completed Tasks',
        href: coachingTasks.completed().url
    });
}
</script>
