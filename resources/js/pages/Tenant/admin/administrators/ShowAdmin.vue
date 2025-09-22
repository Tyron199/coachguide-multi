<template>

    <Head :title="`${admin.name} - Administrator Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdministratorsLayout>
            <div class="space-y-6">
                <PageHeader title="Administrator Details"
                    description="Overview of administrator information and platform statistics"
                    :badge="admin.archived ? 'Archived' : undefined"
                    :badge-variant="admin.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Unarchive Button -->
                        <Button v-if="admin.archived && can.update" variant="outline" @click="handleUnarchiveClick">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Administrator
                        </Button>

                        <!-- Archive Button (not for self) -->
                        <Button v-else-if="!admin.archived && can.update && !props.isSelf" variant="outline"
                            @click="handleArchiveClick">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Administrator
                        </Button>

                        <!-- Send Invitation Button -->
                        <Button v-if="can.update" variant="outline" @click="handleSendInvitation">
                            <Mail class="mr-2 h-4 w-4" />
                            Send Invitation
                        </Button>

                        <!-- Delete Button (only for archived admins with delete permission, not self) -->
                        <Button v-if="admin.archived && can.delete && !props.isSelf" variant="destructive"
                            @click="handleDeleteClick">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Administrator
                        </Button>

                        <Link v-if="can.update" :href="administrators.edit(admin.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Administrator
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
                                <p class="mt-1">{{ admin.name }}
                                    <span v-if="props.isSelf" class="ml-2 text-sm text-muted-foreground">(You)</span>
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                <p class="mt-1">{{ admin.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Phone Number</Label>
                                <p class="mt-1">{{ admin.phone || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <div class="mt-1">
                                    <Badge :variant="admin.status === 'active' ? 'default' : 'secondary'">
                                        {{ admin.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Member Since</Label>
                                <p class="mt-1">{{ formatDate(admin.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Statistics Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Platform Overview</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ stats.total_coaches }}</div>
                                <div class="text-sm text-muted-foreground">Total Coaches</div>
                                <div class="text-xs text-muted-foreground mt-1">{{ stats.active_coaches }} active</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ stats.total_clients }}</div>
                                <div class="text-sm text-muted-foreground">Total Clients</div>
                                <div class="text-xs text-muted-foreground mt-1">{{ stats.active_clients }} active</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ stats.total_admins }}</div>
                                <div class="text-sm text-muted-foreground">Total Administrators</div>
                                <div class="text-xs text-muted-foreground mt-1">{{ stats.active_admins }} active</div>
                            </div>
                        </div>
                    </div>

                    <!-- Administrator Privileges Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Administrator Privileges</h2>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Complete user management</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Platform configuration</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Subscription management</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Company management</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Full coaching oversight</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                <span>Theme and branding control</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdministratorsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AdministratorsLayout from '@/layouts/administrators/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import administrators from '@/routes/tenant/admin/administrators';
import {
    Edit,
    Archive,
    ArchiveRestore,
    Mail,
    Trash2
} from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { formatDate } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Administrator {
    id: number;
    name: string;
    email: string;
    phone?: string;
    archived: boolean;
    status: string;
    created_at: string;
}

interface Stats {
    total_coaches: number;
    active_coaches: number;
    total_clients: number;
    active_clients: number;
    total_admins: number;
    active_admins: number;
}

interface Props {
    admin: Administrator;
    stats: Stats;
    can: {
        update: boolean;
        delete: boolean;
    };
    isSelf: boolean;
}

const props = defineProps<Props>();

const handleSendInvitation = async () => {
    const confirmed = await alertConfirm({
        title: 'Send Invitation',
        description: 'Send an invitation email to this administrator?',
        confirmText: 'Send Invitation',
        variant: 'default'
    });

    if (confirmed) {
        router.post(administrators.invite(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleArchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Archive Administrator',
        description: 'Are you sure you want to archive this administrator? They will no longer have access to the platform.',
        confirmText: 'Archive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(administrators.archive(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleUnarchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Administrator',
        description: 'Are you sure you want to unarchive this administrator? They will regain access to the platform.',
        confirmText: 'Unarchive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(administrators.unarchive(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleDeleteClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Administrator',
        description: 'Are you sure you want to permanently delete this administrator? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(administrators.destroy(props.admin.id).url, {
            preserveScroll: true
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: 'Administrators', href: administrators.index().url },
    { title: props.admin.name, href: '#' },
];
</script>
