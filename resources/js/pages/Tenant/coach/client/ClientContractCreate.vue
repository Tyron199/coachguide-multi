<template>

    <Head :title="`Create Contract - ${client.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Create Contract"
                    :description="`Create a new coaching agreement with ${client.name}`" />

                <div class="max-w-4xl">
                    <!-- Step 1: Template Selection -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div class="rounded-lg border bg-card p-6">
                            <h2 class="text-lg font-medium mb-4">Choose Contract Template</h2>
                            <p class="text-sm text-muted-foreground mb-6">
                                Select a contract template that best fits your coaching agreement with {{ client.name
                                }}.
                            </p>

                            <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                                <div v-for="template in availableTemplates" :key="template.path"
                                    @click="selectTemplate(template)" :class="[
                                        'relative cursor-pointer rounded-lg border p-4 hover:border-primary transition-colors',
                                        selectedTemplate?.path === template.path ? 'border-primary bg-primary/5' : 'border-border'
                                    ]">

                                    <!-- Selection indicator -->
                                    <div v-if="selectedTemplate?.path === template.path"
                                        class="absolute top-2 right-2 w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                        <Check class="w-3 h-3 text-primary-foreground" />
                                    </div>

                                    <div class="space-y-3">
                                        <div>
                                            <h3 class="font-medium">{{ template.title }}</h3>
                                            <p v-if="template.description" class="text-sm text-muted-foreground mt-1">
                                                {{ template.description }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                            <div class="flex items-center gap-1">
                                                <FileText class="w-3 h-3" />
                                                {{ template.categories_count }} sections
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <Edit class="w-3 h-3" />
                                                {{ template.total_fields }} fields
                                            </div>
                                        </div>

                                        <Badge variant="outline" class="text-xs">
                                            v{{ template.version }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div v-if="availableTemplates.length === 0" class="text-center py-8">
                                <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 class="mt-2 text-sm font-medium">No templates available</h3>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    No contract templates were found in the system.
                                </p>
                            </div>
                        </div>

                        <!-- Step Navigation -->
                        <div class="flex justify-between">
                            <Link :href="contracts.index(client.id).url">
                            <Button variant="outline">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Contracts
                            </Button>
                            </Link>
                            <Button @click="nextStep" :disabled="!selectedTemplate">
                                Continue
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Step 2: Contract Details Form -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div class="rounded-lg border bg-card p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-medium">Contract Details</h2>
                                    <p class="text-sm text-muted-foreground">
                                        Using template: {{ selectedTemplate?.title }}
                                    </p>
                                </div>
                                <Button variant="outline" size="sm" @click="changeTemplate">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Change Template
                                </Button>
                            </div>

                            <Form :action="contracts.store(client.id)" class="space-y-6"
                                v-slot="{ errors, processing }">
                                <input type="hidden" name="template_path" :value="selectedTemplate?.path" />

                                <!-- Dynamic form fields will be loaded here -->
                                <div v-if="templateVariables" class="space-y-8">
                                    <div v-for="(category, categoryKey) in templateVariables" :key="categoryKey"
                                        class="space-y-4">

                                        <!-- Skip auto-populated categories -->
                                        <template v-if="!category.auto_populate">
                                            <div class="border-b pb-2">
                                                <h3 class="text-base font-medium">{{ category.label }}</h3>
                                                <p v-if="category.description"
                                                    class="text-sm text-muted-foreground mt-1">
                                                    {{ category.description }}
                                                </p>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <div v-for="(field, fieldKey) in category.fields" :key="fieldKey"
                                                    class="grid gap-2">

                                                    <!-- Text/Email/Phone inputs -->
                                                    <template
                                                        v-if="['text', 'email', 'phone', 'currency'].includes(field.type)">
                                                        <Label :for="String(fieldKey)">
                                                            {{ field.label }}
                                                            <span v-if="field.required"
                                                                class="text-destructive">*</span>
                                                        </Label>
                                                        <Input :id="String(fieldKey)" :name="String(fieldKey)"
                                                            :type="field.type === 'email' ? 'email' : field.type === 'phone' ? 'tel' : 'text'"
                                                            :placeholder="field.placeholder || `Enter ${field.label.toLowerCase()}`"
                                                            :required="field.required"
                                                            :model-value="formData[fieldKey] || field.default || ''"
                                                            @input="updateFormData(String(fieldKey), $event.target.value)" />
                                                        <InputError :message="errors[fieldKey]" />
                                                    </template>

                                                    <!-- Date inputs -->
                                                    <template v-if="field.type === 'date'">
                                                        <Label :for="String(fieldKey)">
                                                            {{ field.label }}
                                                            <span v-if="field.required"
                                                                class="text-destructive">*</span>
                                                        </Label>
                                                        <Input :id="String(fieldKey)" :name="String(fieldKey)"
                                                            type="date" :required="field.required"
                                                            :model-value="formData[fieldKey] || getDefaultDate(field.default)"
                                                            @input="updateFormData(String(fieldKey), $event.target.value)" />
                                                        <InputError :message="errors[fieldKey]" />
                                                    </template>

                                                    <!-- Number inputs -->
                                                    <template v-if="field.type === 'number'">
                                                        <Label :for="String(fieldKey)">
                                                            {{ field.label }}
                                                            <span v-if="field.required"
                                                                class="text-destructive">*</span>
                                                        </Label>
                                                        <Input :id="String(fieldKey)" :name="String(fieldKey)"
                                                            type="number" :min="field.min" :max="field.max"
                                                            :required="field.required"
                                                            :model-value="formData[fieldKey] || field.default || ''"
                                                            @input="updateFormData(String(fieldKey), $event.target.value)" />
                                                        <InputError :message="errors[fieldKey]" />
                                                    </template>

                                                    <!-- Select dropdowns -->
                                                    <template v-if="field.type === 'select'">
                                                        <Label :for="String(fieldKey)">
                                                            {{ field.label }}
                                                            <span v-if="field.required"
                                                                class="text-destructive">*</span>
                                                        </Label>
                                                        <Select :name="String(fieldKey)"
                                                            :model-value="formData[fieldKey] || field.default || ''"
                                                            @update:model-value="updateFormData(String(fieldKey), $event)">
                                                            <SelectTrigger>
                                                                <SelectValue
                                                                    :placeholder="`Select ${field.label.toLowerCase()}`" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem v-for="option in field.options"
                                                                    :key="option" :value="option">
                                                                    {{ option }}
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError :message="errors[fieldKey]" />
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="flex justify-between pt-6 border-t">
                                    <Button type="button" variant="outline" @click="previousStep">
                                        <ArrowLeft class="mr-2 h-4 w-4" />
                                        Back
                                    </Button>
                                    <Button type="submit" :disabled="processing">
                                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                        <FileText class="mr-2 h-4 w-4" />
                                        Create Contract
                                    </Button>
                                </div>
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Edit,
    FileText,
    LoaderCircle
} from 'lucide-vue-next';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import contracts from '@/routes/tenant/coach/clients/contracts';
import PageHeader from '@/components/PageHeader.vue';
import axios from 'axios';

interface Template {
    path: string;
    filename: string;
    title: string;
    description?: string;
    version: string;
    categories_count: number;
    total_fields: number;
}

const props = defineProps<{
    client: Client;
    availableTemplates: Template[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: clients.index().url,
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url,
    },
    {
        title: 'Contracts',
        href: contracts.index(props.client.id).url,
    },
    {
        title: 'Create Contract',
        href: contracts.create(props.client.id).url,
    }
];

const currentStep = ref(1);
const selectedTemplate = ref<Template | null>(null);
const templateVariables = ref<any>(null);
const formData = ref<Record<string, any>>({});

const selectTemplate = (template: Template) => {
    selectedTemplate.value = template;
};

const changeTemplate = () => {
    currentStep.value = 1;
    selectedTemplate.value = null;
    templateVariables.value = null;
    formData.value = {};
};

const nextStep = async () => {
    if (currentStep.value === 1 && selectedTemplate.value) {
        // Load template variables for the selected template
        try {
            const response = await axios.get(`/api/templates/${selectedTemplate.value.path}/variables`);
            templateVariables.value = response.data;
            currentStep.value = 2;
        } catch (error) {
            console.error('Failed to load template variables:', error);
        }
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const updateFormData = (key: string, value: any) => {
    formData.value[key] = value;
};

const getDefaultDate = (defaultValue?: string) => {
    if (defaultValue === 'current_date') {
        return new Date().toISOString().split('T')[0];
    }
    return defaultValue || '';
};


</script>
