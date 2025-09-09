<template>

    <Head :title="`${client.name} - Client Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Details" description="Overview of client information and profile"
                    :badge="client.archived ? 'Archived' : undefined"
                    :badge-variant="client.archived ? 'destructive' : 'default'">
                    <template #actions>
                        <!-- Unarchive Button -->
                        <Button v-if="client.archived && can.update" variant="outline" @click="handleUnarchiveClick">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Unarchive Client
                        </Button>

                        <!-- Archive Button -->
                        <Button v-else-if="!client.archived && can.update" variant="outline"
                            @click="handleArchiveClick">
                            <Archive class="mr-2 h-4 w-4" />
                            Archive Client
                        </Button>

                        <!-- Schedule Session Button -->
                        <Link :href="createSession({ query: { client_id: client.id } }).url">
                        <Button v-if="can.update" variant="outline">
                            <Calendar class="mr-2 h-4 w-4" />
                            Schedule Session
                        </Button>
                        </Link>
                        <!-- Delete Button (only for archived clients with delete permission) -->
                        <Button v-if="client.archived && can.delete" variant="destructive" @click="handleDeleteClick">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Client
                        </Button>

                        <Link v-if="can.update" :href="clients.edit(client.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Client
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
                                <p class="mt-1">{{ client.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Date of Birth</Label>
                                <p class="mt-1">{{ formatDate(client.profile?.birthdate) || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Company</Label>
                                <p class="mt-1">
                                    <Link v-if="client.company" :href="companies.show(client.company.id).url">
                                    {{ client.company?.name || 'No Company' }}
                                    </Link>
                                    <span v-else>No Company</span>
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Assigned Coach</Label>
                                <p class="mt-1">{{ client.assigned_coach?.name || 'No coach assigned' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Date Added</Label>
                                <p class="mt-1">{{ formatDate(client.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Contact Details</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                <p class="mt-1">{{ client.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Phone Number</Label>
                                <p class="mt-1">{{ client.phone || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Address</Label>
                                <p class="mt-1">{{ client.profile?.address || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Preferred Communication</Label>
                                <div>
                                    <Badge v-if="client.profile?.preferred_method_of_communication">
                                        {{ client.profile?.preferred_method_of_communication.toUpperCase() }}
                                    </Badge>
                                    <p v-else class="text-muted-foreground">Not provided</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical & Emergency Information Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Medical & Emergency Information</h2>

                        <!-- Medical Conditions -->
                        <div class="mb-6">
                            <Label class="text-sm font-medium text-muted-foreground">Medical Conditions</Label>
                            <div class="mt-1">
                                <ul v-if="client.profile?.medical_conditions && client.profile.medical_conditions.length > 0"
                                    class="list-disc list-inside space-y-1">
                                    <li v-for="condition in client.profile.medical_conditions" :key="condition">
                                        {{ condition }}
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">Not provided</p>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-2 block">Emergency
                                Contact</Label>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Contact Name</Label>
                                    <p class="mt-1">{{ client.profile?.emergency_contact_name || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Contact Phone</Label>
                                    <p class="mt-1">{{ client.profile?.emergency_contact_phone || 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import { destroy as destroyClientAction, archive as archiveClientAction, unarchive as unarchiveClientAction } from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { Edit, Archive, ArchiveRestore, Trash2, Calendar } from 'lucide-vue-next';
import companies from '@/routes/tenant/coach/companies';
import { Badge } from '@/components/ui/badge';
import { alertConfirm } from '@/plugins/alert';
import PageHeader from '@/components/PageHeader.vue';
import { create as createSession } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';


const props = defineProps<{
    client: Client;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: clients.index().url,
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url,
    }
];

//If user archived, insert archived clients breadcrumb in index 1
if (props.client.archived) {
    breadcrumbs.splice(1, 0, {
        title: 'Archived Clients',
        href: clients.archived().url,
    });
}

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const handleArchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Archive Client',
        description: `Are you sure you want to archive ${props.client.name}? Archived clients will be moved to the archived clients list and won't appear in your main clients view.`,
        confirmText: 'Archive Client',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(archiveClientAction(props.client.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // The page will reload with updated client data
            }
        });
    }
};

const handleUnarchiveClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Client',
        description: `Are you sure you want to unarchive ${props.client.name}? This will make the client active again and visible in your main clients list.`,
        confirmText: 'Unarchive Client',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(unarchiveClientAction(props.client.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // The page will reload with updated client data
            }
        });
    }
};

const handleDeleteClick = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Client',
        description: `Are you sure you want to permanently delete ${props.client.name}? This action cannot be undone and will remove all client data including profile information, tasks, and session history.`,
        confirmText: 'Delete Permanently',
        variant: 'destructive'
    });

    if (confirmed) {
        router.visit(destroyClientAction(props.client.id));
    }
};
</script>