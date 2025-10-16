<template>

    <Head title="My Custom Frameworks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader title="My Custom Frameworks" description="Manage your custom coaching frameworks"
                    :badge="`${frameworks.length} ${frameworks.length === 1 ? 'framework' : 'frameworks'}`">
                    <template #actions>
                        <Button @click="createFramework">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Framework
                        </Button>
                    </template>
                </PageHeader>

                <!-- Empty State -->
                <div v-if="frameworks.length === 0" class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-muted rounded-full flex items-center justify-center mb-4">
                        <FileText class="h-12 w-12 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium text-foreground mb-2">No Custom Frameworks Yet</h3>
                    <p class="text-muted-foreground mb-6 max-w-md mx-auto">
                        Create your first custom framework to tailor coaching tools specifically for your practice.
                    </p>
                    <Button @click="createFramework">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Your First Framework
                    </Button>
                </div>

                <!-- Frameworks Grid -->
                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <FrameworkCard v-for="framework in frameworks" :key="framework.id" :framework="framework"
                        :is-custom="true" @preview="handlePreviewFramework" @assign="handleAssignFramework"
                        @edit="editFramework" @duplicate="duplicateFramework" @toggle-active="toggleActive"
                        @delete="deleteFramework" />
                </div>
            </div>
        </CoachingFrameworksLayout>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Framework</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{ frameworkToDelete?.name }}"?
                        This action cannot be undone.
                        <span v-if="frameworkToDelete?.usage_count > 0" class="block mt-2 text-destructive">
                            Warning: This framework has been used {{ frameworkToDelete.usage_count }} times.
                        </span>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" variant="destructive">
                        Delete Framework
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Framework Preview Sheet -->
        <FrameworkPreviewSheet :framework="selectedFramework" :is-open="isPreviewOpen"
            @update:open="isPreviewOpen = $event" @assign="handleAssignFramework" />

        <!-- Assignment Modal -->
        <AssignFrameworkModal :is-open="isAssignModalOpen" :pre-selected-framework="assignmentFramework"
            @update:is-open="isAssignModalOpen = $event" @success="handleAssignmentSuccess" />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import FrameworkCard from '@/components/FrameworkCard.vue';
import FrameworkPreviewSheet from '@/components/FrameworkPreviewSheet.vue';
import AssignFrameworkModal from '@/components/AssignFrameworkModal.vue';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Plus,
    FileText
} from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import * as customFrameworkRoutes from '@/routes/tenant/coach/custom-frameworks';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    is_active: boolean;
    usage_count: number;
    completed_count: number;
    created_at: string;
    updated_at: string;
    schema?: {
        properties?: Record<string, any>;
    };
    instances_count?: number;
    completed_instances_count?: number;
}

interface Props {
    frameworks: Framework[];
}

defineProps<Props>();

// State
const deleteDialogOpen = ref(false);
const frameworkToDelete = ref<Framework | null>(null);

// Preview and assignment modal state
const selectedFramework = ref<Framework | null>(null);
const isPreviewOpen = ref(false);
const isAssignModalOpen = ref(false);
const assignmentFramework = ref<Framework | null>(null);

const breadcrumbs: BreadcrumbItem[] = [

    {
        title: 'My Custom Frameworks',
        href: customFrameworkRoutes.index().url
    },
];

// Methods
function createFramework(): void {
    router.visit(customFrameworkRoutes.create().url);
}

function editFramework(framework: Framework): void {
    router.visit(customFrameworkRoutes.edit(framework.id).url);
}

function duplicateFramework(framework: Framework): void {
    router.patch(customFrameworkRoutes.duplicate(framework.id).url, {}, {
        preserveScroll: true,
    });
}

function toggleActive(framework: Framework): void {
    router.patch(customFrameworkRoutes.toggleActive(framework.id).url, {}, {
        preserveScroll: true,
    });
}

function deleteFramework(framework: Framework): void {
    frameworkToDelete.value = framework;
    deleteDialogOpen.value = true;
}

function confirmDelete(): void {
    if (frameworkToDelete.value) {
        router.delete(customFrameworkRoutes.destroy(frameworkToDelete.value.id).url);
        deleteDialogOpen.value = false;
        frameworkToDelete.value = null;
    }
}

function handlePreviewFramework(framework: Framework): void {
    selectedFramework.value = framework;
    isPreviewOpen.value = true;
}

function handleAssignFramework(framework: Framework): void {
    assignmentFramework.value = framework;
    isAssignModalOpen.value = true;
}

function handleAssignmentSuccess(instance: any): void {
    // The modal component already handles page refresh
    console.log('Custom framework assigned successfully:', instance);
}
</script>
