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

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger
} from '@/components/ui/alert-dialog';
import { dashboard } from "@/routes/tenant"
import { show as showCalendarIntegration } from '@/actions/App/Http/Controllers/Tenant/Settings/CalendarIntegrationController';
import { create as createSession } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import { create as createClient } from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { show as showSession, index as indexSessions } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';

interface Props {
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

// Dummy data - will be replaced with real data from backend
const dashboardStats = {
    activeClients: 24,
    upcomingSessions: {
        today: 3,
        thisWeek: 12
    },
    outstandingActions: 8,
    cpdHours: 6.5,
    monthlyTarget: 10,
    quickStats: {
        sessionsCompleted: 18,
        contractsSigned: 3,
        completionRate: 92
    }
};

const upcomingSessionsData = [
    {
        id: 1,
        client: { name: 'Sarah Johnson', email: 'sarah@example.com' },
        scheduled_at: new Date(2024, 11, 19, 10, 0),
        duration: 60,
        session_type: 'online'
    },
    {
        id: 2,
        client: { name: 'Michael Chen', email: 'michael@example.com' },
        scheduled_at: new Date(2024, 11, 19, 14, 30),
        duration: 45,
        session_type: 'in_person'
    },
    {
        id: 3,
        client: { name: 'Emma Wilson', email: 'emma@example.com' },
        scheduled_at: new Date(2024, 11, 20, 9, 0),
        duration: 90,
        session_type: 'hybrid'
    }
];

const outstandingActionsData = [
    {
        id: 1,
        title: 'Complete career assessment worksheet',
        client: 'Sarah Johnson',
        due_date: new Date(2024, 11, 20),
        priority: 'high'
    },
    {
        id: 2,
        title: 'Review quarterly goals',
        client: 'Michael Chen',
        due_date: new Date(2024, 11, 22),
        priority: 'medium'
    },
    {
        id: 3,
        title: 'Prepare presentation materials',
        client: 'Emma Wilson',
        due_date: new Date(2024, 11, 25),
        priority: 'low'
    }
];

const getSessionTypeLabel = (type: string) => {
    switch (type) {
        case 'online': return 'Online';
        case 'in_person': return 'In Person';
        case 'hybrid': return 'Hybrid';
        default: return type;
    }
};

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'high': return 'destructive' as const;
        case 'medium': return 'secondary' as const;
        case 'low': return 'outline' as const;
        default: return 'outline' as const;
    }
};

const formatTime = (date: Date) => {
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const isToday = (date: Date) => {
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

const isTomorrow = (date: Date) => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return date.toDateString() === tomorrow.toDateString();
};

const getDateLabel = (date: Date) => {
    if (isToday(date)) return 'Today';
    if (isTomorrow(date)) return 'Tomorrow';
    return formatDate(date);
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
                        <div class="text-2xl font-bold">{{ dashboardStats.activeClients }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-green-600">+2</span> from last month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sessions This Week</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.upcomingSessions.thisWeek }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ dashboardStats.upcomingSessions.today }} scheduled today
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Outstanding Actions</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.outstandingActions }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-amber-600">3 due this week</span>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">CPD Hours</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.cpdHours }}h</div>
                        <p class="text-xs text-muted-foreground">
                            {{ dashboardStats.monthlyTarget - dashboardStats.cpdHours }}h remaining this month
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
                        <div v-for="session in upcomingSessionsData" :key="session.id"
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
                            <Button variant="outline" size="sm">
                                <Eye class="mr-2 h-4 w-4" />
                                View All
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-for="action in outstandingActionsData" :key="action.id"
                            class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium">{{ action.title }}</span>
                                    <Badge :variant="getPriorityVariant(action.priority)" class="text-xs">
                                        {{ action.priority }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>{{ action.client }}</span>
                                    <div class="flex items-center gap-1">
                                        <Calendar class="h-3 w-3" />
                                        Due {{ formatDate(action.due_date) }}
                                    </div>
                                </div>
                            </div>
                            <Button variant="ghost" size="sm">
                                <ArrowRight class="h-4 w-4" />
                            </Button>
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
                            <div class="text-3xl font-bold text-blue-600">{{ dashboardStats.quickStats.sessionsCompleted
                            }}</div>
                            <div class="text-sm text-muted-foreground">Sessions Completed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ dashboardStats.quickStats.contractsSigned
                            }}</div>
                            <div class="text-sm text-muted-foreground">New Contracts Signed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ dashboardStats.quickStats.completionRate
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
