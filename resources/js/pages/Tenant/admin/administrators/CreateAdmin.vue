<template>

    <Head title="Add Administrator" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdministratorsLayout>
            <div class="space-y-6">
                <PageHeader title="Add Administrator" description="Create a new administrator profile" />

                <div class="max-w-2xl">
                    <Form :action="AdminController.store()" class="space-y-6" v-slot="{ errors, processing }">
                        <div class="grid gap-2">
                            <Label for="name">Full Name</Label>
                            <Input id="name" name="name" type="text" class="mt-1 block w-full"
                                placeholder="Enter administrator's full name" required autocomplete="name" />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email Address</Label>
                            <Input id="email" name="email" type="email" class="mt-1 block w-full"
                                placeholder="Enter administrator's email address" required autocomplete="email" />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone Number (Optional)</Label>
                            <Input id="phone" name="phone" type="tel" class="mt-1 block w-full"
                                placeholder="Enter administrator's phone number" autocomplete="tel" />
                            <InputError :message="errors.phone" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="send_invitation" :model-value="formData.send_invitation"
                                @update:model-value="formData.send_invitation = Boolean($event)" />
                            <Label for="send_invitation"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Send notification email (invitation for new users, role update for existing users)
                            </Label>
                            <input type="hidden" name="send_invitation" :value="formData.send_invitation ? '1' : '0'" />
                            <InputError :message="errors.send_invitation" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="administrators.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Add Administrator
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </AdministratorsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdministratorsLayout from '@/layouts/administrators/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { type BreadcrumbItem } from '@/types';
import administrators from '@/routes/tenant/admin/administrators';
import AdminController from '@/actions/App/Http/Controllers/Tenant/Admin/AdminController';
import { LoaderCircle, Plus } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

const formData = ref({
    send_invitation: true, // Default to checked
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'User Management', href: '#' },
    { title: 'Administrators', href: administrators.index().url },
    { title: 'Add Administrator', href: '#' },
];
</script>
