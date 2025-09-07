<script setup lang="ts">
import avatar from '@/routes/tenant/avatar';
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { ref } from 'vue';

interface Props {
    mustVerifyEmail?: boolean;
    status?: string;
    user: User;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile photo',
        href: avatar.index().url,
    },
];

const user = props.user;
const isUploading = ref(false);

const form = useForm({
    avatar: null as File | null
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.avatar = file;
        isUploading.value = true;

        // Clear any previous errors
        form.clearErrors();

        // Automatically upload when file is selected
        form.post(avatar.update().url, {
            preserveScroll: true,
            onSuccess: () => {
                isUploading.value = false;
                window.location.reload();
            },
            onError: (errors) => {
                isUploading.value = false;
                console.error('Upload errors:', errors);
            },
            onFinish: () => {
                isUploading.value = false;
                // Reset the file input
                if (target) {
                    target.value = '';
                }
            },
        });
    }
};

const clearAvatar = () => {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        isUploading.value = true;
        form.delete(avatar.destroy().url, {
            preserveScroll: true,
            onSuccess: () => {
                isUploading.value = false;
                window.location.reload();
            },
            onError: (errors) => {
                isUploading.value = false;
                console.error('Delete errors:', errors);
            },
            onFinish: () => {
                isUploading.value = false;
            },
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Profile photo" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile photo"
                    description="Upload your profile photo to personalize your account. Supports JPEG, PNG, GIF, WebP, and SVG formats." />

                <div class="space-y-6">
                    <div class="space-y-4">
                        <!-- Current Avatar Preview -->
                        <div class="mb-4">
                            <Label>Current Profile Photo</Label>
                            <div class="mt-2">
                                <div v-if="user.avatar" class="flex items-center space-x-4">
                                    <img :src="user.avatar" alt="Current Profile Photo"
                                        class="h-20 w-20 rounded-full object-cover">
                                    <Button variant="destructive" @click="clearAvatar" :disabled="isUploading">
                                        {{ isUploading ? 'Removing...' : 'Remove Photo' }}
                                    </Button>
                                </div>
                                <div v-else class="flex items-center space-x-4">
                                    <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">No photo</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">No profile photo uploaded</span>
                                </div>
                            </div>
                        </div>

                        <!-- Avatar Upload Form -->
                        <div class="space-y-2">
                            <Label>{{ user.avatar ? 'Upload New Profile Photo' : 'Upload Profile Photo' }}</Label>
                            <div class="relative">
                                <input type="file" @change="handleFileUpload" accept="image/*" :disabled="isUploading"
                                    class="block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-md file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-primary file:text-primary-foreground
                                              hover:file:bg-primary/90
                                              disabled:opacity-50 disabled:cursor-not-allowed
                                              file:disabled:bg-gray-400" />
                                <div v-if="isUploading"
                                    class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-md">
                                    <span class="text-sm text-gray-600">Uploading...</span>
                                </div>
                            </div>
                            <InputError :message="form.errors.avatar" class="mt-2" />
                            <p class="text-sm text-muted-foreground mt-1">
                                All image formats supported (JPEG, PNG, GIF, WebP, SVG), max 2MB
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
