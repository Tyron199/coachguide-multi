<template>
    <div class="space-y-4">
        <!-- Framework Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <h3 class="font-medium">{{ frameworkData.name || 'Untitled Framework' }}</h3>
                <Badge :variant="frameworkData.category === 'models' ? 'default' : 'secondary'" class="text-xs">
                    {{ frameworkData.category === 'models' ? 'Model' : 'Tool' }}
                </Badge>
            </div>
            <p v-if="frameworkData.description" class="text-sm text-muted-foreground">
                {{ frameworkData.description }}
            </p>
        </div>

        <!-- Progress Indicator -->
        <div v-if="frameworkData.fields.length > 0" class="space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-muted-foreground">Progress</span>
                <span class="font-medium">{{ progressPercentage }}%</span>
            </div>
            <div class="w-full bg-secondary rounded-full h-1.5">
                <div class="bg-primary h-1.5 rounded-full transition-all duration-300"
                    :style="{ width: `${progressPercentage}%` }"></div>
            </div>
            <div class="text-xs text-muted-foreground">
                {{ completedFields }} of {{ totalFields }} {{ totalFields === 1 ? 'field' : 'fields' }} completed
            </div>
        </div>

        <!-- Framework Fields Preview -->
        <div v-if="frameworkData.fields.length > 0" class="space-y-4">
            <div v-for="(field, index) in frameworkData.fields" :key="field.id" class="space-y-2">
                <Label :for="`preview-${field.id}`" class="text-sm font-medium">
                    {{ field.title || `Question ${index + 1}` }}
                </Label>

                <Textarea :id="`preview-${field.id}`" v-model="previewData[field.key]"
                    :placeholder="field.description || 'Enter your response...'" rows="3" class="text-sm resize-none" />

                <!-- Field completion indicator -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full transition-colors"
                            :class="previewData[field.key] ? 'bg-primary' : 'bg-muted'">
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {{ previewData[field.key] ? 'Completed' : 'Not completed' }}
                        </span>
                    </div>

                    <div v-if="previewData[field.key]" class="text-xs text-muted-foreground">
                        {{ previewData[field.key].length }} characters
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-6 text-muted-foreground">
            <FileQuestion class="mx-auto h-8 w-8 mb-2" />
            <p class="text-sm">No questions added yet</p>
        </div>

        <!-- Preview Actions -->
        <div v-if="frameworkData.fields.length > 0" class="flex gap-2 pt-4 border-t">
            <Button variant="outline" size="sm" @click="clearPreview">
                <RotateCcw class="mr-2 h-3 w-3" />
                Clear
            </Button>
            <Button variant="outline" size="sm" @click="fillSampleData">
                <Sparkles class="mr-2 h-3 w-3" />
                Sample Data
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileQuestion, RotateCcw, Sparkles } from 'lucide-vue-next';

interface Field {
    id: string;
    key: string;
    title: string;
    description: string;
}

interface FrameworkData {
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    fields: Field[];
}

interface Props {
    frameworkData: FrameworkData;
    previewSchema?: any;
}

const props = defineProps<Props>();

// Local state for preview form data
const previewData = ref<Record<string, string>>({});

// Computed properties
const totalFields = computed(() => props.frameworkData.fields.length);

const completedFields = computed(() => {
    return Object.values(previewData.value).filter(value => value && value.trim() !== '').length;
});

const progressPercentage = computed(() => {
    if (totalFields.value === 0) return 0;
    return Math.round((completedFields.value / totalFields.value) * 100);
});

// Watch for field changes and initialize preview data
watch(() => props.frameworkData.fields, (newFields) => {
    // Initialize preview data for new fields
    newFields.forEach(field => {
        if (field.key && !(field.key in previewData.value)) {
            previewData.value[field.key] = '';
        }
    });

    // Remove preview data for deleted fields
    const currentKeys = newFields.map(f => f.key).filter(Boolean);
    Object.keys(previewData.value).forEach(key => {
        if (!currentKeys.includes(key)) {
            delete previewData.value[key];
        }
    });
}, { deep: true, immediate: true });

// Methods
function clearPreview(): void {
    Object.keys(previewData.value).forEach(key => {
        previewData.value[key] = '';
    });
}

function fillSampleData(): void {
    props.frameworkData.fields.forEach(field => {
        if (field.key) {
            previewData.value[field.key] = getSampleResponse(field.title, field.description);
        }
    });
}

function getSampleResponse(title: string, description: string): string {
    // Generate contextual sample data based on the question
    const lowerTitle = title.toLowerCase();

    if (lowerTitle.includes('goal') || lowerTitle.includes('achieve')) {
        return 'Improve team communication and increase quarterly sales by 15%.';
    }

    if (lowerTitle.includes('current') || lowerTitle.includes('situation') || lowerTitle.includes('now')) {
        return 'Currently facing challenges with remote team coordination and meeting project deadlines.';
    }

    if (lowerTitle.includes('challenge') || lowerTitle.includes('obstacle') || lowerTitle.includes('barrier')) {
        return 'Limited face-to-face interaction, different time zones, and unclear communication protocols.';
    }

    if (lowerTitle.includes('option') || lowerTitle.includes('solution') || lowerTitle.includes('approach')) {
        return 'Implement daily stand-ups, use project management tools, and establish clear communication guidelines.';
    }

    if (lowerTitle.includes('action') || lowerTitle.includes('step') || lowerTitle.includes('plan')) {
        return 'Schedule weekly team meetings, introduce Slack for instant communication, and create project timelines.';
    }

    if (lowerTitle.includes('support') || lowerTitle.includes('resource') || lowerTitle.includes('help')) {
        return 'Need management buy-in, team training on new tools, and regular check-ins for accountability.';
    }

    // Default sample response
    return 'This is sample response text to demonstrate how the framework will look during actual coaching sessions.';
}
</script>
