<template>

    <Head title="Add Client" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientsLayout>
            <div class="space-y-6">
                <PageHeader title="Add Client" description="Create a new client profile" />

                <div class="max-w-2xl">
                    <Form :action="ClientController.store()" class="space-y-6" v-slot="{ errors, processing }">
                        <div class="grid gap-2">
                            <Label for="name">Full Name</Label>
                            <Input id="name" name="name" type="text" class="mt-1 block w-full"
                                placeholder="Enter client's full name" required autocomplete="name" />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email Address</Label>
                            <Input id="email" name="email" type="email" class="mt-1 block w-full"
                                placeholder="Enter client's email address" required autocomplete="email" />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="company_id">Company</Label>
                            <Select :model-value="getCompanySelectValue()" @update:model-value="selectCompany">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="selectedCompany">{{ selectedCompany.name }}</span>
                                        <span v-else-if="formData.creating_new_company" class="flex items-center">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Create New Company
                                        </span>
                                        <span v-else class="text-muted-foreground">Select a company</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">No Company</SelectItem>
                                    <SelectSeparator />
                                    <SelectItem v-for="company in companies" :key="company.id"
                                        :value="company.id.toString()">
                                        {{ company.name }}
                                    </SelectItem>
                                    <SelectSeparator v-if="companies.length > 0" />
                                    <SelectItem value="new">
                                        <div class="flex items-center">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Create New Company
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="company_id" :value="formData.company_id" />
                            <InputError :message="errors.company_id" />
                        </div>

                        <div v-if="formData.creating_new_company" class="grid gap-2">
                            <Label for="new_company_name">New Company Name</Label>
                            <Input id="new_company_name" name="new_company_name" type="text" class="mt-1 block w-full"
                                placeholder="Enter new company name" v-model="formData.new_company_name" />
                            <InputError :message="errors.new_company_name" />
                        </div>

                        <div v-if="props.canAssignCoach && props.coaches && props.coaches.length > 0"
                            class="grid gap-2">
                            <Label for="assigned_coach_id">Assigned Coach</Label>
                            <Select :model-value="formData.assigned_coach_id?.toString() || 'null'"
                                @update:model-value="selectCoach">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="selectedCoach">{{ selectedCoach.name }}</span>
                                        <span v-else class="text-muted-foreground">Auto-assign to me</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">Auto-assign to me</SelectItem>
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

                        <div class="flex items-center space-x-2">
                            <Checkbox id="send_invitation" :model-value="formData.send_invitation"
                                @update:model-value="formData.send_invitation = Boolean($event)" />
                            <Label for="send_invitation"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Send invitation email to client
                            </Label>
                            <input type="hidden" name="send_invitation" :value="formData.send_invitation ? '1' : '0'" />
                            <InputError :message="errors.send_invitation" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="clients.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Add Client
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </ClientsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientsLayout from '@/layouts/clients/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem, type Company } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import ClientController from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';
import { LoaderCircle, Plus } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

interface Props {
    companies: Company[];
    coaches?: Array<{ id: number, name: string }>;
    canAssignCoach?: boolean;
}

const props = defineProps<Props>();

const formData = ref({
    company_id: null as number | null,
    new_company_name: '',
    creating_new_company: false,
    assigned_coach_id: null as number | null,
    send_invitation: true, // Default to checked
});

// Check for company_id in URL params and pre-fill if it exists
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const companyIdParam = urlParams.get('company_id');

    if (companyIdParam) {
        const companyId = Number(companyIdParam);
        // Verify the company exists in our list
        const company = props.companies.find(c => c.id === companyId);
        if (company) {
            formData.value.company_id = companyId;
        }
    }
});

const selectedCompany = computed(() => {
    if (formData.value.company_id === null || formData.value.creating_new_company) return null;
    return props.companies.find(c => c.id === Number(formData.value.company_id)) || null;
});

const selectedCoach = computed(() => {
    if (formData.value.assigned_coach_id === null) return null;
    return props.coaches?.find(c => c.id === Number(formData.value.assigned_coach_id)) || null;
});

const getCompanySelectValue = () => {
    if (formData.value.creating_new_company) return 'new';
    if (formData.value.company_id === null) return 'null';
    return formData.value.company_id.toString();
};

const selectCompany = (value: any) => {
    if (value === 'new') {
        formData.value.creating_new_company = true;
        formData.value.company_id = null;
    } else if (value === 'null') {
        formData.value.creating_new_company = false;
        formData.value.company_id = null;
        formData.value.new_company_name = '';
    } else {
        formData.value.creating_new_company = false;
        formData.value.company_id = Number(value);
        formData.value.new_company_name = '';
    }
};

const selectCoach = (value: any) => {
    formData.value.assigned_coach_id = value === 'null' ? null : Number(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: clients.index().url
    },
    {
        title: 'Add Client',
        href: clients.create().url
    },
];
</script>