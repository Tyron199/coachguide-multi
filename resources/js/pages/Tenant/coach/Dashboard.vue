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
    Users,
    Calendar,
    CheckSquare,
    GraduationCap,
    TrendingUp,
    Clock,
    Plus,
    Eye,
    ArrowRight
} from 'lucide-vue-next';

import { dashboard } from "@/routes/tenant"
import { show as showCalendarIntegration } from '@/actions/App/Http/Controllers/Tenant/Settings/CalendarIntegrationController';
import { create as createSession } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import { create as createClient } from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { show as showSession, index as indexSessions } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import coachingTasks from '@/routes/tenant/coach/coaching-tasks';
import { show as showTask } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingTaskController';

interface Props {
    dashboardStats: {
        activeClients: number;
        upcomingSessions: {
            today: number;
            thisWeek: number;
        };
        outstandingActions: number;
        cpdHours: number;
        monthlyTarget: number;
    };
    upcomingSessions: Array<{
        id: number;
        client: { name: string; email: string };
        scheduled_at: string;
        duration: number;
        session_type: string;
        is_active: boolean;
    }>;
    outstandingActions: Array<{
        id: number;
        title: string;
        client: string;
        due_date: string;
        status: string;
    }>;
    quickStats: {
        sessionsCompleted: number;
        contractsSigned: number;
        completionRate: number;
    };
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
    // Show dashboard-specific toast since they're on the dashboard
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
                    <div class="relative flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                        <div class="space-y-2">
                            <h1
                                class="text-4xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">
                                Welcome back! 👋
                            </h1>
                            <p class="text-lg text-muted-foreground max-w-2xl">
                                Here's what's happening with your coaching practice today.
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <Link :href="createClient().url">
                            <Button
                                class="h-11 px-6 shadow-lg transition-all duration-200 hover:shadow-xl hover:scale-105">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Client
                            </Button>
                            </Link>
                            <Link :href="createSession().url">
                            <Button variant="outline"
                                class="h-11 px-6 border-2 transition-all duration-200 hover:shadow-lg hover:scale-105">
                                <Plus class="mr-2 h-4 w-4" />
                                Schedule Session
                            </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-950/30 dark:to-blue-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-blue-700 dark:text-blue-300">Active Clients
                            </CardTitle>
                            <div class="rounded-full bg-blue-500/20 p-2">
                                <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{
                                props.dashboardStats.activeClients }}</div>
                            <p class="text-sm text-blue-600/80 dark:text-blue-300/80 mt-1">
                                Total active clients
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-green-50 to-green-100/50 dark:from-green-950/30 dark:to-green-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-green-700 dark:text-green-300">Sessions This
                                Week</CardTitle>
                            <div class="rounded-full bg-green-500/20 p-2">
                                <Calendar class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-green-900 dark:text-green-100">{{
                                props.dashboardStats.upcomingSessions.thisWeek }}</div>
                            <p class="text-sm text-green-600/80 dark:text-green-300/80 mt-1">
                                {{ props.dashboardStats.upcomingSessions.today }} scheduled today
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-950/30 dark:to-orange-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-orange-700 dark:text-orange-300">Outstanding
                                Actions</CardTitle>
                            <div class="rounded-full bg-orange-500/20 p-2">
                                <CheckSquare class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{
                                props.dashboardStats.outstandingActions }}</div>
                            <p class="text-sm text-orange-600/80 dark:text-orange-300/80 mt-1">
                                Tasks pending or in progress
                            </p>
                        </CardContent>
                    </Card>

                    <Card
                        class="group relative overflow-hidden border-0 bg-gradient-to-br from-purple-50 to-purple-100/50 dark:from-purple-950/30 dark:to-purple-900/20 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        </div>
                        <CardHeader class="relative flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle class="text-sm font-semibold text-purple-700 dark:text-purple-300">CPD Hours
                            </CardTitle>
                            <div class="rounded-full bg-purple-500/20 p-2">
                                <GraduationCap class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{
                                props.dashboardStats.cpdHours }}h</div>
                            <p class="text-sm text-purple-600/80 dark:text-purple-300/80 mt-1">
                                {{ Math.max(0, props.dashboardStats.monthlyTarget - props.dashboardStats.cpdHours) }}h
                                remaining this month
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
                                        <div class="rounded-full bg-green-500/20 p-2">
                                            <Calendar class="h-5 w-5 text-green-600 dark:text-green-400" />
                                        </div>
                                        Upcoming Sessions
                                    </CardTitle>
                                    <CardDescription class="text-base">Your next coaching appointments</CardDescription>
                                </div>
                                <Link :href="indexSessions().url">
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
                                <p class="text-sm">Schedule your first session to get started</p>
                            </div>
                            <div v-for="session in props.upcomingSessions" :key="session.id"
                                class="group relative overflow-hidden rounded-xl border transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                                :class="{
                                    'border-green-500 bg-gradient-to-r from-green-50 to-green-100/50 dark:from-green-950/30 dark:to-green-900/20 shadow-lg': session.is_active,
                                    'border-border bg-card hover:border-primary/50': !session.is_active
                                }">
                                <div v-if="session.is_active"
                                    class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-transparent"></div>
                                <div class="relative flex items-center justify-between p-4">
                                    <div class="flex-1 space-y-2">
                                        <div class="flex items-center gap-3">
                                            <span class="font-semibold text-lg">{{ session.client.name }}</span>
                                            <Badge v-if="session.is_active" variant="default"
                                                class="text-xs animate-pulse bg-green-500 hover:bg-green-600">
                                                LIVE
                                            </Badge>
                                            <Badge variant="outline" class="text-xs border-primary/50 text-primary">
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
                                    <Link :href="showSession(session.id).url">
                                    <Button variant="ghost" size="sm"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <ArrowRight class="h-4 w-4" />
                                    </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Outstanding Actions -->
                    <Card class="border-0 shadow-xl bg-gradient-to-br from-background to-muted/30 backdrop-blur-sm">
                        <CardHeader class="pb-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <CardTitle class="text-xl font-bold flex items-center gap-2">
                                        <div class="rounded-full bg-orange-500/20 p-2">
                                            <CheckSquare class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                        </div>
                                        Outstanding Actions
                                    </CardTitle>
                                    <CardDescription class="text-base">Tasks requiring attention</CardDescription>
                                </div>
                                <Link :href="coachingTasks.index().url">
                                <Button variant="outline" size="sm"
                                    class="shadow-md hover:shadow-lg transition-all duration-200">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View All
                                </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="props.outstandingActions.length === 0"
                                class="text-center text-muted-foreground py-12">
                                <CheckSquare class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p class="text-lg font-medium">No outstanding actions</p>
                                <p class="text-sm">All caught up! Great work</p>
                            </div>
                            <div v-for="action in props.outstandingActions" :key="action.id"
                                class="group relative overflow-hidden rounded-xl border transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                                :class="{
                                    'border-orange-500 bg-gradient-to-r from-orange-50 to-orange-100/50 dark:from-orange-950/30 dark:to-orange-900/20 shadow-lg': action.status === 'review',
                                    'border-border bg-card hover:border-primary/50': action.status !== 'review'
                                }">
                                <div v-if="action.status === 'review'"
                                    class="absolute inset-0 bg-gradient-to-r from-orange-500/10 to-transparent"></div>
                                <div class="relative flex items-center justify-between p-4">
                                    <div class="flex-1 space-y-2">
                                        <div class="flex items-center gap-3">
                                            <span class="font-semibold text-lg">{{ action.title }}</span>
                                            <Badge v-if="action.status === 'review'" variant="default"
                                                class="text-xs bg-orange-500 hover:bg-orange-600 animate-pulse">
                                                NEEDS REVIEW
                                            </Badge>
                                        </div>
                                        <div class="flex items-center gap-6 text-sm text-muted-foreground">
                                            <span class="font-medium">{{ action.client }}</span>
                                            <div class="flex items-center gap-2">
                                                <Calendar class="h-4 w-4" />
                                                Due {{ formatDate(action.due_date) }}
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="showTask(action.id).url">
                                    <Button variant="ghost" size="sm"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <ArrowRight class="h-4 w-4" />
                                    </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Stats -->
                <Card class="border-0 shadow-xl bg-gradient-to-br from-background to-muted/30 backdrop-blur-sm">
                    <CardHeader class="pb-6">
                        <CardTitle class="text-2xl font-bold flex items-center gap-3">
                            <div class="rounded-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 p-3">
                                <TrendingUp class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            Quick Stats - This Month
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-3">
                            <div
                                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-950/30 dark:to-blue-900/20 p-6 text-center transition-all duration-300 hover:shadow-lg hover:scale-105">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{
                                        props.quickStats.sessionsCompleted }}</div>
                                    <div class="text-sm font-medium text-blue-700 dark:text-blue-300">Sessions Completed
                                    </div>
                                </div>
                            </div>
                            <div
                                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-50 to-green-100/50 dark:from-green-950/30 dark:to-green-900/20 p-6 text-center transition-all duration-300 hover:shadow-lg hover:scale-105">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">{{
                                        props.quickStats.contractsSigned }}</div>
                                    <div class="text-sm font-medium text-green-700 dark:text-green-300">New Contracts
                                        Signed</div>
                                </div>
                            </div>
                            <div
                                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-50 to-purple-100/50 dark:from-purple-950/30 dark:to-purple-900/20 p-6 text-center transition-all duration-300 hover:shadow-lg hover:scale-105">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">{{
                                        props.quickStats.completionRate }}%</div>
                                    <div class="text-sm font-medium text-purple-700 dark:text-purple-300">Action
                                        Completion Rate</div>
                                </div>
                            </div>
                        </div>
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
