<template>
    <div class="framework-builder">
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Builder Form (2/3 width) -->
            <div class="lg:col-span-2">
                <Tabs default-value="details" class="w-full">
                    <TabsList class="grid w-full grid-cols-2">
                        <TabsTrigger value="details">Details</TabsTrigger>
                        <TabsTrigger value="fields">Fields</TabsTrigger>
                    </TabsList>

                    <TabsContent value="details" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Framework Details</CardTitle>
                                <CardDescription>Set up the framework name, description, and categorization
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <MetadataStep v-model="formData" :existing-subcategories="existingSubcategories"
                                    :existing-best-for-options="existingBestForOptions" :errors="errors" />
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="fields" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Framework Questions</CardTitle>
                                <CardDescription>Add and configure the questions for your framework</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <FieldBuilderStep v-model:fields="formData.fields" :errors="errors" />
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            <!-- Live Preview (1/3 width) -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm">Live Preview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <FrameworkPreview :framework-data="formData" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t">
            <!-- Auto-save status -->
            <div class="flex items-center text-sm text-muted-foreground">
                <LoaderCircle v-if="savingDraft" class="mr-2 h-3 w-3 animate-spin" />
                <CheckCircle v-else-if="justSaved" class="mr-2 h-3 w-3 text-green-600" />
                <span v-if="savingDraft">Saving...</span>
                <span v-else-if="justSaved" class="text-green-600">Saved</span>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-4">
                <Button variant="outline" @click="$emit('cancel')" :disabled="savingDraft">
                    Close
                </Button>

                <Button @click="publishFramework" :disabled="!canPublish || publishing || savingDraft">
                    <LoaderCircle v-if="publishing" class="mr-2 h-4 w-4 animate-spin" />
                    <CheckCircle v-else class="mr-2 h-4 w-4" />
                    {{ publishing ? 'Publishing...' : 'Publish Framework' }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    CheckCircle,
    LoaderCircle
} from 'lucide-vue-next';
import MetadataStep from '@/components/framework-builder/MetadataStep.vue';
import FieldBuilderStep from '@/components/framework-builder/FieldBuilderStep.vue';
import FrameworkPreview from '@/components/framework-builder/FrameworkPreview.vue';

interface Props {
    existingSubcategories: string[];
    existingBestForOptions: string[];
    draft?: any;
    initialData?: any;
    isEditing?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    save: [data: any];
    cancel: [];
}>();

// State
const errors = ref<Record<string, string>>({});
const savingDraft = ref(false);
const publishing = ref(false);
const justSaved = ref(false);

const formData = ref({
    name: '',
    description: '',
    category: 'models',
    subcategory: '',
    best_for: [] as string[],
    fields: [] as Array<{
        id: string;
        key: string;
        title: string;
        description: string;
    }>
});


// Computed properties

const canPublish = computed(() => {
    return formData.value.name.trim() !== '' &&
        formData.value.description.trim() !== '' &&
        formData.value.category !== '' &&
        formData.value.fields.length > 0 &&
        formData.value.fields.every(field => field.title.trim() !== '');
});

// Watch for changes and auto-save
let autoSaveTimeout: NodeJS.Timeout;
const isInitialized = ref(false);

watch(formData, () => {
    // Don't auto-save during initialization
    if (!isInitialized.value) return;

    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        autoSave();
    }, 2000); // Auto-save after 2 seconds of inactivity
}, { deep: true });

// Methods

async function autoSave(): Promise<void> {
    if (savingDraft.value) return;

    savingDraft.value = true;
    justSaved.value = false;

    try {
        if (props.isEditing && props.initialData?.id) {
            // For editing mode, use router.patch with preserveState like framework instances
            await router.patch(
                `/coach/custom-frameworks/${props.initialData.id}`,
                formData.value,
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        // Show "Saved" state briefly
                        justSaved.value = true;
                        setTimeout(() => {
                            justSaved.value = false;
                        }, 2000); // Show "Saved" for 2 seconds
                    },
                    onError: (errors) => {
                        console.error('Auto-save failed:', errors);
                    }
                }
            );
        }
    } finally {
        savingDraft.value = false;
    }
}


async function publishFramework(): Promise<void> {
    if (publishing.value) return;

    publishing.value = true;

    try {
        if (props.isEditing && props.initialData?.id) {
            // For editing mode, use the publish endpoint which redirects to index
            router.patch(`/coach/custom-frameworks/${props.initialData.id}/publish`, formData.value);
        } else {
            // For creation mode, emit to parent
            emit('save', formData.value);
        }
    } finally {
        publishing.value = false;
    }
}


// Initialize
onMounted(() => {
    // Load initial data for editing
    if (props.isEditing && props.initialData) {
        formData.value.name = props.initialData.name || '';
        formData.value.description = props.initialData.description || '';
        formData.value.category = props.initialData.category || 'models';
        formData.value.subcategory = props.initialData.subcategory || '';
        formData.value.best_for = props.initialData.best_for || [];
        formData.value.fields = props.initialData.fields || [];
    }
    // Load draft data if available (for new frameworks)
    else if (props.draft) {
        Object.assign(formData.value, props.draft);
    }

    // Add initial field if none exist
    if (formData.value.fields.length === 0) {
        formData.value.fields.push({
            id: crypto.randomUUID(),
            key: crypto.randomUUID(), // Auto-generate UUID for field key
            title: 'What would you like to explore?',
            description: 'This is your first framework question. You can edit or replace it.'
        });
    }

    // Mark as initialized after initial setup
    setTimeout(() => {
        isInitialized.value = true;
    }, 100);
});
</script>
