<template>

    <Head :title="`Edit ${framework.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader :title="`Build ${framework.name}`"
                    description="Add questions and customize your coaching framework"
                    :badge="framework.is_active ? 'Active' : 'Draft'"
                    :badge-variant="framework.is_active ? 'default' : 'secondary'" />

                <CustomFrameworkBuilder :existing-subcategories="existingSubcategories"
                    :existing-best-for-options="existingBestForOptions" :initial-data="framework" :is-editing="true"
                    @save="handleUpdate" @cancel="handleCancel" />
            </div>
        </CoachingFrameworksLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import CustomFrameworkBuilder from '@/components/CustomFrameworkBuilder.vue';
import { type BreadcrumbItem } from '@/types';
import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';
import * as customFrameworkRoutes from '@/routes/tenant/coach/custom-frameworks';

interface Field {
    id: string;
    key: string;
    title: string;
    description: string;
}

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    schema: any;
    fields: Field[];
}

interface Props {
    framework: Framework;
    existingSubcategories: string[];
    existingBestForOptions: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tools & Models',
        href: frameworkRoutes.index().url
    },
    {
        title: 'My Custom Frameworks',
        href: customFrameworkRoutes.index().url
    },
    {
        title: props.framework.name,
        href: customFrameworkRoutes.show(props.framework.id).url
    },
    {
        title: 'Edit',
        href: customFrameworkRoutes.edit(props.framework.id).url
    },
];

function handleUpdate(frameworkData: any): void {
    router.patch(customFrameworkRoutes.update(props.framework.id).url, frameworkData);
}

function handleCancel(): void {
    router.visit(customFrameworkRoutes.index().url);
}
</script>
