<template>

    <Head :title="`${client.name} - Notes`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Notes" description="View and manage notes for this client"
                    :badge="`${notes.length} ${notes.length === 1 ? 'note' : 'notes'}`">
                    <template #actions>
                        <Button variant="outline">
                            <Bot class="mr-2 h-4 w-4" />
                            Summarize with AI
                        </Button>
                        <Link :href="createNoteAction({ query: { client_id: client.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Note
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <NotesList :notes="notes" :can-edit="can.update" :can-delete="can.delete" />
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import NotesList from '@/components/NotesList.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Client, type CoachingNote } from '@/types';
import clients from '@/routes/tenant/clients';
import { create as createNoteAction } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingNoteController';
import { Plus, Bot } from 'lucide-vue-next';

const props = defineProps<{
    client: Client;
    notes: CoachingNote[];
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
    },
    {
        title: 'Notes',
        href: clients.notes(props.client.id).url,
    }
];

// If client is archived, insert archived clients breadcrumb
if (props.client.archived) {
    breadcrumbs.splice(1, 0, {
        title: 'Archived Clients',
        href: clients.archived().url,
    });
}


</script>