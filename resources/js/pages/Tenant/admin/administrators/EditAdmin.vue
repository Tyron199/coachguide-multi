<template>

    <Head :title="`Edit ${admin.name} - Administrator`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdministratorsLayout>
            <div class="space-y-6">
                <PageHeader :title="`Edit ${admin.name}`"
                    description="Update administrator information and profile details" />

                <Form :action="AdminController.update(admin.id)" class="space-y-6" v-slot="{ errors, processing }">
                    <!-- Basic Information Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Basic Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" name="name" type="text" :model-value="admin.name"
                                    placeholder="Enter administrator's full name" required autocomplete="name" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email Address</Label>
                                <Input id="email" name="email" type="email" :model-value="admin.email"
                                    placeholder="Enter administrator's email address" required autocomplete="email" />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" name="phone" type="tel" :model-value="admin.phone || ''"
                                    placeholder="Enter administrator's phone number" autocomplete="tel" />
                                <InputError :message="errors.phone" />
                            </div>
                        </div>
                    </div>

                    <!-- Administrator Status Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Administrator Status</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ admin.status === 'active' ? 'Active' :
                                    'Inactive' }}</div>
                                <div class="text-sm text-muted-foreground">Current Status</div>
                            </div>
                            <div class="text-center p-4 bg-muted/50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ formatDate(admin.created_at) }}</div>
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

                            <Button v-if="!admin.archived && !props.isSelf" type="button" variant="outline"
                                @click="handleArchive">
                                <Archive class="mr-2 h-4 w-4" />
                                Archive Administrator
                            </Button>

                            <Button v-else-if="admin.archived" type="button" variant="outline" @click="handleUnarchive">
                                <ArchiveRestore class="mr-2 h-4 w-4" />
                                Unarchive Administrator
                            </Button>
                        </div>

                        <div class="flex gap-3">
                            <Link :href="administrators.show(admin.id).url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Update Administrator
                            </Button>
                        </div>
                    </div>
                </Form>
            </div>
        </AdministratorsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AdministratorsLayout from '@/layouts/administrators/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import administrators from '@/routes/tenant/admin/administrators';
import AdminController from '@/actions/App/Http/Controllers/Tenant/Admin/AdminController';
import { LoaderCircle, Save, Mail, Archive, ArchiveRestore } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { formatDate } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Administrator {
    id: number;
    name: string;
    email: string;
    phone?: string;
    archived: boolean;
    status: string;
    created_at: string;
}

interface Props {
    admin: Administrator;
    isSelf: boolean;
}

const props = defineProps<Props>();

const handleSendInvitation = async () => {
    const confirmed = await alertConfirm({
        title: 'Send Invitation',
        description: 'Send an invitation email to this administrator?',
        confirmText: 'Send Invitation',
        variant: 'default'
    });

    if (confirmed) {
        router.post(administrators.invite(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleArchive = async () => {
    const confirmed = await alertConfirm({
        title: 'Archive Administrator',
        description: 'Are you sure you want to archive this administrator? They will no longer have access to the platform.',
        confirmText: 'Archive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(administrators.archive(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const handleUnarchive = async () => {
    const confirmed = await alertConfirm({
        title: 'Unarchive Administrator',
        description: 'Are you sure you want to unarchive this administrator? They will regain access to the platform.',
        confirmText: 'Unarchive',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(administrators.unarchive(props.admin.id).url, {}, {
            preserveScroll: true
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: 'Administrators', href: administrators.index().url },
    { title: props.admin.name, href: administrators.show(props.admin.id).url },
    { title: 'Edit', href: '#' },
];
</script>