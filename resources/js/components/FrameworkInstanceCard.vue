<template>
    <Card class="hover:shadow-sm transition-shadow">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle class="mb-2">
                        <Link :href="frameworkInstanceRoutes.show(instance.id).url" class="flex-1"> {{
                            instance.framework.name }}
                        </Link>
                    </CardTitle>
                    <CardDescription class="line-clamp-3">
                        {{ instance.framework.description }}
                    </CardDescription>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <Badge :variant="instance.framework.category === 'models' ? 'default' : 'secondary'" class="ml-3">
                        {{ instance.framework.category === 'models' ? 'Model' : 'Tool' }}
                    </Badge>
                    <Badge v-if="instance.completed_at" variant="default" class="ml-3">
                        <CheckCircle class="mr-1 h-3 w-3" />
                        Completed
                    </Badge>
                    <Badge v-else variant="outline" class="ml-3">
                        <Clock class="mr-1 h-3 w-3" />
                        In Progress
                    </Badge>
                </div>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <!-- Subcategory -->
            <div v-if="instance.framework.subcategory" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm text-foreground">
                    {{ formatSubcategory(instance.framework.subcategory) }}
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="font-medium">{{ progressPercentage }}%</span>
                </div>
                <div class="w-full bg-secondary rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${progressPercentage}%` }"></div>
                </div>
                <div class="text-xs text-muted-foreground">
                    {{ completedFields }} of {{ totalFields }} {{ totalFields === 1 ? 'field' : 'fields' }} completed
                </div>
            </div>

            <!-- Best For -->
            <div v-if="instance.framework.best_for && instance.framework.best_for.length > 0" class="space-y-2">
                <div class="flex items-center gap-2">
                    <Target class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium text-foreground">Best for:</span>
                </div>
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="type in instance.framework.best_for.slice(0, 3)" :key="type" variant="outline"
                        class="text-xs">
                        {{ formatCoachingType(type) }}
                    </Badge>
                    <Badge v-if="instance.framework.best_for.length > 3" variant="outline" class="text-xs">
                        +{{ instance.framework.best_for.length - 3 }} more
                    </Badge>
                </div>
            </div>

            <!-- Assignment Info -->
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-1">
                    <Calendar class="h-4 w-4" />
                    <span>Assigned {{ formatDate(instance.created_at) }}</span>
                </div>
                <div v-if="instance.completed_at" class="flex items-center gap-1">
                    <CheckCircle class="h-4 w-4" />
                    <span>Completed {{ formatDate(instance.completed_at) }}</span>
                </div>
            </div>
        </CardContent>

        <CardFooter class="flex gap-3">
            <Button variant="outline" class="flex-1" @click="$emit('remove', instance)">
                <Trash2 class="mr-2 h-4 w-4" />
                Remove
            </Button>
            <Link :href="frameworkInstanceRoutes.show(instance.id).url" class="flex-1">
            <Button class="w-full">
                <Edit class="mr-2 h-4 w-4" />
                Edit
            </Button>
            </Link>
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import {
    Tag,
    Target,
    CheckCircle,
    Clock,
    Calendar,
    Edit,
    Trash2
} from 'lucide-vue-next';
import { computed } from 'vue';
import frameworkInstanceRoutes from '@/routes/tenant/coach/coaching-framework-instances';

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

interface Props {
    instance: FrameworkInstance;
}

const props = defineProps<Props>();

// Define emits
defineEmits<{
    remove: [instance: FrameworkInstance];
}>();

// Computed properties
const progressPercentage = computed(() => {
    return props.instance.progress_percentage || 0;
});

const totalFields = computed(() => {
    return props.instance.total_fields || 0;
});

const completedFields = computed(() => {
    return props.instance.completed_fields || 0;
});

// Utility functions
function formatSubcategory(subcategory: string): string {
    return subcategory
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatCoachingType(type: string): string {
    return type
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
