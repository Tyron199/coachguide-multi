<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { BreadcrumbItem } from '@/types';
import { dashboard } from "@/routes/tenant";
import { index as sessionsIndex, show as sessionShow } from '@/actions/App/Http/Controllers/Tenant/Client/SessionController';
import { Calendar, Clock, User } from 'lucide-vue-next';

interface Session {
    id: number;
    session_number: number;
    coach: { name: string };
    scheduled_at: string;
    duration: number;
    formatted_duration: string;
    session_type: string;
    is_past: boolean;
}

interface Props {
    upcomingSessions: Session[];
    pastSessions: Session[];
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
</script>

<template>

    <Head title="Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Page Header -->
            <div>
                <h1 class="text-3xl font-bold">My Sessions</h1>
                <p class="text-muted-foreground">View your coaching sessions</p>
            </div>

            <!-- Upcoming Sessions -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Upcoming Sessions</h2>
                    <Badge variant="secondary">{{ props.upcomingSessions.length }}</Badge>
                </div>

                <div v-if="props.upcomingSessions.length === 0" class="text-center py-12">
                    <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                        <Calendar class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium mb-2">No upcoming sessions</h3>
                    <p class="text-muted-foreground">
                        Your coach will schedule sessions with you.
                    </p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="session in props.upcomingSessions" :key="session.id"
                        :href="sessionShow(session.id).url">
                    <Card class="hover:shadow-md transition-shadow cursor-pointer hover:border-primary/50">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">Session #{{ session.session_number }}</CardTitle>
                                <Badge :variant="getSessionTypeVariant(session.session_type)">
                                    {{ getSessionTypeLabel(session.session_type) }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-start gap-2 text-sm">
                                <Calendar class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                                <span>{{ formatDate(session.scheduled_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <Clock class="h-4 w-4 flex-shrink-0 text-muted-foreground" />
                                <span>{{ formatTime(session.scheduled_at) }} • {{ session.formatted_duration }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <User class="h-4 w-4 flex-shrink-0 text-muted-foreground" />
                                <span>{{ session.coach.name }}</span>
                            </div>
                        </CardContent>
                    </Card>
                    </Link>
                </div>
            </div>

            <!-- Past Sessions -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Past Sessions</h2>
                    <Badge variant="secondary">{{ props.pastSessions.length }}</Badge>
                </div>

                <div v-if="props.pastSessions.length === 0" class="text-center py-12">
                    <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                        <Calendar class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium mb-2">No past sessions</h3>
                    <p class="text-muted-foreground">
                        Completed sessions will appear here.
                    </p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="session in props.pastSessions" :key="session.id" :href="sessionShow(session.id).url">
                    <Card
                        class="opacity-75 hover:opacity-100 transition-opacity cursor-pointer hover:border-primary/50">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">Session #{{ session.session_number }}</CardTitle>
                                <Badge :variant="getSessionTypeVariant(session.session_type)">
                                    {{ getSessionTypeLabel(session.session_type) }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-start gap-2 text-sm">
                                <Calendar class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                                <span>{{ formatDate(session.scheduled_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <Clock class="h-4 w-4 flex-shrink-0 text-muted-foreground" />
                                <span>{{ formatTime(session.scheduled_at) }} • {{ session.formatted_duration }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <User class="h-4 w-4 flex-shrink-0 text-muted-foreground" />
                                <span>{{ session.coach.name }}</span>
                            </div>
                        </CardContent>
                    </Card>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
