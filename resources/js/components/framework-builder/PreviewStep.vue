<template>
    <div class="space-y-6">
        <!-- Instructions -->
        <div class="bg-muted/50 rounded-lg p-4">
            <h3 class="font-medium mb-2">Test Your Framework</h3>
            <p class="text-sm text-muted-foreground">
                Try out your framework as if you were using it in a real coaching session.
                This helps ensure the questions flow well and provide value to coaches.
            </p>
        </div>

        <!-- Framework Test Area -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>{{ frameworkData.name }}</CardTitle>
                        <CardDescription>{{ frameworkData.description }}</CardDescription>
                    </div>
                    <Badge :variant="frameworkData.category === 'models' ? 'default' : 'secondary'">
                        {{ frameworkData.category === 'models' ? 'Model' : 'Tool' }}
                    </Badge>
                </div>
            </CardHeader>

            <CardContent>
                <!-- Progress Section -->
                <div v-if="frameworkData.fields.length > 0" class="space-y-4 mb-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Completion</span>
                            <span class="font-medium">{{ progressPercentage }}%</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all duration-300"
                                :style="{ width: `${progressPercentage}%` }"></div>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ completedFields }} of {{ totalFields }} {{ totalFields === 1 ? 'field' : 'fields' }}
                            completed
                        </div>
                    </div>
                </div>

                <!-- Framework Questions -->
                <div v-if="frameworkData.fields.length > 0" class="space-y-6">
                    <div v-for="(field, index) in frameworkData.fields" :key="field.id" class="space-y-2">
                        <Label :for="`test-${field.id}`" class="text-sm font-medium">
                            {{ field.title || `Question ${index + 1}` }}
                        </Label>

                        <div class="space-y-1">
                            <Textarea :id="`test-${field.id}`" v-model="testData[field.key]"
                                :placeholder="field.description || 'Enter your response...'" rows="4"
                                class="resize-y" />

                            <div v-if="field.description" class="text-xs text-muted-foreground">
                                {{ field.description }}
                            </div>
                        </div>

                        <!-- Field completion indicator -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full transition-colors"
                                    :class="testData[field.key] ? 'bg-primary' : 'bg-muted'">
                                </div>
                                <span class="text-xs text-muted-foreground">
                                    {{ testData[field.key] ? 'Completed' : 'Not completed' }}
                                </span>
                            </div>

                            <div v-if="testData[field.key]" class="text-xs text-muted-foreground">
                                {{ testData[field.key].length }} characters
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 text-muted-foreground">
                    <FileQuestion class="mx-auto h-12 w-12 mb-4" />
                    <h3 class="text-lg font-medium mb-2">No Questions Added</h3>
                    <p class="text-sm">Go back to the previous step to add questions to your framework.</p>
                </div>
            </CardContent>
        </Card>

        <!-- Test Actions -->
        <div v-if="frameworkData.fields.length > 0" class="flex gap-4">
            <Button variant="outline" @click="clearTest">
                <RotateCcw class="mr-2 h-4 w-4" />
                Clear All
            </Button>
            <Button variant="outline" @click="fillSampleData">
                <Sparkles class="mr-2 h-4 w-4" />
                Fill Sample Data
            </Button>
            <Button variant="outline" @click="exportTest" :disabled="completedFields === 0">
                <Download class="mr-2 h-4 w-4" />
                Export Test Data
            </Button>
        </div>

        <!-- Framework Summary -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg">Framework Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Category</div>
                        <div class="flex items-center gap-2 mt-1">
                            <Badge :variant="frameworkData.category === 'models' ? 'default' : 'secondary'">
                                {{ frameworkData.category === 'models' ? 'Model' : 'Tool' }}
                            </Badge>
                            <span v-if="frameworkData.subcategory" class="text-sm text-muted-foreground">
                                • {{ frameworkData.subcategory }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Questions</div>
                        <div class="mt-1">{{ frameworkData.fields.length }} questions</div>
                    </div>

                    <div v-if="frameworkData.best_for.length > 0" class="md:col-span-2">
                        <div class="text-sm font-medium text-muted-foreground mb-2">Best For</div>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="tag in frameworkData.best_for" :key="tag" variant="outline" class="text-xs">
                                {{ tag }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Validation Results -->
                <div v-if="validationResults" class="pt-4 border-t">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <CheckCircle v-if="validationResults.valid" class="h-4 w-4 text-green-600" />
                            <AlertCircle v-else class="h-4 w-4 text-destructive" />
                            <span class="text-sm font-medium">
                                {{ validationResults.valid ? 'Framework is ready to publish' : 'Issues found' }}
                            </span>
                        </div>

                        <div v-if="!validationResults.valid && validationResults.errors" class="space-y-1">
                            <div v-for="(error, key) in validationResults.errors" :key="key"
                                class="text-xs text-destructive">
                                • {{ error }}
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    FileQuestion,
    RotateCcw,
    Sparkles,
    Download,
    CheckCircle,
    AlertCircle
} from 'lucide-vue-next';

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

// Local state
const testData = ref<Record<string, string>>({});
const validationResults = ref<any>(null);

// Computed properties
const totalFields = computed(() => props.frameworkData.fields.length);

const completedFields = computed(() => {
    return Object.values(testData.value).filter(value => value && value.trim() !== '').length;
});

const progressPercentage = computed(() => {
    if (totalFields.value === 0) return 0;
    return Math.round((completedFields.value / totalFields.value) * 100);
});

// Watch for field changes and initialize test data
watch(() => props.frameworkData.fields, (newFields) => {
    // Initialize test data for new fields
    newFields.forEach(field => {
        if (field.key && !(field.key in testData.value)) {
            testData.value[field.key] = '';
        }
    });

    // Remove test data for deleted fields
    const currentKeys = newFields.map(f => f.key).filter(Boolean);
    Object.keys(testData.value).forEach(key => {
        if (!currentKeys.includes(key)) {
            delete testData.value[key];
        }
    });

    // Re-validate when fields change
    validateFramework();
}, { deep: true, immediate: true });

// Methods
function clearTest(): void {
    Object.keys(testData.value).forEach(key => {
        testData.value[key] = '';
    });
}

function fillSampleData(): void {
    props.frameworkData.fields.forEach(field => {
        if (field.key) {
            testData.value[field.key] = getSampleResponse(field.title);
        }
    });
}

function getSampleResponse(title: string): string {
    // Generate contextual sample data based on the question
    const lowerTitle = title.toLowerCase();

    if (lowerTitle.includes('goal') || lowerTitle.includes('achieve')) {
        return 'Improve leadership skills and build stronger team relationships to increase overall team performance by 20% within the next quarter.';
    }

    if (lowerTitle.includes('current') || lowerTitle.includes('situation') || lowerTitle.includes('now')) {
        return 'Currently managing a team of 8 people across different departments. Some team members seem disengaged and communication could be more effective.';
    }

    if (lowerTitle.includes('challenge') || lowerTitle.includes('obstacle') || lowerTitle.includes('barrier')) {
        return 'Main challenges include: 1) Limited time for one-on-ones, 2) Different communication styles within the team, 3) Lack of clear feedback mechanisms.';
    }

    if (lowerTitle.includes('option') || lowerTitle.includes('solution') || lowerTitle.includes('approach')) {
        return 'Potential approaches: Weekly team meetings, individual coaching sessions, implementing a feedback system, team building activities, and communication training.';
    }

    if (lowerTitle.includes('action') || lowerTitle.includes('step') || lowerTitle.includes('plan')) {
        return 'Next steps: 1) Schedule monthly one-on-ones with each team member, 2) Implement weekly check-ins, 3) Create a team charter, 4) Set up feedback loops.';
    }

    if (lowerTitle.includes('support') || lowerTitle.includes('resource') || lowerTitle.includes('help')) {
        return 'Need support from HR for communication training, management backing for time allocation, and possibly a mentor for my own leadership development.';
    }

    // Default sample response
    return 'This is sample response text that demonstrates how coaches will interact with this question during real coaching sessions. It provides meaningful context and shows the depth of reflection expected.';
}

function exportTest(): void {
    const exportData = {
        framework: {
            name: props.frameworkData.name,
            description: props.frameworkData.description,
            category: props.frameworkData.category,
            subcategory: props.frameworkData.subcategory,
            best_for: props.frameworkData.best_for,
        },
        responses: testData.value,
        completed_at: new Date().toISOString(),
        completion_percentage: progressPercentage.value
    };

    const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${props.frameworkData.name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_test.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

async function validateFramework(): Promise<void> {
    try {
        const response = await fetch('/api/custom-frameworks/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(props.frameworkData)
        });

        const data = await response.json();
        validationResults.value = data;
    } catch (error) {
        console.error('Validation failed:', error);
        validationResults.value = { valid: false, errors: { general: 'Validation failed' } };
    }
}

// Initialize
onMounted(() => {
    validateFramework();
});
</script>
