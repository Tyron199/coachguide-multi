<template>

    <Head :title="`${client.name} - Objectives`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Objectives"
                    description="Goals, objectives, and focus areas for coaching sessions">
                    <template #actions>
                        <Link :href="editObjectives(client.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Objectives
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <div class="grid gap-6">
                    <!-- Goal Summary Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Goal Summary</h2>
                        <div v-if="client.profile?.goal_summary" class="prose prose-sm max-w-none">
                            <p>{{ client.profile.goal_summary }}</p>
                        </div>
                        <div v-else class="text-muted-foreground">
                            <p>No goal summary provided yet.</p>

                        </div>
                    </div>

                    <!-- Objectives Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Objectives</h2>
                        <div v-if="client.profile?.objectives" class="prose prose-sm max-w-none">
                            <p class="whitespace-pre-wrap">{{ client.profile.objectives }}</p>
                        </div>
                        <div v-else class="text-muted-foreground">
                            <p>No objectives defined yet.</p>

                        </div>
                    </div>

                    <!-- Focus Areas Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Focus Areas</h2>
                        <div v-if="client.profile?.focus_areas && client.profile.focus_areas.length > 0">
                            <ul class="space-y-2">
                                <li v-for="(area, index) in client.profile.focus_areas" :key="index"
                                    class="flex items-center gap-3">
                                    <div class="w-2 h-2 bg-primary rounded-full flex-shrink-0"></div>
                                    <span>{{ area }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-muted-foreground">
                            <p>No focus areas defined yet.</p>

                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import { editObjectives } from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { Edit } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
    client: Client;
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
        title: 'Objectives',
        href: clients.objectives(props.client.id).url,
    }
];
</script>
