<template>

    <Head :title="`Edit ${client.name} - Client`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Edit Client" description="Update client information and profile details" />

                <Form :action="ClientController.update(client.id)" class="space-y-6" v-slot="{ errors, processing }">
                    <!-- Basic Information Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Basic Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" name="name" type="text" :model-value="client.name"
                                    placeholder="Enter client's full name" required autocomplete="name" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="birthdate">Date of Birth</Label>
                                <Input id="birthdate" name="birthdate" type="date"
                                    :model-value="formatDateForInput(client.profile?.birthdate)" />
                                <InputError :message="errors.birthdate" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="company_id">Company</Label>
                                <Select :model-value="formData.company_id?.toString() || 'null'"
                                    @update:model-value="selectCompany">
                                    <SelectTrigger class="w-full">
                                        <SelectValue>
                                            <span v-if="selectedCompany">{{ selectedCompany.name }}</span>
                                            <span v-else class="text-muted-foreground">No Company</span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="null">No Company</SelectItem>
                                        <SelectSeparator />
                                        <SelectItem v-for="company in companies" :key="company.id"
                                            :value="company.id.toString()">
                                            {{ company.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <input type="hidden" name="company_id" :value="formData.company_id" />
                                <InputError :message="errors.company_id" />
                            </div>

                            <div v-if="props.canAssignCoach && props.coaches && props.coaches.length > 0"
                                class="grid gap-2">
                                <Label for="assigned_coach_id">Assigned Coach</Label>
                                <Select :model-value="formData.assigned_coach_id?.toString() || 'null'"
                                    @update:model-value="selectCoach">
                                    <SelectTrigger class="w-full">
                                        <SelectValue>
                                            <span v-if="selectedCoach">{{ selectedCoach.name }}</span>
                                            <span v-else class="text-muted-foreground">No Coach Assigned</span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="null">No Coach Assigned</SelectItem>
                                        <SelectSeparator />
                                        <SelectItem v-for="coach in props.coaches" :key="coach.id"
                                            :value="coach.id.toString()">
                                            {{ coach.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <input type="hidden" name="assigned_coach_id" :value="formData.assigned_coach_id" />
                                <InputError :message="errors.assigned_coach_id" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Contact Details</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="email">Email Address</Label>
                                <Input id="email" name="email" type="email" :model-value="client.email"
                                    placeholder="Enter client's email address" required autocomplete="email" />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" name="phone" type="tel" :model-value="client.phone || ''"
                                    placeholder="Enter client's phone number" autocomplete="tel" />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Address</Label>
                                <Input id="address" name="address" type="text"
                                    :model-value="client.profile?.address || ''" placeholder="Enter client's address" />
                                <InputError :message="errors.address" />
                            </div>
                        </div>

                        <!-- Preferred Communication Method -->
                        <div class="grid gap-2 mt-4">
                            <Label>Preferred Communication Method</Label>
                            <RadioGroup v-model="formData.preferred_method_of_communication"
                                name="preferred_method_of_communication" class="flex gap-6">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="email" id="comm-email" />
                                    <Label for="comm-email">Email</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="phone" id="comm-phone" />
                                    <Label for="comm-phone">Phone</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="text" id="comm-text" />
                                    <Label for="comm-text">Text</Label>
                                </div>
                            </RadioGroup>
                            <InputError :message="errors.preferred_method_of_communication" />
                        </div>
                    </div>

                    <!-- Medical & Emergency Information Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Medical & Emergency Information</h2>

                        <!-- Medical Conditions -->
                        <div class="grid gap-2 mb-6">
                            <Label>Medical Conditions</Label>
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-2" v-if="formData.medical_conditions.length > 0">
                                    <div v-for="(condition, index) in formData.medical_conditions" :key="index"
                                        class="flex items-center gap-1 bg-secondary text-secondary-foreground px-2 py-1 rounded-md text-sm">
                                        <span>{{ condition }}</span>
                                        <Button type="button" variant="ghost" size="sm"
                                            @click="removeMedicalCondition(index)"
                                            class="h-4 w-4 p-0 hover:bg-destructive hover:text-destructive-foreground">
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Input v-model="newMedicalCondition" placeholder="Add medical condition"
                                        @keydown.enter.prevent="addMedicalCondition" />
                                    <Button type="button" @click="addMedicalCondition" variant="outline" size="sm">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <input v-for="(condition, index) in formData.medical_conditions" :key="index"
                                    type="hidden" :name="`medical_conditions[${index}]`" :value="condition" />
                            </div>
                            <InputError :message="errors.medical_conditions" />
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-2 block">Emergency
                                Contact</Label>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="emergency_contact_name">Contact Name</Label>
                                    <Input id="emergency_contact_name" name="emergency_contact_name" type="text"
                                        :model-value="client.profile?.emergency_contact_name || ''"
                                        placeholder="Enter emergency contact name" />
                                    <InputError :message="errors.emergency_contact_name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="emergency_contact_phone">Contact Phone</Label>
                                    <Input id="emergency_contact_phone" name="emergency_contact_phone" type="tel"
                                        :model-value="client.profile?.emergency_contact_phone || ''"
                                        placeholder="Enter emergency contact phone" />
                                    <InputError :message="errors.emergency_contact_phone" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="clients.show(client.id).url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                        </Link>
                        <Button type="submit" :disabled="processing">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            <SaveIcon class="mr-2 h-4 w-4" />
                            Update Client
                        </Button>
                    </div>
                </Form>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem, type Client, type Company } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import ClientController from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { Plus, X, LoaderCircle, SaveIcon } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

interface Props {
    client: Client;
    companies: Company[];
    coaches?: Array<{ id: number, name: string }>;
    canAssignCoach?: boolean;
}

const props = defineProps<Props>();

const formData = ref({
    company_id: props.client.company?.id || null,
    assigned_coach_id: props.client.assigned_coach?.id || null,
    preferred_method_of_communication: props.client.profile?.preferred_method_of_communication || '',
    medical_conditions: [...(props.client.profile?.medical_conditions || [])],
});

const newMedicalCondition = ref('');

const selectedCompany = computed(() => {
    if (formData.value.company_id === null) return null;
    return props.companies.find(c => c.id === Number(formData.value.company_id)) || null;
});

const selectedCoach = computed(() => {
    if (formData.value.assigned_coach_id === null) return null;
    return props.coaches?.find(c => c.id === Number(formData.value.assigned_coach_id)) || null;
});

const selectCompany = (value: any) => {
    formData.value.company_id = value === 'null' ? null : Number(value);
};

const selectCoach = (value: any) => {
    formData.value.assigned_coach_id = value === 'null' ? null : Number(value);
};

const addMedicalCondition = () => {
    if (newMedicalCondition.value.trim()) {
        formData.value.medical_conditions.push(newMedicalCondition.value.trim());
        newMedicalCondition.value = '';
    }
};

const removeMedicalCondition = (index: number) => {
    formData.value.medical_conditions.splice(index, 1);
};

const formatDateForInput = (dateString: string | undefined) => {
    if (!dateString) return '';
    return new Date(dateString).toISOString().split('T')[0];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: clients.index().url
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url
    },
    {
        title: 'Edit',
        href: clients.edit(props.client.id).url
    },
];
</script>