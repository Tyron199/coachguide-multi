<template>

    <Head :title="framework.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader :title="framework.name" :description="framework.description"
                    :badge="framework.is_active ? 'Active' : 'Inactive'"
                    :badge-variant="framework.is_active ? 'default' : 'secondary'">
                    <template #actions>
                        <div class="flex items-center gap-3">
                            <Badge :variant="framework.category === 'models' ? 'default' : 'secondary'">
                                {{ framework.category === 'models' ? 'Model' : 'Tool' }}
                            </Badge>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline">
                                        <Settings class="mr-2 h-4 w-4" />
                                        Actions
                                        <ChevronDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="editFramework">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit Framework
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="duplicateFramework">
                                        <Copy class="mr-2 h-4 w-4" />
                                        Duplicate
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="assignFramework">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Assign to Session
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="toggleActive">
                                        <Power class="mr-2 h-4 w-4" />
                                        {{ framework.is_active ? 'Deactivate' : 'Activate' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteFramework"
                                        class="text-destructive focus:text-destructive">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </template>
                </PageHeader>

                <!-- Framework Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Framework Schema -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Framework Questions</CardTitle>
                                <CardDescription>
                                    Questions that coaches will use during coaching sessions
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div v-for="(property, key, index) in framework.schema.properties" :key="key"
                                        class="border rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <Badge variant="outline" class="text-xs">
                                                Question {{ index + 1 }}
                                            </Badge>
                                            <Badge variant="secondary" class="text-xs font-mono">
                                                {{ key }}
                                            </Badge>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="font-medium">{{ property.title }}</div>
                                            <div v-if="property.description" class="text-sm text-muted-foreground">
                                                {{ property.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Framework Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg">Framework Info</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Category</div>
                                    <div class="mt-1">
                                        {{ framework.category === 'models' ? 'Coaching Model' : 'Coaching Tool' }}
                                    </div>
                                </div>

                                <div v-if="framework.subcategory">
                                    <div class="text-sm font-medium text-muted-foreground">Subcategory</div>
                                    <div class="mt-1">{{ framework.subcategory }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Questions</div>
                                    <div class="mt-1">{{ Object.keys(framework.schema.properties || {}).length }}
                                        questions</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Status</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div :class="framework.is_active ? 'bg-green-500' : 'bg-gray-400'"
                                            class="w-2 h-2 rounded-full">
                                        </div>
                                        <span class="text-sm">
                                            {{ framework.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="framework.best_for && framework.best_for.length > 0">
                                    <div class="text-sm font-medium text-muted-foreground mb-2">Best For</div>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-for="tag in framework.best_for" :key="tag" variant="outline"
                                            class="text-xs">
                                            {{ tag }}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Usage Stats -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg">Usage Statistics</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Total Uses</div>
                                    <div class="text-2xl font-bold mt-1">{{ framework.usage_count }}</div>
                                </div>

                                <div v-if="framework.usage_count > 0">
                                    <div class="text-sm font-medium text-muted-foreground">Completed</div>
                                    <div class="text-2xl font-bold mt-1">{{ framework.completed_count }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ Math.round((framework.completed_count / framework.usage_count) * 100) }}%
                                        completion rate
                                    </div>
                                </div>

                                <div class="pt-3 border-t">
                                    <div class="text-sm font-medium text-muted-foreground">Created</div>
                                    <div class="text-sm mt-1">{{ formatDate(framework.created_at) }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Last Updated</div>
                                    <div class="text-sm mt-1">{{ formatDate(framework.updated_at) }}</div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </CoachingFrameworksLayout>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Framework</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{ framework.name }}"?
                        This action cannot be undone.
                        <span v-if="framework.usage_count > 0" class="block mt-2 text-destructive">
                            Warning: This framework has been used {{ framework.usage_count }} times.
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
    Settings,
    ChevronDown,
    Edit,
    Copy,
    Plus,
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
    schema: any;
    is_active: boolean;
    usage_count: number;
    completed_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    framework: Framework;
}

const props = defineProps<Props>();

// State
const deleteDialogOpen = ref(false);

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
];

// Methods
function editFramework(): void {
    router.visit(customFrameworkRoutes.edit(props.framework.id).url);
}

function duplicateFramework(): void {
    router.patch(customFrameworkRoutes.duplicate(props.framework.id).url, {}, {
        preserveScroll: true,
    });
}

function assignFramework(): void {
    router.visit(frameworkRoutes.assignSpecific(props.framework.id).url);
}

function toggleActive(): void {
    router.patch(customFrameworkRoutes.toggleActive(props.framework.id).url, {}, {
        preserveScroll: true,
    });
}

function deleteFramework(): void {
    deleteDialogOpen.value = true;
}

function confirmDelete(): void {
    router.delete(customFrameworkRoutes.destroy(props.framework.id).url);
    deleteDialogOpen.value = false;
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>
