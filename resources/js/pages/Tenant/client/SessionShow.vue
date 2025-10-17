<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { BreadcrumbItem } from '@/types';
import { dashboard } from "@/routes/tenant";
import { index as sessionsIndex } from '@/actions/App/Http/Controllers/Tenant/Client/SessionController';
import { show as taskShow } from '@/actions/App/Http/Controllers/Tenant/Client/TaskController';
import { Calendar, Clock, User, CheckSquare, ChevronRight } from 'lucide-vue-next';

interface Task {
    id: number;
    title: string;
    description: string;
    deadline: string | null;
    status: string;
    evidence_required: boolean;
    is_overdue: boolean;
}

interface Session {
    id: number;
    session_number: number;
    coach: { id: number; name: string };
    scheduled_at: string;
    duration: number;
    formatted_duration: string;
    session_type: string;
    is_past: boolean;
    is_active: boolean;
    tasks: Task[];
}

interface Props {
    session: Session;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Sessions',
        href: sessionsIndex().url,
    },
    {
        title: `Session #${props.session.session_number}`,
        href: '#',
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getSessionTypeLabel = (type: string) => {
    switch (type) {
        case 'online': return 'Online';
        case 'in_person': return 'In Person';
        case 'hybrid': return 'Hybrid';
        default: return type;
    }
};

const getSessionTypeVariant = (type: string) => {
    switch (type) {
        case 'online': return 'default' as const;
        case 'in_person': return 'secondary' as const;
        case 'hybrid': return 'outline' as const;
        default: return 'outline' as const;
    }
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

const formatTaskDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<template>

    <Head :title="`Session #${session.session_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 max-w-4xl mx-auto">
            <!-- Session Details Card -->
            <Card>
                <CardHeader>
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <CardTitle class="text-2xl">Session #{{ session.session_number }}</CardTitle>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge :variant="getSessionTypeVariant(session.session_type)">
                                {{ getSessionTypeLabel(session.session_type) }}
                            </Badge>
                            <Badge v-if="session.is_active" variant="default" class="animate-pulse">
                                LIVE NOW
                            </Badge>
                            <Badge v-else-if="session.is_past" variant="outline">
                                Completed
                            </Badge>
                            <Badge v-else variant="secondary">
                                Upcoming
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Session Info Grid -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex items-start gap-2">
                            <Calendar class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Date</p>
                                <p class="text-sm text-muted-foreground">{{ formatDate(session.scheduled_at) }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <Clock class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Time</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatTime(session.scheduled_at) }} • {{ session.formatted_duration }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <User class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Coach</p>
                                <p class="text-sm text-muted-foreground">{{ session.coach.name }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Session Tasks</CardTitle>
                        <Badge variant="secondary">{{ session.tasks.length }}</Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="session.tasks.length === 0" class="text-center py-8">
                        <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                            <CheckSquare class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-medium mb-2">No tasks yet</h3>
                        <p class="text-muted-foreground">
                            Tasks assigned during this session will appear here.
                        </p>
                    </div>

                    <div v-else class="space-y-3">
                        <Link v-for="task in session.tasks" :key="task.id" :href="taskShow(task.id).url" class="block">
                        <Card class="hover:shadow-md transition-all hover:border-primary/50 cursor-pointer">
                            <CardContent class="pt-4 pb-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <!-- Task Title and Badges -->
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-semibold">{{ task.title }}</h4>
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
                                            <p v-if="task.description"
                                                class="text-sm text-muted-foreground line-clamp-2">
                                                {{ task.description }}
                                            </p>
                                        </div>

                                        <!-- Task Deadline -->
                                        <div v-if="task.deadline"
                                            class="flex items-center gap-1 text-sm text-muted-foreground">
                                            <Clock class="h-3 w-3 flex-shrink-0" />
                                            <span :class="{ 'text-destructive font-medium': task.is_overdue }">
                                                Due {{ formatTaskDate(task.deadline) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Arrow Icon -->
                                    <ChevronRight class="h-5 w-5 flex-shrink-0 text-muted-foreground" />
                                </div>
                            </CardContent>
                        </Card>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
