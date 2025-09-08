<template>

    <Head :title="`Edit ${company.name} - Company`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CompanyLayout :company="company">
            <div class="space-y-6">
                <PageHeader title="Edit Company" description="Update company information and details" />

                <div class="max-w-4xl">
                    <Form v-bind="CompanyController.update.form(company.id)" class="space-y-8"
                        v-slot="{ errors, processing }">
                        <!-- Basic Information -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Basic Information</h2>
                                <p class="text-sm text-muted-foreground">Company details and basic information.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="name">Company Name</Label>
                                    <Input id="name" name="name" type="text" :model-value="company.name"
                                        placeholder="Enter company name" required />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="industry_sector">Industry Sector</Label>
                                    <Input id="industry_sector" name="industry_sector" type="text"
                                        :model-value="company.industry_sector || ''"
                                        placeholder="e.g., Technology, Healthcare, Finance" />
                                    <InputError :message="errors.industry_sector" />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Address</Label>
                                <Textarea id="address" name="address" :model-value="company.address || ''"
                                    placeholder="Enter company address" rows="3" />
                                <InputError :message="errors.address" />
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Contact Information</h2>
                                <p class="text-sm text-muted-foreground">Primary contact person for this company.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="contact_person_name">Contact Person Name</Label>
                                    <Input id="contact_person_name" name="contact_person_name" type="text"
                                        :model-value="company.contact_person_name || ''"
                                        placeholder="Enter contact person name" />
                                    <InputError :message="errors.contact_person_name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="contact_person_position">Position/Title</Label>
                                    <Input id="contact_person_position" name="contact_person_position" type="text"
                                        :model-value="company.contact_person_position || ''"
                                        placeholder="e.g., HR Manager, CEO" />
                                    <InputError :message="errors.contact_person_position" />
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="contact_person_email">Contact Email</Label>
                                    <Input id="contact_person_email" name="contact_person_email" type="email"
                                        :model-value="company.contact_person_email || ''"
                                        placeholder="Enter contact email" />
                                    <InputError :message="errors.contact_person_email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="contact_person_phone">Contact Phone</Label>
                                    <Input id="contact_person_phone" name="contact_person_phone" type="tel"
                                        :model-value="company.contact_person_phone || ''"
                                        placeholder="Enter contact phone" />
                                    <InputError :message="errors.contact_person_phone" />
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Billing Information</h2>
                                <p class="text-sm text-muted-foreground">Billing contact and invoicing details.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-3">
                                <div class="grid gap-2">
                                    <Label for="billing_contact_name">Billing Contact Name</Label>
                                    <Input id="billing_contact_name" name="billing_contact_name" type="text"
                                        :model-value="company.billing_contact_name || ''"
                                        placeholder="Enter billing contact name" />
                                    <InputError :message="errors.billing_contact_name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="billing_contact_email">Billing Email</Label>
                                    <Input id="billing_contact_email" name="billing_contact_email" type="email"
                                        :model-value="company.billing_contact_email || ''"
                                        placeholder="Enter billing email" />
                                    <InputError :message="errors.billing_contact_email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="billing_contact_phone">Billing Phone</Label>
                                    <Input id="billing_contact_phone" name="billing_contact_phone" type="tel"
                                        :model-value="company.billing_contact_phone || ''"
                                        placeholder="Enter billing phone" />
                                    <InputError :message="errors.billing_contact_phone" />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="invoicing_notes">Invoicing Notes</Label>
                                <Textarea id="invoicing_notes" name="invoicing_notes"
                                    :model-value="company.invoicing_notes || ''"
                                    placeholder="Any special invoicing instructions or notes..." rows="4" />
                                <InputError :message="errors.invoicing_notes" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="companies.show(company.id).url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                Update Company
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </CompanyLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CompanyLayout from '@/layouts/company/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem, type Company } from '@/types';
import companies from '@/routes/tenant/coach/companies';
import CompanyController from '@/actions/App/Http/Controllers/Tenant/Coach/CompanyController';
import { LoaderCircle } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
    company: Company;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Companies',
        href: companies.index().url
    },
    {
        title: props.company.name,
        href: companies.show(props.company.id).url
    },
    {
        title: 'Edit',
        href: companies.edit(props.company.id).url
    },
];
</script>
