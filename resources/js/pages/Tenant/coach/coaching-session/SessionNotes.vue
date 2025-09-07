<template>

    <Head :title="`Session ${session.session_number} - Notes`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader title="Session Notes" description="View and manage notes for this coaching session"
                    :badge="`${notes.length} ${notes.length === 1 ? 'note' : 'notes'}`">
                    <template #actions>

                        <Link :href="createNote({ query: { session_id: session.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Note
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <NotesList :notes="notes" :can-edit="can.update" :can-delete="can.delete" />
            </div>
        </CoachingSessionLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import NotesList from '@/components/NotesList.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type CoachingSession, type CoachingNote } from '@/types';
import sessions from '@/routes/tenant/coaching-sessions';
import { create as createNote } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingNoteController';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
    session: CoachingSession;
    notes: CoachingNote[];
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: sessions.index().url,
    },
    {
        title: `Session ${props.session.session_number}`,
        href: sessions.show(props.session.id).url,
    },
    {
        title: 'Notes',
        href: sessions.notes(props.session.id).url,
    }
];


</script>
