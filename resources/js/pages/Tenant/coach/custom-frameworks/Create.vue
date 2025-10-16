<template>

    <Head title="Create Custom Framework" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader title="Create Custom Framework"
                    description="Enter the basic details to create your framework" />

                <div class="max-w-2xl">
                    <Form @submit="handleSubmit" class="space-y-6" v-slot="{ errors, processing }">
                        <Card>
                            <CardHeader>
                                <CardTitle>Framework Details</CardTitle>
                                <CardDescription>
                                    Provide the basic information for your custom framework. You can add questions and
                                    customize it further after creation.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Framework Name -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium">
                                        Framework Name *
                                    </Label>
                                    <Input id="name" v-model="formData.name" placeholder="e.g., My Custom GROW Model"
                                        :class="{ 'border-destructive': errors.name }" maxlength="255" required />
                                    <InputError :message="errors.name" />
                                    <div class="text-xs text-muted-foreground">
                                        {{ formData.name.length }}/255 characters
                                    </div>
                                </div>

                                <!-- Framework Description -->
                                <div class="space-y-2">
                                    <Label for="description" class="text-sm font-medium">
                                        Description *
                                    </Label>
                                    <Textarea id="description" v-model="formData.description"
                                        placeholder="Describe what your framework helps coaches achieve and when to use it..."
                                        :class="{ 'border-destructive': errors.description }" rows="4" maxlength="1000"
                                        required />
                                    <InputError :message="errors.description" />
                                    <div class="text-xs text-muted-foreground">
                                        {{ formData.description.length }}/1000 characters
                                    </div>
                                </div>

                                <!-- Category Selection -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">
                                        Category *
                                    </Label>
                                    <RadioGroup v-model="formData.category" class="flex gap-6">
                                        <div class="flex items-center space-x-2">
                                            <RadioGroupItem value="models" id="models" />
                                            <Label for="models" class="font-normal cursor-pointer">
                                                <div>
                                                    <div class="font-medium">Models</div>
                                                    <div class="text-xs text-muted-foreground">
                                                        Theoretical frameworks and structured approaches
                                                    </div>
                                                </div>
                                            </Label>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <RadioGroupItem value="tools" id="tools" />
                                            <Label for="tools" class="font-normal cursor-pointer">
                                                <div>
                                                    <div class="font-medium">Tools</div>
                                                    <div class="text-xs text-muted-foreground">
                                                        Practical techniques and exercises
                                                    </div>
                                                </div>
                                            </Label>
                                        </div>
                                    </RadioGroup>
                                    <InputError :message="errors.category" />
                                </div>

                                <!-- Subcategory -->
                                <div class="space-y-2">
                                    <Label for="subcategory" class="text-sm font-medium">
                                        Subcategory (Optional)
                                    </Label>
                                    <div class="flex gap-2">
                                        <Select v-model="selectedSubcategory"
                                            @update:model-value="handleSubcategoryChange">
                                            <SelectTrigger class="flex-1">
                                                <SelectValue placeholder="Choose existing or leave blank..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">No subcategory</SelectItem>
                                                <SelectItem value="custom">Create New Subcategory</SelectItem>
                                                <SelectSeparator v-if="existingSubcategories.value.length > 0" />
                                                <SelectItem v-for="subcategory in existingSubcategories.value"
                                                    :key="subcategory" :value="subcategory">
                                                    {{ subcategory }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <!-- Custom Subcategory Input -->
                                    <Input v-if="showCustomSubcategory" v-model="formData.subcategory"
                                        placeholder="Enter new subcategory..." maxlength="100" />

                                    <InputError :message="errors.subcategory" />
                                    <div class="text-xs text-muted-foreground">
                                        Optional: Helps organize frameworks by specific focus area
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Submit Actions -->
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="handleCancel">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="!canSubmit || processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Create & Continue Building
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </CoachingFrameworksLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { LoaderCircle, Plus } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import * as customFrameworkRoutes from '@/routes/tenant/coach/custom-frameworks';

interface Props {
    existingSubcategories: string[];
    existingBestForOptions: string[];
}

const props = defineProps<Props>();

const existingSubcategories = computed(() => props.existingSubcategories);

// Form state
const formData = ref({
    name: '',
    description: '',
    category: 'models',
    subcategory: '',
});

const selectedSubcategory = ref('');
const showCustomSubcategory = ref(false);

// Computed
const canSubmit = computed(() => {
    return formData.value.name.trim() !== '' &&
        formData.value.description.trim() !== '' &&
        formData.value.category !== '';
});

const breadcrumbs: BreadcrumbItem[] = [


    {
        title: 'My Custom Frameworks',
        href: customFrameworkRoutes.index().url
    },
    {
        title: 'Create Framework',
        href: customFrameworkRoutes.create().url
    },
];

// Methods
function handleSubcategoryChange(value: string): void {
    if (value === 'custom') {
        showCustomSubcategory.value = true;
        formData.value.subcategory = '';
    } else if (value === 'none') {
        showCustomSubcategory.value = false;
        formData.value.subcategory = '';
    } else {
        showCustomSubcategory.value = false;
        formData.value.subcategory = value;
    }
}

function handleSubmit(): void {
    // Create the framework with minimal data and redirect to edit
    router.post(customFrameworkRoutes.store().url, {
        name: formData.value.name,
        description: formData.value.description,
        category: formData.value.category,
        subcategory: formData.value.subcategory || null,
        // Create with empty schema initially
        fields: [{
            id: crypto.randomUUID(),
            key: crypto.randomUUID(), // Auto-generate UUID for field key
            title: 'What would you like to explore?',
            description: 'This is your first framework question. You can edit or replace it.'
        }],
        // Default values
        best_for: [],
    }, {
        onSuccess: () => {
            // The controller should redirect to edit page with the new framework ID
        }
    });
}

function handleCancel(): void {
    router.visit(customFrameworkRoutes.index().url);
}
</script>