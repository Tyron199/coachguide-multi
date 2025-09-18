<template>
    <Card class="hover:shadow-sm transition-shadow">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle class="mb-2">
                        {{ framework.name }}
                    </CardTitle>
                    <CardDescription class="line-clamp-3">
                        {{ framework.description }}
                    </CardDescription>
                </div>
                <Badge :variant="framework.category === 'models' ? 'default' : 'secondary'" class="ml-3">
                    {{ framework.category === 'models' ? 'Model' : 'Tool' }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <!-- Subcategory -->
            <div v-if="framework.subcategory" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm text-foreground">
                    {{ formatSubcategory(framework.subcategory) }}
                </span>
            </div>

            <!-- Best For -->
            <div v-if="framework.best_for && framework.best_for.length > 0" class="space-y-2">
                <div class="flex items-center gap-2">
                    <Target class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium text-foreground">Best for:</span>
                </div>
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="type in framework.best_for.slice(0, 3)" :key="type" variant="outline" class="text-xs">
                        {{ formatCoachingType(type) }}
                    </Badge>
                    <Badge v-if="framework.best_for.length > 3" variant="outline" class="text-xs">
                        +{{ framework.best_for.length - 3 }} more
                    </Badge>
                </div>
            </div>

            <!-- Usage Stats -->
            <div v-if="framework.instances_count !== undefined"
                class="flex items-center gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-1">
                    <BarChart3 class="h-4 w-4" />
                    <span>{{ framework.instances_count || 0 }} uses</span>
                </div>
                <div v-if="framework.completed_instances_count !== undefined" class="flex items-center gap-1">
                    <CheckCircle class="h-4 w-4" />
                    <span>{{ framework.completed_instances_count || 0 }} completed</span>
                </div>
            </div>

            <!-- Field Count -->
            <div v-if="fieldCount" class="flex items-center gap-2 text-sm text-muted-foreground">
                <FileText class="h-4 w-4" />
                <span>{{ fieldCount }} {{ fieldCount === 1 ? 'field' : 'fields' }}</span>
            </div>
        </CardContent>

        <!-- System Framework Actions -->
        <CardFooter v-if="!isCustom" class="flex gap-3">
            <Button variant="outline" class="flex-1" @click="$emit('preview', framework)">
                <Eye class="mr-2 h-4 w-4" />
                Preview
            </Button>
            <Button class="flex-1" @click="$emit('assign', framework)">
                <Plus class="mr-2 h-4 w-4" />
                Use
            </Button>
        </CardFooter>

        <!-- Custom Framework Actions -->
        <CardFooter v-else class="flex gap-3">
            <Button variant="outline" class="flex-1" @click="$emit('preview', framework)">
                <Eye class="mr-2 h-4 w-4" />
                Preview
            </Button>
            <Button class="flex-1" @click="$emit('assign', framework)" :disabled="!framework.is_active">
                <Plus class="mr-2 h-4 w-4" />
                Use
            </Button>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon">
                        <MoreVertical class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="$emit('edit', framework)">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('duplicate', framework)">
                        <Copy class="mr-2 h-4 w-4" />
                        Duplicate
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('toggle-active', framework)">
                        <Power class="mr-2 h-4 w-4" />
                        {{ framework.is_active ? 'Deactivate' : 'Activate' }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="$emit('delete', framework)"
                        class="text-destructive focus:text-destructive">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tag,
    Target,
    BarChart3,
    CheckCircle,
    FileText,
    Eye,
    Plus,
    MoreVertical,
    Edit,
    Copy,
    Power,
    Trash2
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    schema?: {
        properties?: Record<string, any>;
    };
    instances_count?: number;
    completed_instances_count?: number;
    is_active?: boolean; // For custom frameworks
}

interface Props {
    framework: Framework;
    isCustom?: boolean;
}

const props = defineProps<Props>();

// Define emits
defineEmits<{
    preview: [framework: Framework];
    assign: [framework: Framework];
    edit?: [framework: Framework];
    duplicate?: [framework: Framework];
    'toggle-active'?: [framework: Framework];
    delete?: [framework: Framework];
}>();

// Computed properties
const fieldCount = computed(() => {
    if (!props.framework.schema?.properties) return null;
    return Object.keys(props.framework.schema.properties).length;
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
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>