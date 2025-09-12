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
                    <Card v-for="framework in frameworks" :key="framework.id" class="relative">
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <CardTitle class="text-lg">{{ framework.name }}</CardTitle>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge :variant="framework.category === 'models' ? 'default' : 'secondary'">
                                            {{ framework.category === 'models' ? 'Model' : 'Tool' }}
                                        </Badge>
                                        <Badge v-if="framework.subcategory" variant="outline" class="text-xs">
                                            {{ framework.subcategory }}
                                        </Badge>
                                    </div>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm">
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="editFramework(framework)">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="duplicateFramework(framework)">
                                            <Copy class="mr-2 h-4 w-4" />
                                            Duplicate
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="toggleActive(framework)">
                                            <Power class="mr-2 h-4 w-4" />
                                            {{ framework.is_active ? 'Deactivate' : 'Activate' }}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="deleteFramework(framework)"
                                            class="text-destructive focus:text-destructive">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <p class="text-sm text-muted-foreground mb-4 line-clamp-2">
                                {{ framework.description }}
                            </p>

                            <div class="space-y-3">
                                <!-- Usage Stats -->
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Usage</span>
                                    <span class="font-medium">{{ framework.usage_count }} times</span>
                                </div>

                                <!-- Completion Rate -->
                                <div v-if="framework.usage_count > 0" class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Completed</span>
                                    <span class="font-medium">
                                        {{ Math.round((framework.completed_count / framework.usage_count) * 100) }}%
                                    </span>
                                </div>

                                <!-- Best For Tags -->
                                <div v-if="framework.best_for && framework.best_for.length > 0" class="space-y-2">
                                    <span class="text-xs text-muted-foreground">Best for:</span>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="tag in framework.best_for.slice(0, 2)" :key="tag"
                                            variant="outline" class="text-xs">
                                            {{ tag }}
                                        </Badge>
                                        <Badge v-if="framework.best_for.length > 2" variant="outline" class="text-xs">
                                            +{{ framework.best_for.length - 2 }} more
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="flex items-center justify-between pt-2 border-t">
                                    <div class="flex items-center gap-2">
                                        <div :class="framework.is_active ? 'bg-green-500' : 'bg-gray-400'"
                                            class="w-2 h-2 rounded-full">
                                        </div>
                                        <span class="text-xs text-muted-foreground">
                                            {{ framework.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatDate(framework.updated_at) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
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
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
    FileText,
    MoreVertical,
    Edit,
    Copy,
    Power,
    Trash2
} from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';
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
}

interface Props {
    frameworks: Framework[];
}

const props = defineProps<Props>();

// State
const deleteDialogOpen = ref(false);
const frameworkToDelete = ref<Framework | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tools & Models',
        href: frameworkRoutes.index().url
    },
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
    // Store framework data in session and redirect to create with pre-filled data
    const duplicateData = {
        name: `${framework.name} (Copy)`,
        description: framework.description,
        category: framework.category,
        subcategory: framework.subcategory,
        best_for: framework.best_for,
        // Would need to extract fields from schema - this would be handled in the controller
    };

    router.post('/api/custom-frameworks/save-draft', duplicateData, {
        onSuccess: () => {
            router.visit(customFrameworkRoutes.create().url);
        }
    });
}

function toggleActive(framework: Framework): void {
    router.patch(customFrameworkRoutes.toggleActive(framework.id).url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local state
            framework.is_active = !framework.is_active;
        }
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

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>
