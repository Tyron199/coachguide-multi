<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { BreadcrumbItem } from '@/types';
import { dashboard } from "@/routes/tenant";
import { index as tasksIndex, show as taskShow } from '@/actions/App/Http/Controllers/Tenant/Client/TaskController';
import { CheckSquare, Clock, AlertCircle, Calendar, ChevronRight } from 'lucide-vue-next';

interface Task {
    id: number;
    title: string;
    description: string;
    deadline: string | null;
    status: string;
    evidence_required: boolean;
    created_at: string;
    completed_at: string | null;
    coach: { name: string } | null;
    session: { session_number: number; scheduled_at: string } | null;
    reminders_count: number;
    actions_count: number;
    is_overdue: boolean;
}

interface Props {
    tasks: Task[];
    filter: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Tasks',
        href: tasksIndex().url,
    },
];

const changeFilter = (filter: string) => {
    router.get(tasksIndex().url, { filter }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending': return 'outline' as const;
        case 'in_progress': return 'secondary' as const;
        case 'review': return 'default' as const;
        case 'completed': return 'default' as const;
        default: return 'outline' as const;
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'in_progress': return 'In Progress';
        case 'review': return 'Under Review';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
};

const pendingTasks = props.tasks.filter(t => ['pending', 'in_progress'].includes(t.status));
const completedTasks = props.tasks.filter(t => t.status === 'completed');
</script>

<template>

    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Page Header -->
            <div>
                <h1 class="text-3xl font-bold">My Tasks</h1>
                <p class="text-muted-foreground">View and complete your coaching tasks</p>
            </div>

            <!-- Filter Tabs -->
            <Tabs :default-value="props.filter" class="w-full">
                <TabsList class="grid w-full max-w-md grid-cols-3">
                    <TabsTrigger value="all" @click="changeFilter('all')">
                        All ({{ props.tasks.length }})
                    </TabsTrigger>
                    <TabsTrigger value="pending" @click="changeFilter('pending')">
                        Pending ({{ pendingTasks.length }})
                    </TabsTrigger>
                    <TabsTrigger value="completed" @click="changeFilter('completed')">
                        Completed ({{ completedTasks.length }})
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <!-- Tasks List -->
            <div v-if="props.tasks.length === 0" class="text-center py-12">
                <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                    <CheckSquare class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-medium mb-2">No tasks</h3>
                <p class="text-muted-foreground">
                    Tasks assigned by your coach will appear here.
                </p>
            </div>

            <div v-else class="space-y-3">
                <Link v-for="task in props.tasks" :key="task.id" :href="taskShow(task.id).url" class="block">
                <Card class="hover:shadow-md transition-all hover:border-primary/50 cursor-pointer">
                    <CardContent>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0 space-y-3">
                                <!-- Task Title and Badges -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-lg font-semibold">{{ task.title }}</h3>
                                        <Badge :variant="getStatusVariant(task.status)">
                                            {{ getStatusLabel(task.status) }}
                                        </Badge>
                                        <Badge v-if="task.evidence_required" variant="outline" class="text-xs">
                                            Evidence Required
                                        </Badge>
                                        <Badge v-if="task.is_overdue && task.status !== 'completed'"
                                            variant="destructive" class="text-xs">
                                            Overdue
                                        </Badge>
                                    </div>

                                    <!-- Description Preview -->
                                    <p v-if="task.description" class="text-sm text-muted-foreground line-clamp-2">
                                        {{ task.description }}
                                    </p>
                                </div>

                                <!-- Task Metadata -->
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                    <div v-if="task.deadline" class="flex items-center gap-1">
                                        <Clock class="h-3 w-3 flex-shrink-0" />
                                        <span :class="{ 'text-destructive font-medium': task.is_overdue }">
                                            Due {{ formatDate(task.deadline) }}
                                        </span>
                                    </div>

                                    <div v-if="task.coach" class="flex items-center gap-1">
                                        <span>Coach: {{ task.coach.name }}</span>
                                    </div>

                                    <div v-if="task.session" class="flex items-center gap-1">
                                        <Calendar class="h-3 w-3 flex-shrink-0" />
                                        <span>Session #{{ task.session.session_number }}</span>
                                    </div>

                                    <div v-if="task.actions_count > 0" class="flex items-center gap-1">
                                        <AlertCircle class="h-3 w-3 flex-shrink-0" />
                                        <span>{{ task.actions_count }} submission{{ task.actions_count === 1 ? '' : 's'
                                            }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Arrow Icon -->
                            <ChevronRight class="h-5 w-5 flex-shrink-0 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
