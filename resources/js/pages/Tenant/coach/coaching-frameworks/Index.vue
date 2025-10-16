<template>

    <Head title="Tools & Models" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description"
                    :badge="`${formatNumber(props.frameworks.total)} ${props.frameworks.total === 1 ? 'framework' : 'frameworks'}`">

                </PageHeader>

                <FrameworksTable :frameworks="props.frameworks" :subcategories="props.subcategories"
                    :best-for-options="props.bestForOptions" :filters="props.filters" @assign="handleAssignFramework"
                    @preview="handlePreviewFramework" />
            </div>
        </CoachingFrameworksLayout>

        <!-- Framework Preview Sheet -->
        <FrameworkPreviewSheet :framework="selectedFramework" :is-open="isPreviewOpen"
            @update:open="isPreviewOpen = $event" @assign="handleAssignFramework" />

        <!-- Assignment Modal -->
        <AssignFrameworkModal :is-open="isAssignModalOpen" :pre-selected-framework="assignmentFramework"
            @update:is-open="isAssignModalOpen = $event" @success="handleAssignmentSuccess" />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import FrameworksTable from '@/components/FrameworksTable.vue';
import FrameworkPreviewSheet from '@/components/FrameworkPreviewSheet.vue';
import AssignFrameworkModal from '@/components/AssignFrameworkModal.vue';
import { computed, ref } from 'vue';
import { formatNumber } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    instances_count?: number;
    completed_instances_count?: number;
}

interface PaginatedFrameworks {
    data: Framework[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface FrameworkFilters {
    search?: string;
    category?: string;
    subcategory?: string;
    best_for?: string;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    frameworks: PaginatedFrameworks;
    subcategories: string[];
    bestForOptions: string[];
    filters: FrameworkFilters;
    currentCategory?: string;
}

const props = defineProps<Props>();

// Preview sheet state
const selectedFramework = ref<Framework | null>(null);
const isPreviewOpen = ref(false);

// Assignment modal state
const isAssignModalOpen = ref(false);
const assignmentFramework = ref<Framework | null>(null);

// Computed properties
const title = computed(() => {
    if (props.currentCategory === 'models') return 'Coaching Models';
    if (props.currentCategory === 'tools') return 'Coaching Tools';
    return 'Tools & Models';
});

const description = computed(() => {
    if (props.currentCategory === 'models') return 'Theoretical coaching frameworks and structured approaches';
    if (props.currentCategory === 'tools') return 'Practical coaching techniques and exercises';
    return 'Access and select the appropriate coaching tools and models for your clients';
});

const breadcrumbs: BreadcrumbItem[] = [

    {
        title: title.value,
        href: getCurrentUrl()
    },
];

// Methods
function getCurrentUrl(): string {
    if (props.currentCategory === 'models') return frameworkRoutes.models().url;
    if (props.currentCategory === 'tools') return frameworkRoutes.tools().url;
    return frameworkRoutes.index().url;
}

function handleAssignFramework(framework: Framework): void {
    // Open assignment modal with framework pre-selected
    assignmentFramework.value = framework;
    isAssignModalOpen.value = true;
}

function handlePreviewFramework(framework: Framework): void {
    selectedFramework.value = framework;
    isPreviewOpen.value = true;
}

function handleAssignmentSuccess(instance: any): void {
    // The modal component already handles page refresh
    // We could show a success toast here if desired
    console.log('Framework assigned successfully:', instance);
}
</script>
