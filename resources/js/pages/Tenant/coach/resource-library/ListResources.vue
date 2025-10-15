<template>

    <Head title="Resource Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ResourceLibraryLayout>
            <div class="space-y-6">
                <PageHeader title="Resource Library"
                    description="Curated coaching resources to support your professional development"
                    :badge="`${resources.length} ${resources.length === 1 ? 'resource' : 'resources'}`" />

                <!-- Resources Grid -->
                <div v-if="resources.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Card v-for="resource in resources" :key="resource.id"
                        class="group hover:shadow-lg transition-all duration-200 cursor-pointer flex flex-col overflow-hidden pt-0"
                        @click="openResource(resource.link)">

                        <!-- Image/Icon Header -->
                        <div v-if="resource.image_path"
                            class="relative w-full h-56 bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                            <img :src="resource.image_path" :alt="resource.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        </div>
                        <div v-else
                            class="flex items-center justify-center h-56 bg-gradient-to-br from-primary/5 to-primary/10">
                            <div class="p-4 rounded-xl bg-background/80 backdrop-blur-sm shadow-sm">
                                <component :is="getIconComponent(resource.type_icon)" class="h-12 w-12 text-primary" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-col flex-1 py-0 px-2">
                            <div class="flex-1 space-y-2">
                                <h3
                                    class="font-semibold text-base leading-tight line-clamp-2 group-hover:text-primary transition-colors">
                                    {{ resource.title }}
                                </h3>
                                <p class="text-sm text-muted-foreground line-clamp-3">
                                    {{ resource.description }}
                                </p>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between mt-4 pt-4 border-t">
                                <Badge variant="secondary" class="text-xs">
                                    {{ resource.type_label }}
                                </Badge>
                                <ExternalLink
                                    class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" />
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else
                    class="relative flex justify-center items-center min-h-96 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-700">
                    <PlaceholderPattern />
                    <div class="relative z-10 text-center space-y-3">
                        <div class="flex justify-center">
                            <div class="p-3 rounded-full bg-neutral-100 dark:bg-neutral-800">
                                <BookOpen class="h-8 w-8 text-neutral-400" />
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                                No resources found
                            </h3>
                            <p class="text-neutral-500 dark:text-neutral-400 text-sm mt-1">
                                {{ emptyStateMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </ResourceLibraryLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ResourceLibraryLayout from '@/layouts/resource-library/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    BookOpen,
    Podcast,
    Video,
    GraduationCap,
    FileText,
    ExternalLink
} from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import { all as resourceLibraryAll } from '@/actions/App/Http/Controllers/Tenant/Coach/ResourceLibraryController';

interface Resource {
    id: number;
    title: string;
    description: string;
    link: string;
    type: number;
    type_label: string;
    type_icon: string;
    image_path?: string | null;
}

interface Props {
    resources: Resource[];
    typeFilter?: string;
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Resource Library',
        href: resourceLibraryAll().url
    },
]);

const emptyStateMessage = computed(() => {
    return props.typeFilter
        ? 'No resources of this type are available yet.'
        : 'No resources are available yet.';
});

// Map icon names to components
const iconMap: Record<string, any> = {
    BookOpen,
    Podcast,
    Video,
    GraduationCap,
    FileText,
};

const getIconComponent = (iconName: string) => {
    return iconMap[iconName] || BookOpen;
};

const openResource = (url: string) => {
    window.open(url, '_blank', 'noopener,noreferrer');
};
</script>
