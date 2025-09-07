<template>

    <Head :title="`Session ${session.session_number} - Tasks`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader title="Session Tasks" description="View and manage tasks for this coaching session"
                    :badge="`${tasks.length} ${tasks.length === 1 ? 'task' : 'tasks'}`">
                    <template #actions>
                        <Link :href="CoachingTaskController.create({ query: { session_id: session.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Task
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <TasksList :tasks="tasks" :can-edit="can.update" :can-delete="can.delete" />
            </div>
        </CoachingSessionLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import TasksList from '@/components/TasksList.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type CoachingSession, type CoachingTask } from '@/types';
import sessions from '@/routes/tenant/coaching-sessions';

import { Plus } from 'lucide-vue-next';
import CoachingTaskController from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingTaskController';

const props = defineProps<{
    session: CoachingSession;
    tasks: CoachingTask[];
    can: {
        create: boolean;
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
        title: 'Tasks',
        href: sessions.tasks(props.session.id).url,
    }
];


</script>
