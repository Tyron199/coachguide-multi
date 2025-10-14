<template>

    <Head :title="`${client.name} - Sessions`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Sessions" description="View and manage coaching sessions for this client"
                    :badge="`${sessions.length} ${sessions.length === 1 ? 'session' : 'sessions'}`">
                    <template #actions>
                        <Link :href="createSession({ query: { client_id: client.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Schedule Session
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <SessionsList :sessions="sessions" :can-edit="can.update" :can-delete="can.delete">
                    <template #empty-actions>
                        <Link :href="createSession({ query: { client_id: client.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Schedule Session
                        </Button>
                        </Link>
                    </template>
                </SessionsList>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SessionsList from '@/components/SessionsList.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Client, type CoachingSession } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import { create as createSession } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
    client: Client;
    sessions: CoachingSession[];
    can: {
        create: boolean;
        update: boolean;
        delete: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.client.archived ? 'Archived Clients' : 'Clients',
        href: props.client.archived ? clients.archived().url : clients.index().url,
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url,
    },
    {
        title: 'Sessions',
        href: clients.sessions(props.client.id).url,
    }
];
</script>
