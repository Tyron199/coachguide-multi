<template>

    <Head :title="`${coach.name} - Coach Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachesLayout>
            <div class="space-y-6">
                <PageHeader title="Coach Details" description="Overview of coach information and statistics"
                    :badge="coach.archived ? 'Archived' : undefined"
                    :badge-variant="coach.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Unarchive Button -->
                        <Button v-if="coach.archived && can.update" variant="outline" @click="handleUnarchiveClick">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Coach
                        </Button>

                        <!-- Archive Button -->
                        <Button v-else-if="!coach.archived && can.update" variant="outline" @click="handleArchiveClick">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Coach
                        </Button>

                        <!-- Send Invitation Button -->
                        <Button v-if="can.update" variant="outline" @click="handleSendInvitation">
                            <Mail class="mr-2 h-4 w-4" />
                            Send Invitation
                        </Button>

                        <!-- Delete Button (only for archived coaches with delete permission) -->
                        <Button v-if="coach.archived && can.delete" variant="destructive" @click="handleDeleteClick">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Coach
                        </Button>

                        <Link v-if="can.update" :href="coaches.edit(coach.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Coach
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <div class="grid gap-6">
                    <!-- Basic Information Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Basic Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Full Name</Label>
                                <p class="mt-1">{{ coach.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                <p class="mt-1">{{ coach.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Phone Number</Label>
                                <p class="mt-1">{{ coach.phone || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <div class="mt-1">
                                    <Badge :variant="coach.status === 'active' ? 'default' : 'secondary'">
                                        {{ coach.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Member Since</Label>
                                <p class="mt-1">{{ formatDate(coach.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Coach Statistics Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Coach Statistics</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ stats.total_clients }}</div>
                                <div class="text-sm text-muted-foreground">Total Clients</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ stats.active_clients }}</div>
                                <div class="text-sm text-muted-foreground">Active Clients</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ stats.archived_clients }}</div>
                                <div class="text-sm text-muted-foreground">Archived Clients</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ stats.total_sessions }}</div>
                                <div class="text-sm text-muted-foreground">Total Sessions</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ stats.upcoming_sessions }}</div>
                                <div class="text-sm text-muted-foreground">Upcoming Sessions</div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Clients Card -->
                    <div class="rounded-lg border bg-card p-6"
                        v-if="coach.assigned_clients && coach.assigned_clients.length > 0">
                        <h2 class="text-lg font-medium mb-4">Assigned Clients</h2>
                        <div class="space-y-3">
                            <div v-for="client in coach.assigned_clients" :key="client.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-sm font-medium text-primary">
                                            {{ client.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ client.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ client.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Badge :variant="client.archived ? 'destructive' : 'default'">
                                        {{ client.archived ? 'Archived' : 'Active' }}
                                    </Badge>
                                    <!-- Add a link to view client if needed -->
                                    <!-- <Button variant="outline" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Clients Card -->
                    <div class="rounded-lg border bg-card p-6" v-else>
                        <h2 class="text-lg font-medium mb-4">Assigned Clients</h2>
                        <div class="text-center py-8">
                            <Users class="mx-auto h-12 w-12 text-muted-foreground/50" />
                            <p class="mt-2 text-sm text-muted-foreground">This coach has no assigned clients yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </CoachesLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachesLayout from '@/layouts/coaches/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import coaches from '@/routes/tenant/admin/coaches';
import {
    Edit,
    Archive,
    ArchiveRestore,
    Mail,
    Trash2,
    Users
} from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { formatDate } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Client {
    id: number;
    name: string;
    email: string;
    archived: boolean;
}

interface Coach {
    id: number;
    name: string;
    email: string;
    phone?: string;
    archived: boolean;
    status: string;
    created_at: string;
    assigned_clients?: Client[];
}

interface Stats {
    total_clients: number;
    active_clients: number;
    archived_clients: number;
    total_sessions: number;
    upcoming_sessions: number;
}

interface Props {
    coach: Coach;
    stats: Stats;
    can: {
        update: boolean;
        delete: boolean;
    };
}

const props = defineProps<Props>();

const handleSendInvitation = async () => {
    const confirmed = await alertConfirm({
        title: 'Send Invitation',
        description: 'Send an invitation email to this coach?',
        confirmText: 'Send Invitation',
        variant: 'default'
    });

    if (confirmed) {
        router.post(coaches.invite(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleArchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Archive Coach',
        description: 'Are you sure you want to archive this coach? They will no longer have access to the platform.',
        confirmText: 'Archive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(coaches.archive(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleUnarchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Coach',
        description: 'Are you sure you want to unarchive this coach? They will regain access to the platform.',
        confirmText: 'Unarchive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(coaches.unarchive(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleDeleteClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Coach',
        description: 'Are you sure you want to permanently delete this coach? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(coaches.destroy(props.coach.id).url, {
            preserveScroll: true
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: 'Coaches', href: coaches.index().url },
    { title: props.coach.name, href: '#' },
];
</script>
