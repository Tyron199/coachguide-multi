<template>

    <Head :title="`${company.name} - Company Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CompanyLayout :company="company">
            <div class="space-y-6">
                <PageHeader title="Company Details" description="Overview of company information and profile">
                    <template #actions>
                        <Link :href="companies.edit(company.id).url">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Company
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <div class="grid gap-6">
                    <!-- Basic Information Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Basic Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Company Name</Label>
                                <p class="mt-1">{{ company.name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Industry Sector</Label>
                                <p class="mt-1">{{ company.industry_sector || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Total Clients</Label>
                                <p class="mt-1">{{ company.users_count || 0 }} clients</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Date Added</Label>
                                <p class="mt-1">{{ formatDate(company.created_at) }}</p>
                            </div>
                        </div>

                        <div class="mt-4" v-if="company.address">
                            <Label class="text-sm font-medium text-muted-foreground">Address</Label>
                            <p class="mt-1 whitespace-pre-line">{{ company.address }}</p>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Contact Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Contact Person</Label>
                                <p class="mt-1">{{ company.contact_person_name || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Position/Title</Label>
                                <p class="mt-1">{{ company.contact_person_position || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                <p class="mt-1">{{ company.contact_person_email || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Phone Number</Label>
                                <p class="mt-1">{{ company.contact_person_phone || 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Information Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Billing Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Billing Contact</Label>
                                <p class="mt-1">{{ company.billing_contact_name || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Billing Email</Label>
                                <p class="mt-1">{{ company.billing_contact_email || 'Not provided' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Billing Phone</Label>
                                <p class="mt-1">{{ company.billing_contact_phone || 'Not provided' }}</p>
                            </div>
                        </div>

                        <div class="mt-4" v-if="company.invoicing_notes">
                            <Label class="text-sm font-medium text-muted-foreground">Invoicing Notes</Label>
                            <p class="mt-1 whitespace-pre-line">{{ company.invoicing_notes }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </CompanyLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CompanyLayout from '@/layouts/company/Layout.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type Company } from '@/types';
import companies from '@/routes/tenant/coach/companies';
import { Edit } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';


const props = defineProps<{
    company: Company & {
        users?: Array<{
            id: number;
            name: string;
            email: string;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Companies',
        href: companies.index().url,
    },
    {
        title: props.company.name,
        href: companies.show(props.company.id).url,
    }
];

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
