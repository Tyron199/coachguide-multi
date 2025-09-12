<template>
    <Sheet :open="isOpen" @update:open="$emit('update:open', $event)">
        <SheetContent class="overflow-y-auto">
            <SheetHeader v-if="framework">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <SheetTitle>{{ framework.name }}</SheetTitle>
                        <!-- <SheetDescription class="mt-2">
                            {{ framework.description }}
                        </SheetDescription> -->
                    </div>
                    <!-- <Badge :variant="framework.category === 'models' ? 'default' : 'secondary'" class="ml-3">
                        {{ framework.category === 'models' ? 'Model' : 'Tool' }}
                    </Badge> -->
                </div>
            </SheetHeader>

            <div v-if="framework" class="space-y-6 p-2">
                <!-- Framework Details -->
                <div class="space-y-4" v-if="0 > 1">
                    <!-- Subcategory -->
                    <div v-if="framework.subcategory" class="flex items-center gap-2">
                        <Tag class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm text-foreground font-medium">{{ formatSubcategory(framework.subcategory)
                        }}</span>
                    </div>

                    <!-- Best For -->
                    <div v-if="framework.best_for && framework.best_for.length > 0" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Target class="h-4 w-4 text-muted-foreground" />
                            <span class="text-sm font-medium text-foreground">Best for:</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="type in framework.best_for" :key="type" variant="outline" class="text-xs">
                                {{ formatCoachingType(type) }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Usage Stats -->
                    <div v-if="framework.instances_count !== undefined"
                        class="flex items-center gap-4 text-sm text-muted-foreground">
                        <div class="flex items-center gap-1">
                            <BarChart3 class="h-4 w-4" />
                            <span>{{ framework.instances_count || 0 }} total uses</span>
                        </div>
                        <div v-if="framework.completed_instances_count !== undefined" class="flex items-center gap-1">
                            <CheckCircle class="h-4 w-4" />
                            <span>{{ framework.completed_instances_count || 0 }} completed</span>
                        </div>
                    </div>
                </div>

                <!-- Questions Preview -->
                <div v-if="frameworkFields.length > 0" class="space-y-4">
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-muted-foreground" />
                        <h3 class="text-lg font-semibold text-foreground">Questions</h3>
                        <Badge variant="outline" class="ml-auto">
                            {{ frameworkFields.length }} {{ frameworkFields.length === 1 ? 'question' : 'questions' }}
                        </Badge>
                    </div>

                    <!-- Questions List -->
                    <div class="space-y-2">
                        <div v-for="(field, index) in frameworkFields" :key="field.key"
                            class="border rounded-lg p-3 bg-muted/20">
                            <div class="flex items-start gap-3">
                                <Badge variant="outline" class="text-xs mt-0.5 min-w-[28px] justify-center">
                                    {{ index + 1 }}
                                </Badge>
                                <div class="flex-1">
                                    <h4 class="font-medium text-foreground text-sm leading-relaxed">
                                        {{ field.title }}
                                    </h4>
                                    <p v-if="field.description" class="text-xs text-muted-foreground mt-1">
                                        {{ field.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t">
                    <Button class="flex-1" @click="$emit('assign', framework)">
                        <Plus class="mr-2 h-4 w-4" />
                        Use with Session
                    </Button>
                    <Button variant="outline" @click="$emit('update:open', false)">
                        Close
                    </Button>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>

<script setup lang="ts">
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tag,
    Target,
    BarChart3,
    CheckCircle,
    FileText,
    Plus
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
}

interface Props {
    framework: Framework | null;
    isOpen: boolean;
}

const props = defineProps<Props>();

defineEmits<{
    'update:open': [value: boolean];
    assign: [framework: Framework];
}>();

// Parse framework fields from schema
const frameworkFields = computed(() => {
    if (!props.framework?.schema?.properties) return [];

    return Object.entries(props.framework.schema.properties).map(([key, fieldSchema]: [string, any]) => ({
        key,
        title: fieldSchema.title || key,
        description: fieldSchema.description || '',
        type: fieldSchema.type || 'string',
    }));
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
