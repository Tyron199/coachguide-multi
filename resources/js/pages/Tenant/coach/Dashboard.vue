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
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">

            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Welcome back! 👋</h1>
                    <p class="text-muted-foreground">Here's what's happening with your coaching practice today.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="createClient().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Client
                    </Button>
                    </Link>
                    <Link :href="createSession().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Schedule Session
                    </Button>
                    </Link>
                </div>

            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardStats.activeClients }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total active clients
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sessions This Week</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardStats.upcomingSessions.thisWeek }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ props.dashboardStats.upcomingSessions.today }} scheduled today
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Outstanding Actions</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardStats.outstandingActions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Tasks pending or in progress
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">CPD Hours</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardStats.cpdHours }}h</div>
                        <p class="text-xs text-muted-foreground">
                            {{ Math.max(0, props.dashboardStats.monthlyTarget - props.dashboardStats.cpdHours) }}h
                            remaining this month
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
                                <CardDescription>Your next coaching appointments</CardDescription>
                            </div>
                            <Link :href="indexSessions().url">
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
                        <div v-for="session in props.upcomingSessions" :key="session.id"
                            class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium">{{ session.client.name }}</span>
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
                            <Link :href="showSession(session.id).url">
                            <Button variant="ghost" size="sm">
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Outstanding Actions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Outstanding Actions</CardTitle>
                                <CardDescription>Tasks requiring attention</CardDescription>
                            </div>
                            <Link :href="coachingTasks.index().url">
                            <Button variant="outline" size="sm">
                                <Eye class="mr-2 h-4 w-4" />
                                View All
                            </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="props.outstandingActions.length === 0"
                            class="text-center text-muted-foreground py-8">
                            No outstanding actions
                        </div>
                        <div v-for="action in props.outstandingActions" :key="action.id"
                            class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium">{{ action.title }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>{{ action.client }}</span>
                                    <div class="flex items-center gap-1">
                                        <Calendar class="h-3 w-3" />
                                        Due {{ formatDate(action.due_date) }}
                                    </div>
                                </div>
                            </div>
                            <Link :href="showTask(action.id).url">
                            <Button variant="ghost" size="sm">
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Stats -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5" />
                        Quick Stats - This Month
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ props.quickStats.sessionsCompleted
                            }}</div>
                            <div class="text-sm text-muted-foreground">Sessions Completed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ props.quickStats.contractsSigned
                            }}</div>
                            <div class="text-sm text-muted-foreground">New Contracts Signed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ props.quickStats.completionRate
                            }}%</div>
                            <div class="text-sm text-muted-foreground">Action Completion Rate</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Calendar Connect Modal -->
        <CalendarConnectModal :hasMicrosoftCalendar="props.hasMicrosoftCalendar"
            :hasGoogleCalendar="props.hasGoogleCalendar" :show="true" @close="handleCalendarModalClose"
            @notNow="handleCalendarModalNotNow" />
    </AppLayout>
</template>
