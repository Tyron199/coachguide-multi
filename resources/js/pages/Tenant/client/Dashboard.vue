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
        sessionsToday: number;
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
        <div class="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
            <div class="flex h-full flex-1 flex-col gap-8 rounded-xl p-6">

                <!-- Welcome Section -->
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-8 backdrop-blur-sm">
                    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
                    <div class="relative space-y-6">
                        <div class="space-y-2">
                            <h1
                                class="text-4xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">
                                Welcome back! 👋
                            </h1>
                            <p class="text-lg text-muted-foreground max-w-2xl">
                                Here's an overview of your coaching journey.
                            </p>
                        </div>

                        <!-- Coach Info Card -->
                        <Card v-if="props.assignedCoach"
                            class="border-0 bg-gradient-to-r from-blue-50 to-blue-100/50 dark:from-blue-950/30 dark:to-blue-900/20 shadow-lg">
                            <CardContent class="p-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-r from-blue-500/20 to-blue-600/20 shadow-lg">
                                        <User class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Your Coach</p>
                                        <p class="text-xl font-bold text-blue-900 dark:text-blue-100">{{
                                            props.assignedCoach.name }}</p>
                                        <p class="text-sm text-blue-600/80 dark:text-blue-300/80">{{
                                            props.assignedCoach.email }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-950/30 dark:to-emerald-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Completed
                                Sessions</CardTitle>
                            <div class="rounded-full bg-emerald-500/20 p-2">
                                <Calendar class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{
                                props.quickStats.totalSessions }}</div>
                            <p class="text-sm text-emerald-600/80 dark:text-emerald-300/80 mt-1">
                                {{ props.quickStats.sessionsToday }} scheduled today
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-950/30 dark:to-orange-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-orange-700 dark:text-orange-300">Tasks Due This
                                Week</CardTitle>
                            <div class="rounded-full bg-orange-500/20 p-2">
                                <CheckSquare class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{
                                props.quickStats.tasksDueThisWeek }}</div>
                            <p class="text-sm text-orange-600/80 dark:text-orange-300/80 mt-1">
                                Requiring your attention
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-purple-50 to-purple-100/50 dark:from-purple-950/30 dark:to-purple-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-purple-700 dark:text-purple-300">Tasks
                                Completed</CardTitle>
                            <div class="rounded-full bg-purple-500/20 p-2">
                                <TrendingUp class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{
                                props.quickStats.tasksCompletedThisMonth }}</div>
                            <p class="text-sm text-purple-600/80 dark:text-purple-300/80 mt-1">
                                This month
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content Grid -->
                <div class="grid gap-8 lg:grid-cols-2">

                    <!-- Upcoming Sessions -->
                    <Card class="border-0 shadow-xl bg-gradient-to-br from-background to-muted/30 backdrop-blur-sm">
                        <CardHeader class="pb-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                                        <div class="rounded-full bg-emerald-500/20 p-2">
                                            <Calendar class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        Upcoming Sessions
                                    </CardTitle>
                                    <CardDescription class="text-base">Your next coaching sessions</CardDescription>
                                </div>
                                <Link :href="sessionsIndex().url">
                                <Button variant="outline" size="sm"
                                    class="shadow-md hover:shadow-lg transition-all duration-200">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View All
                                </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="props.upcomingSessions.length === 0"
                                class="text-center text-muted-foreground py-12">
                                <Calendar class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p class="text-lg font-medium">No upcoming sessions scheduled</p>
                                <p class="text-sm">Your coach will schedule sessions with you</p>
                            </div>
                            <Link v-for="session in props.upcomingSessions" :key="session.id"
                                :href="sessionShow(session.id).url"
                                class="group relative overflow-hidden rounded-xl border border-border bg-card transition-all duration-300 hover:shadow-lg hover:scale-[1.02] hover:border-emerald-500/50">
                            <div class="flex items-center justify-between p-4">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-lg">{{ session.coach.name }}</span>
                                        <Badge variant="outline"
                                            class="text-xs border-emerald-500/50 text-emerald-600 dark:text-emerald-400">
                                            {{ getSessionTypeLabel(session.session_type) }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-6 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="h-4 w-4" />
                                            {{ getDateLabel(session.scheduled_at) }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-4 w-4" />
                                            {{ formatTime(session.scheduled_at) }}
                                        </div>
                                        <span class="font-medium">{{ session.duration }}min</span>
                                    </div>
                                </div>
                                <ArrowRight
                                    class="h-4 w-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                            </div>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Outstanding Tasks -->
                    <Card class="border-0 shadow-xl bg-gradient-to-br from-background to-muted/30 backdrop-blur-sm">
                        <CardHeader class="pb-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                                        <div class="rounded-full bg-orange-500/20 p-2">
                                            <CheckSquare class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                        </div>
                                        Outstanding Tasks
                                    </CardTitle>
                                    <CardDescription class="text-base">Tasks assigned to you</CardDescription>
                                </div>
                                <Link :href="tasksIndex().url">
                                <Button variant="outline" size="sm"
                                    class="shadow-md hover:shadow-lg transition-all duration-200">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View All
                                </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="props.outstandingTasks.length === 0"
                                class="text-center text-muted-foreground py-12">
                                <CheckSquare class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p class="text-lg font-medium">No outstanding tasks</p>
                                <p class="text-sm">All caught up! Great work</p>
                            </div>
                            <Link v-for="task in props.outstandingTasks" :key="task.id" :href="taskShow(task.id).url"
                                class="group relative overflow-hidden rounded-xl border border-border bg-card transition-all duration-300 hover:shadow-lg hover:scale-[1.02] hover:border-orange-500/50">
                            <div class="flex items-center justify-between p-4">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-lg">{{ task.title }}</span>
                                        <Badge :variant="getStatusVariant(task.status)" class="text-xs" :class="{
                                            'border-orange-500/50 text-orange-600 dark:text-orange-400': task.status === 'pending',
                                            'border-blue-500/50 text-blue-600 dark:text-blue-400': task.status === 'in_progress',
                                            'border-green-500/50 text-green-600 dark:text-green-400': task.status === 'completed'
                                        }">
                                            {{ getStatusLabel(task.status) }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-6 text-sm text-muted-foreground">
                                        <div v-if="task.deadline" class="flex items-center gap-2">
                                            <Calendar class="h-4 w-4" />
                                            Due {{ formatDate(task.deadline) }}
                                        </div>
                                        <div v-else class="text-muted-foreground/60">
                                            No deadline set
                                        </div>
                                    </div>
                                </div>
                                <ArrowRight
                                    class="h-4 w-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                            </div>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Calendar Connect Modal -->
        <CalendarConnectModal :hasMicrosoftCalendar="props.hasMicrosoftCalendar"
            :hasGoogleCalendar="props.hasGoogleCalendar" :show="true" @close="handleCalendarModalClose"
            @notNow="handleCalendarModalNotNow" />
    </AppLayout>
</template>
