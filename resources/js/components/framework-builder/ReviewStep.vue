<template>
    <div class="space-y-6">
        <!-- Instructions -->
        <div class="bg-muted/50 rounded-lg p-4">
            <h3 class="font-medium mb-2">Ready to Publish</h3>
            <p class="text-sm text-muted-foreground">
                Review your framework one final time before publishing. Once published,
                it will be available for use in coaching sessions.
            </p>
        </div>

        <!-- Framework Overview -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>{{ frameworkData.name }}</CardTitle>
                        <CardDescription>{{ frameworkData.description }}</CardDescription>
                    </div>
                    <Badge :variant="frameworkData.category === 'models' ? 'default' : 'secondary'"
                        class="text-lg px-3 py-1">
                        {{ frameworkData.category === 'models' ? 'Model' : 'Tool' }}
                    </Badge>
                </div>
            </CardHeader>
        </Card>

        <!-- Framework Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Metadata Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Framework Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Category</div>
                        <div class="mt-1">
                            {{ frameworkData.category === 'models' ? 'Coaching Model' : 'Coaching Tool' }}
                        </div>
                    </div>

                    <div v-if="frameworkData.subcategory">
                        <div class="text-sm font-medium text-muted-foreground">Subcategory</div>
                        <div class="mt-1">{{ frameworkData.subcategory }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Questions</div>
                        <div class="mt-1">{{ frameworkData.fields.length }} questions</div>
                    </div>

                    <div v-if="frameworkData.best_for.length > 0">
                        <div class="text-sm font-medium text-muted-foreground mb-2">Best For</div>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="tag in frameworkData.best_for" :key="tag" variant="outline" class="text-xs">
                                {{ tag }}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Usage Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Usage Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Users class="h-4 w-4 text-muted-foreground" />
                            <span>Available to all coaches in your organization</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <span>Can be assigned to coaching sessions</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm">
                            <BarChart class="h-4 w-4 text-muted-foreground" />
                            <span>Usage analytics will be tracked</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm">
                            <Edit class="h-4 w-4 text-muted-foreground" />
                            <span>Can be edited after publishing</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t">
                        <div class="text-sm font-medium text-muted-foreground mb-2">Framework ID</div>
                        <div class="text-xs font-mono bg-muted px-2 py-1 rounded">
                            {{ generateSlug(frameworkData.name) }}
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Questions Preview -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg">Framework Questions</CardTitle>
                <CardDescription>
                    These questions will be presented to coaches during coaching sessions
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div v-for="(field, index) in frameworkData.fields" :key="field.id" class="border rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="text-xs">
                                    Question {{ index + 1 }}
                                </Badge>
                                <Badge variant="secondary" class="text-xs font-mono">
                                    {{ field.key }}
                                </Badge>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="font-medium">{{ field.title }}</div>
                            <div v-if="field.description" class="text-sm text-muted-foreground">
                                {{ field.description }}
                            </div>

                            <!-- Preview of how it will look -->
                            <div class="mt-3 p-3 bg-muted/30 rounded border-l-2 border-primary/20">
                                <div class="text-xs text-muted-foreground mb-1">Preview:</div>
                                <Textarea :placeholder="field.description || 'Coach will enter response here...'"
                                    rows="2" disabled class="text-sm bg-background" />
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Validation Status -->
        <Card v-if="validationResults">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <CheckCircle v-if="validationResults.valid" class="h-5 w-5 text-green-600" />
                    <AlertCircle v-else class="h-5 w-5 text-destructive" />
                    <CardTitle class="text-lg">
                        {{ validationResults.valid ? 'Ready to Publish' : 'Issues Found' }}
                    </CardTitle>
                </div>
            </CardHeader>
            <CardContent v-if="!validationResults.valid">
                <div class="space-y-2">
                    <div v-for="(error, key) in validationResults.errors" :key="key"
                        class="flex items-center gap-2 text-sm text-destructive">
                        <AlertCircle class="h-3 w-3" />
                        {{ error }}
                    </div>
                </div>
            </CardContent>
            <CardContent v-else>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <CheckCircle class="h-4 w-4 text-green-600" />
                    Your framework has passed all validation checks and is ready to publish.
                </div>
            </CardContent>
        </Card>

        <!-- Publishing Options -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg">Publishing Options</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="flex items-center space-x-2">
                    <Checkbox id="active" :checked="publishActive" @update:checked="publishActive = $event" />
                    <Label for="active" class="text-sm font-medium">
                        Publish as active framework
                    </Label>
                </div>
                <div class="text-xs text-muted-foreground ml-6">
                    Active frameworks are immediately available for use in coaching sessions.
                    You can always activate/deactivate later.
                </div>

                <div class="pt-4 border-t">
                    <div class="text-sm font-medium mb-2">What happens next?</div>
                    <ul class="text-sm text-muted-foreground space-y-1">
                        <li>• Framework will be saved to your custom frameworks library</li>
                        <li>• It will appear in the frameworks list for assignment to sessions</li>
                        <li>• Other coaches in your organization can use it</li>
                        <li>• Usage statistics will be tracked automatically</li>
                        <li>• You can edit or deactivate it anytime</li>
                    </ul>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Users,
    Calendar,
    BarChart,
    Edit,
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
const publishActive = ref(true);
const validationResults = ref<any>(null);

// Methods
function generateSlug(name: string): string {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, '-')
        .replace(/^-+|-+$/g, '');
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
