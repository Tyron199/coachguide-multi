<template>

    <Head :title="`Edit Contract - ${client.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Edit Contract" :description="`Modify coaching agreement with ${client.name}`">
                    <template #actions>
                        <!-- <Button variant="outline" @click="previewContract">
                            <Eye class="mr-2 h-4 w-4" />
                            Preview Contract
                        </Button> -->
                    </template>
                </PageHeader>

                <div class="max-w-4xl">
                    <!-- Contract Info -->
                    <div class="rounded-lg border bg-card p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium">{{ templateInfo?.title || 'Contract' }}</h2>
                                <p class="text-sm text-muted-foreground">
                                    {{ templateInfo?.description || 'Coaching agreement template' }}
                                </p>
                            </div>
                            <Badge variant="secondary">
                                {{ getStatusLabel(contract.status) }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <div v-if="contract.status === 0" class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-medium mb-4">Contract Details</h3>

                        <Form :action="contracts.update([client.id, contract.id])" class="space-y-6"
                            v-slot="{ errors, processing }">

                            <!-- Dynamic form fields -->
                            <div v-if="templateVariables" class="space-y-8">
                                <div v-for="(category, categoryKey) in templateVariables" :key="categoryKey"
                                    class="space-y-4">

                                    <!-- Skip auto-populated categories -->
                                    <template v-if="!category.auto_populate">
                                        <div class="border-b pb-2">
                                            <h3 class="text-base font-medium">{{ category.label }}</h3>
                                            <p v-if="category.description" class="text-sm text-muted-foreground mt-1">
                                                {{ category.description }}
                                            </p>
                                        </div>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div v-for="(field, fieldKey) in category.fields" :key="String(fieldKey)"
                                                class="grid gap-2">

                                                <!-- Text/Email/Phone/Currency inputs -->
                                                <template
                                                    v-if="['text', 'email', 'phone', 'currency'].includes(field.type)">
                                                    <Label :for="String(fieldKey)">
                                                        {{ field.label }}
                                                        <span v-if="field.required" class="text-destructive">*</span>
                                                    </Label>
                                                    <Input :id="String(fieldKey)" :name="String(fieldKey)"
                                                        :type="field.type === 'email' ? 'email' : field.type === 'phone' ? 'tel' : 'text'"
                                                        :placeholder="field.placeholder || `Enter ${field.label.toLowerCase()}`"
                                                        :required="field.required"
                                                        :model-value="getFieldValue(String(fieldKey), field.default)" />
                                                    <InputError :message="errors[String(fieldKey)]" />
                                                </template>

                                                <!-- Date inputs -->
                                                <template v-if="field.type === 'date'">
                                                    <Label :for="String(fieldKey)">
                                                        {{ field.label }}
                                                        <span v-if="field.required" class="text-destructive">*</span>
                                                    </Label>
                                                    <Input :id="String(fieldKey)" :name="String(fieldKey)" type="date"
                                                        :required="field.required"
                                                        :model-value="getFieldValue(String(fieldKey), getDefaultDate(field.default))" />
                                                    <InputError :message="errors[String(fieldKey)]" />
                                                </template>

                                                <!-- Number inputs -->
                                                <template v-if="field.type === 'number'">
                                                    <Label :for="String(fieldKey)">
                                                        {{ field.label }}
                                                        <span v-if="field.required" class="text-destructive">*</span>
                                                    </Label>
                                                    <Input :id="String(fieldKey)" :name="String(fieldKey)" type="number"
                                                        :min="field.min" :max="field.max" :required="field.required"
                                                        :model-value="getFieldValue(String(fieldKey), field.default)" />
                                                    <InputError :message="errors[String(fieldKey)]" />
                                                </template>

                                                <!-- Select dropdowns -->
                                                <template v-if="field.type === 'select'">
                                                    <Label :for="String(fieldKey)">
                                                        {{ field.label }}
                                                        <span v-if="field.required" class="text-destructive">*</span>
                                                    </Label>
                                                    <Select :name="String(fieldKey)"
                                                        :model-value="getFieldValue(String(fieldKey), field.default)">
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                :placeholder="`Select ${field.label.toLowerCase()}`" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem v-for="option in field.options" :key="option"
                                                                :value="option">
                                                                {{ option }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError :message="errors[String(fieldKey)]" />
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between pt-6 border-t">
                                <!-- <Button type="button" variant="outline"
                                    @click="() => router.visit(contracts.index(client.id).url)">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Back to Contracts
                                </Button> -->
                                <div></div>
                                <div class="flex justify-end gap-3">
                                    <Link :href="contracts.show([client.id, contract.id]).url">
                                    <Button type="button" variant="outline">
                                        Cancel
                                    </Button>
                                    </Link>
                                    <Button type="submit" :disabled="processing">
                                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                        <SaveIcon class="mr-2 h-4 w-4" />
                                        Update Contract
                                    </Button>
                                </div>

                            </div>
                        </Form>
                    </div>

                    <!-- Read-only view for non-draft contracts -->
                    <div v-else class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Contract Details</h3>
                            <Badge :variant="getStatusVariant(contract.status)">
                                {{ getStatusLabel(contract.status) }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mb-4">
                            This contract cannot be edited because it has been {{
                                getStatusLabel(contract.status).toLowerCase() }}.
                        </p>

                        <!-- Show current values read-only -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-for="(value, key) in contract.variables" :key="key">
                                <Label class="text-sm font-medium text-muted-foreground">{{ formatFieldLabel(key)
                                }}</Label>
                                <p class="mt-1">{{ value || 'Not set' }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 border-t">
                            <Link :href="contracts.index(client.id).url">
                            <Button variant="outline">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Contracts
                            </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
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
    LoaderCircle,
    SaveIcon
} from 'lucide-vue-next';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import contracts from '@/routes/tenant/coach/clients/contracts';
import PageHeader from '@/components/PageHeader.vue';
import axios from 'axios';

interface Contract {
    id: number;
    template_path: string;
    variables: Record<string, any> | null;
    status: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    client: Client;
    contract: Contract;
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
        title: 'Edit Contract',
        href: contracts.edit([props.client.id, props.contract.id]).url,
    }
];

const templateVariables = ref<any>(null);
const templateInfo = ref<any>(null);

onMounted(async () => {
    try {
        const response = await axios.get(`/api/templates/${props.contract.template_path}/variables`);
        templateVariables.value = response.data;

        // Get template info
        const templatesResponse = await axios.get('/api/templates');
        const templates = templatesResponse.data;
        templateInfo.value = templates.find((t: any) => t.path === props.contract.template_path);
    } catch (error) {
        console.error('Failed to load template data:', error);
    }
});

const getFieldValue = (fieldKey: string, defaultValue: any = '') => {
    return props.contract.variables?.[fieldKey] || defaultValue || '';
};

const getDefaultDate = (defaultValue?: string) => {
    if (defaultValue === 'current_date') {
        return new Date().toISOString().split('T')[0];
    }
    return defaultValue || '';
};

const getStatusLabel = (status: number) => {
    const labels: Record<number, string> = {
        0: 'Draft',
        1: 'Sent',
        2: 'Viewed',
        3: 'Signed by Client',
        4: 'Countersigned',
        5: 'Active',
        6: 'Lapsed',
        7: 'Terminated',
        8: 'Void',
    };
    return labels[status] || 'Unknown';
};

const getStatusVariant = (status: number): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (status === 0) return 'secondary'; // Draft
    if (status >= 1 && status <= 2) return 'outline'; // Sent/Viewed
    if (status >= 3 && status <= 5) return 'default'; // Signed/Active
    return 'destructive'; // Ended states
};

const formatFieldLabel = (key: string) => {
    return key.split('_').map(word =>
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
};


</script>
