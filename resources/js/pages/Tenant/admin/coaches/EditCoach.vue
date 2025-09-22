<template>

    <Head :title="`Edit ${coach.name} - Coach`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachesLayout>
            <div class="space-y-6">
                <PageHeader :title="`Edit ${coach.name}`" description="Update coach information and profile details" />

                <Form :action="CoachController.update(coach.id)" class="space-y-6" v-slot="{ errors, processing }">
                    <!-- Basic Information Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Basic Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" name="name" type="text" :model-value="coach.name"
                                    placeholder="Enter coach's full name" required autocomplete="name" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email Address</Label>
                                <Input id="email" name="email" type="email" :model-value="coach.email"
                                    placeholder="Enter coach's email address" required autocomplete="email" />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" name="phone" type="tel" :model-value="coach.phone || ''"
                                    placeholder="Enter coach's phone number" autocomplete="tel" />
                                <InputError :message="errors.phone" />
                            </div>
                        </div>
                    </div>

                    <!-- Coach Statistics Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Coach Statistics</h2>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ coach.assigned_clients_count || 0 }}
                                </div>
                                <div class="text-sm text-muted-foreground">Assigned Clients</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ coach.status === 'active' ? 'Active' :
                                    'Inactive' }}</div>
                                <div class="text-sm text-muted-foreground">Status</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ formatDate(coach.created_at) }}</div>
                                <div class="text-sm text-muted-foreground">Member Since</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-4">
                        <div class="flex gap-2">
                            <Button type="button" variant="outline" @click="handleSendInvitation">
                                <Mail class="mr-2 h-4 w-4" />
                                Send Invitation
                            </Button>

                            <Button v-if="!coach.archived" type="button" variant="outline" @click="handleArchive">
                                <Archive class="mr-2 h-4 w-4" />
                                Archive Coach
                            </Button>

                            <Button v-else type="button" variant="outline" @click="handleUnarchive">
                                <ArchiveRestore class="mr-2 h-4 w-4" />
                                Unarchive Coach
                            </Button>
                        </div>

                        <div class="flex gap-3">
                            <Link :href="coaches.show(coach.id).url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Update Coach
                            </Button>
                        </div>
                    </div>
                </Form>
            </div>
        </CoachesLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachesLayout from '@/layouts/coaches/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import coaches from '@/routes/tenant/admin/coaches';
import CoachController from '@/actions/App/Http/Controllers/Tenant/Admin/CoachController';
import { LoaderCircle, Save, Mail, Archive, ArchiveRestore } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { formatDate } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Coach {
    id: number;
    name: string;
    email: string;
    phone?: string;
    assigned_clients_count: number;
    archived: boolean;
    status: string;
    created_at: string;
}

interface Props {
    coach: Coach;
}

const props = defineProps<Props>();

const handleSendInvitation = async () => {
    const confirmed = await alertConfirm({
        title: 'Send Invitation',
        description: 'Send an invitation email to this coach?',
        confirmText: 'Send Invitation',
        variant: 'default'
    });

    if (confirmed) {
        router.post(coaches.invite(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleArchive = async () => {
    const confirmed = await alertConfirm({
        title: 'Archive Coach',
        description: 'Are you sure you want to archive this coach? They will no longer have access to the platform.',
        confirmText: 'Archive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(coaches.archive(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleUnarchive = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Coach',
        description: 'Are you sure you want to unarchive this coach? They will regain access to the platform.',
        confirmText: 'Unarchive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(coaches.unarchive(props.coach.id).url, {}, {
            preserveScroll: true
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: 'Coaches', href: coaches.index().url },
    { title: props.coach.name, href: coaches.show(props.coach.id).url },
    { title: 'Edit', href: '#' },
];
</script>
