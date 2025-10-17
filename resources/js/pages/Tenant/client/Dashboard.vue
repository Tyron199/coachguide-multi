<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CalendarConnectModal from '@/components/CalendarConnectModal.vue';
import { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { toast } from 'vue-sonner';
import {
    Calendar,
    CheckSquare,
    TrendingUp,
    Clock,
    Eye,
    ArrowRight,
    User
} from 'lucide-vue-next';

import { dashboard } from "@/routes/tenant"
import { show as showCalendarIntegration } from '@/actions/App/Http/Controllers/Tenant/Settings/CalendarIntegrationController';
import { index as sessionsIndex, show as sessionShow } from '@/actions/App/Http/Controllers/Tenant/Client/SessionController';
import { index as tasksIndex, show as taskShow } from '@/actions/App/Http/Controllers/Tenant/Client/TaskController';

interface Props {
    upcomingSessions: Array<{
        id: number;
        coach: { name: string };
        scheduled_at: string;
        duration: number;
        session_type: string;
    }>;
    outstandingTasks: Array<{
        id: number;
        title: string;
        description: string | null;
        deadline: string;
        status: string;
    }>;
    quickStats: {
        totalSessions: number;
        tasksDueThisWeek: number;
        tasksCompletedThisMonth: number;
    };
    assignedCoach: { name: string; email: string } | null;
    hasMicrosoftCalendar: boolean;
    hasGoogleCalendar: boolean;
    connectedProvider: string | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

function handleCalendarModalClose() {
    // Modal closed - no action needed
}

function handleCalendarModalNotNow() {
    toast("💡 No problem!", {
        description: "You can set up calendar integration from your profile settings anytime.",
        duration: 5000,
        action: {
            label: 'Go to Integrations',
            onClick: () => {
                window.location.href = showCalendarIntegration().url;
            },
        },
    });
}

const getSessionTypeLabel = (type: string) => {
    switch (type) {
        case 'online': return 'Online';
        case 'in_person': return 'In Person';
        case 'hybrid': return 'Hybrid';
        default: return type;
    }
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const isToday = (dateString: string) => {
    const date = new Date(dateString);
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

const isTomorrow = (dateString: string) => {
    const date = new Date(dateString);
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return date.toDateString() === tomorrow.toDateString();
};

const getDateLabel = (dateString: string) => {
    if (isToday(dateString)) return 'Today';
    if (isTomorrow(dateString)) return 'Tomorrow';
    return formatDate(dateString);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending': return 'outline' as const;
        case 'in_progress': return 'secondary' as const;
        case 'completed': return 'default' as const;
        default: return 'outline' as const;
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'in_progress': return 'In Progress';
        case 'completed': return 'Completed';
        case 'review': return 'In Review';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
};

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">

            <!-- Welcome Section -->
            <div class="flex flex-col gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Welcome back! 👋</h1>
                    <p class="text-muted-foreground">Here's an overview of your coaching journey.</p>
                </div>

                <!-- Coach Info Card -->
                <Card v-if="props.assignedCoach">
                    <CardContent>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <User class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Your Coach</p>
                                <p class="font-semibold">{{ props.assignedCoach.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ props.assignedCoach.email }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Sessions</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.quickStats.totalSessions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Completed sessions
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tasks Due This Week</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.quickStats.tasksDueThisWeek }}</div>
                        <p class="text-xs text-muted-foreground">
                            Requiring your attention
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tasks Completed</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.quickStats.tasksCompletedThisMonth }}</div>
                        <p class="text-xs text-muted-foreground">
                            This month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 md:grid-cols-2">

                <!-- Upcoming Sessions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Upcoming Sessions</CardTitle>
                                <CardDescription>Your next coaching sessions</CardDescription>
                            </div>
                            <Link :href="sessionsIndex().url">
                            <Button variant="outline" size="sm">
                                <Eye class="mr-2 h-4 w-4" />
                                View All
                            </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="props.upcomingSessions.length === 0" class="text-center text-muted-foreground py-8">
                            No upcoming sessions scheduled
                        </div>
                        <Link v-for="session in props.upcomingSessions" :key="session.id"
                            :href="sessionShow(session.id).url"
                            class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium">{{ session.coach.name }}</span>
                                <Badge variant="outline" class="text-xs">
                                    {{ getSessionTypeLabel(session.session_type) }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-3 w-3" />
                                    {{ getDateLabel(session.scheduled_at) }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ formatTime(session.scheduled_at) }}
                                </div>
                                <span>{{ session.duration }}min</span>
                            </div>
                        </div>
                        <ArrowRight class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </CardContent>
                </Card>

                <!-- Outstanding Tasks -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Outstanding Tasks</CardTitle>
                                <CardDescription>Tasks assigned to you</CardDescription>
                            </div>
                            <Link :href="tasksIndex().url">
                            <Button variant="outline" size="sm">
                                <Eye class="mr-2 h-4 w-4" />
                                View All
                            </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="props.outstandingTasks.length === 0" class="text-center text-muted-foreground py-8">
                            No outstanding tasks
                        </div>
                        <Link v-for="task in props.outstandingTasks" :key="task.id" :href="taskShow(task.id).url"
                            class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium">{{ task.title }}</span>
                                <Badge :variant="getStatusVariant(task.status)" class="text-xs">
                                    {{ getStatusLabel(task.status) }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <div v-if="task.deadline" class="flex items-center gap-1">
                                    <Calendar class="h-3 w-3" />
                                    Due {{ formatDate(task.deadline) }}
                                </div>
                                <div v-else>
                                    No deadline set
                                </div>
                            </div>
                        </div>
                        <ArrowRight class="h-4 w-4 text-muted-foreground" />
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Calendar Connect Modal -->
        <CalendarConnectModal :hasMicrosoftCalendar="props.hasMicrosoftCalendar"
            :hasGoogleCalendar="props.hasGoogleCalendar" :show="true" @close="handleCalendarModalClose"
            @notNow="handleCalendarModalNotNow" />
    </AppLayout>
</template>
