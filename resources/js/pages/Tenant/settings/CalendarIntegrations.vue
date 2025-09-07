<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, CheckCircle, ExternalLink } from 'lucide-vue-next';
import { calendarIntegrations } from '@/routes/tenant';
import calendarOauth from '@/routes/tenant/calendar/oauth';
import { alertConfirm } from '@/plugins/alert';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';

interface Props {
    hasMicrosoftCalendar: boolean;
    hasGoogleCalendar: boolean;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Calendar integrations',
        href: calendarIntegrations().url,
    },
];

const handleConnect = (provider: string) => {
    router.visit(calendarOauth.initiate(provider).url);
};

const handleDisconnect = async (provider: string) => {
    const confirmed = await alertConfirm({
        title: `Disconnect ${provider === 'microsoft' ? 'Microsoft' : 'Google'} Calendar`,
        description: 'Are you sure you want to disconnect this calendar? You will no longer be able to sync your coaching sessions.',
        confirmText: 'Disconnect',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(calendarOauth.disconnect(provider).url);
    }
};

const providers = [
    {
        name: 'Microsoft',
        key: 'microsoft',
        description: 'Connect your Outlook calendar to sync coaching sessions',
        logo: 'microsoft',
    },
    {
        name: 'Google',
        key: 'google',
        description: 'Connect your Google Calendar to sync coaching sessions',
        logo: 'google',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Calendar integrations" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Calendar integrations"
                    description="Connect your calendar to automatically sync coaching sessions and appointments" />

                <div class="grid gap-4 md:grid-cols-1">
                    <Card v-for="provider in providers" :key="provider.key" class="relative">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 flex items-center justify-center">
                                        <!-- Microsoft Logo -->
                                        <svg v-if="provider.logo === 'microsoft'" viewBox="0 0 21 21" class="w-6 h-6">
                                            <rect x="1" y="1" width="9" height="9" fill="#f25022" />
                                            <rect x="11" y="1" width="9" height="9" fill="#00a4ef" />
                                            <rect x="1" y="11" width="9" height="9" fill="#ffb900" />
                                            <rect x="11" y="11" width="9" height="9" fill="#7fba00" />
                                        </svg>
                                        <!-- Google Logo -->
                                        <svg v-else-if="provider.logo === 'google'" viewBox="0 0 24 24" class="w-6 h-6">
                                            <path fill="#4285F4"
                                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                            <path fill="#34A853"
                                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                            <path fill="#FBBC05"
                                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                            <path fill="#EA4335"
                                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <CardTitle class="text-lg">{{ provider.name }} Calendar</CardTitle>
                                        <CardDescription>{{ provider.description }}</CardDescription>
                                    </div>
                                </div>
                                <Badge
                                    v-if="(provider.key === 'microsoft' && hasMicrosoftCalendar) || (provider.key === 'google' && hasGoogleCalendar)"
                                    variant="secondary" class="bg-green-50 text-green-700 border-green-200">
                                    <CheckCircle class="w-3 h-3 mr-1" />
                                    Connected
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1 text-sm text-muted-foreground pr-4">
                                    <template
                                        v-if="(provider.key === 'microsoft' && hasMicrosoftCalendar) || (provider.key === 'google' && hasGoogleCalendar)">
                                        Your {{ provider.name }} calendar is connected and ready to sync coaching
                                        sessions.
                                    </template>
                                    <template v-else>
                                        Connect your {{ provider.name }} calendar to automatically sync coaching
                                        sessions.
                                    </template>
                                </div>
                                <div class="flex-shrink-0">
                                    <Button
                                        v-if="(provider.key === 'microsoft' && !hasMicrosoftCalendar) || (provider.key === 'google' && !hasGoogleCalendar)"
                                        @click="handleConnect(provider.key)" class="flex items-center space-x-2">
                                        <ExternalLink class="w-4 h-4" />
                                        <span>Connect</span>
                                    </Button>
                                    <Button v-else @click="handleDisconnect(provider.key)" variant="destructive"
                                        class="flex items-center space-x-2">
                                        <span>Disconnect</span>
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="mt-8 p-4 bg-muted/50 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <Calendar class="w-5 h-5 text-muted-foreground mt-0.5" />
                        <div>
                            <h4 class="text-sm font-medium">About calendar integrations</h4>
                            <p class="text-sm text-muted-foreground mt-1">
                                When you connect a calendar, your coaching sessions will automatically appear in your
                                calendar app.
                                This helps you stay organized and ensures you never miss a session with your clients.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
