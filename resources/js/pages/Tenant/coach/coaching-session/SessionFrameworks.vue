<template>

    <Head :title="`Session ${session.session_number} - Frameworks`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader title="Session Frameworks" description="Tools and models assigned to this coaching session"
                    :badge="`${instances.length} ${instances.length === 1 ? 'framework' : 'frameworks'}`">
                    <template #actions>
                        <Button @click="handleOpenAssignModal">
                            <Plus class="mr-2 h-4 w-4" />
                            Assign Framework
                        </Button>
                    </template>
                </PageHeader>

                <!-- Frameworks Grid -->
                <div v-if="instances.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-6">
                    <FrameworkInstanceCard v-for="instance in instances" :key="instance.id" :instance="instance"
                        @remove="handleRemoveInstance" />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                        <Layers class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-foreground">No frameworks assigned</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Get started by assigning a coaching tool or model to this session.
                    </p>
                    <div class="mt-6">
                        <Button @click="handleOpenAssignModal">
                            <Plus class="mr-2 h-4 w-4" />
                            Assign Framework
                        </Button>
                    </div>
                </div>
            </div>
        </CoachingSessionLayout>

        <!-- Assignment Modal -->
        <AssignFrameworkModal :is-open="isAssignModalOpen"
            :pre-selected-client="{ id: session.client_id, name: session.client?.name || '', email: session.client?.email || '' }"
            :pre-selected-session="{
                id: session.id,
                session_number: session.session_number,
                scheduled_at: session.scheduled_at || '',
                duration: session.duration || 60,
                session_type: session.session_type || 'online',
                formatted_duration: session.formatted_duration || ''
            }" @update:is-open="isAssignModalOpen = $event" @success="handleAssignmentSuccess" />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import FrameworkInstanceCard from '@/components/FrameworkInstanceCard.vue';
import AssignFrameworkModal from '@/components/AssignFrameworkModal.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type CoachingSession } from '@/types';
import sessions from '@/routes/tenant/coach/coaching-sessions';
// import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';
import frameworkInstanceRoutes from '@/routes/tenant/coach/coaching-framework-instances';
import { Plus, Layers } from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';
import { ref } from 'vue';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
}

interface FrameworkInstance {
    id: number;
    framework_id: number;
    session_id: number;
    coach_id: number;
    client_id: number;
    schema_snapshot?: {
        properties: Record<string, any>;
    };
    form_data: Record<string, any>;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    framework: Framework;
    progress_percentage: number;
    total_fields: number;
    completed_fields: number;
}

const props = defineProps<{
    session: CoachingSession;
    instances: FrameworkInstance[];
    can: {
        create: boolean;
        update: boolean;
        delete: boolean;
    };
}>();

// Assignment modal state
const isAssignModalOpen = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.session.is_past ? 'Past Sessions' : 'Sessions',
        href: props.session.is_past ? sessions.past().url : sessions.index().url,
    },
    {
        title: `Session #${props.session.session_number} with ${props.session.client?.name}`,
        href: sessions.show(props.session.id).url,
    },
    {
        title: 'Frameworks',
        href: sessions.frameworks(props.session.id).url,
    }
];

// Methods
async function handleRemoveInstance(instance: FrameworkInstance): Promise<void> {
    const confirmed = await alertConfirm({
        title: 'Remove Framework',
        description: `Are you sure you want to remove "${instance.framework.name}" from this session? This action cannot be undone.`,
        confirmText: 'Remove Framework',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(frameworkInstanceRoutes.destroy(instance.id).url, {
            onSuccess: () => {
                // Page will be reloaded automatically showing updated instances
            },
            onError: (errors) => {
                console.error('Error removing framework instance:', errors);
            }
        });
    }
}

// Handle opening assignment modal
function handleOpenAssignModal(): void {
    isAssignModalOpen.value = true;
}

function handleAssignmentSuccess(instance: any): void {
    // The modal component already handles page refresh
    console.log('Framework assigned successfully:', instance);
}
</script>
