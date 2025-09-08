<template>

    <Head :title="`${client.name} - Tasks`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Tasks" description="View and manage tasks for this client"
                    :badge="`${tasks.length} ${tasks.length === 1 ? 'task' : 'tasks'}`">
                    <template #actions>
                        <Link :href="CoachingTaskController.create({ query: { client_id: client.id } }).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Task
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <TasksList :tasks="tasks" :can-edit="can.update" :can-delete="can.delete" />
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import TasksList from '@/components/TasksList.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Client, type CoachingTask } from '@/types';
import clients from '@/routes/tenant/coach/clients';

import { Plus } from 'lucide-vue-next';
import CoachingTaskController from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingTaskController';

const props = defineProps<{
    client: Client;
    tasks: CoachingTask[];
    can: {
        create: boolean;
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
        title: 'Tasks',
        href: clients.tasks(props.client.id).url,
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
