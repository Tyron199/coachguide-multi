<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { alertSuccess, alertError } from '@/plugins/alert';
import { MessageSquare, Upload, X } from 'lucide-vue-next';
import { store as storeSupportRequest } from '@/actions/App/Http/Controllers/Tenant/SupportController';
import type { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();

const subject = ref('');
const message = ref('');
const files = ref<File[]>([]);

const subjectOptions = [
    { value: 'Request help', label: 'Request help' },
    { value: 'Found a bug', label: 'Found a bug' },
    { value: 'Suggest a feature', label: 'Suggest a feature' },
    { value: 'General message', label: 'General message' },
];

const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    subject: '',
    message: '',
    attachments: [] as File[],
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        files.value = [...files.value, ...Array.from(target.files)];
        // Reset input so same file can be selected again if removed
        target.value = '';
    }
};

const removeFile = (index: number) => {
    files.value = files.value.filter((_, i) => i !== index);
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const handleSubmit = () => {
    if (!subject.value) {
        alertError('Please select a subject');
        return;
    }
    if (!message.value.trim()) {
        alertError('Please enter a message');
        return;
    }

    form.subject = subject.value;
    form.message = message.value;
    form.attachments = files.value;

    form.post(storeSupportRequest(), {
        preserveScroll: true,
        onSuccess: () => {
            alertSuccess('Thank you for your message! We\'ll get back to you soon.');

            // Reset form
            subject.value = '';
            message.value = '';
            files.value = [];
            form.reset();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join(', ');
            alertError(errorMessage || 'Oops! There was a problem submitting your form. Please try again.');
        },
    });
};

const canSubmit = computed(() => {
    return subject.value && message.value.trim() && !form.processing;
});
</script>

<template>

    <Head title="Support" />

    <AppLayout>
        <div class="container mx-auto max-w-3xl space-y-8 py-8">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">Support</h1>
                <p class="text-muted-foreground">
                    Need help or have feedback? We're here to listen. Send us a message and we'll get back to you as
                    soon as possible.
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MessageSquare class="h-5 w-5" />
                        Contact Form
                    </CardTitle>
                    <CardDescription>
                        Choose a category and tell us what's on your mind
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <!-- User Information (Readonly) -->
                        <div class="space-y-4 rounded-lg bg-muted/50 p-4">
                            <p class="text-sm font-medium text-muted-foreground">Your Contact Information</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="user-name">Name</Label>
                                    <Input id="user-name" :model-value="page.props.auth.user.name" readonly
                                        class="bg-background" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="user-email">Email</Label>
                                    <Input id="user-email" :model-value="page.props.auth.user.email" readonly
                                        class="bg-background" />
                                </div>
                            </div>
                        </div>

                        <!-- Subject Field -->
                        <div class="space-y-2">
                            <Label for="subject">Subject <span class="text-destructive">*</span></Label>
                            <Select v-model="subject">
                                <SelectTrigger id="subject">
                                    <SelectValue placeholder="Select a category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in subjectOptions" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Message Field -->
                        <div class="space-y-2">
                            <Label for="message">Message <span class="text-destructive">*</span></Label>
                            <Textarea id="message" v-model="message"
                                placeholder="Tell us more about your request, issue, or feedback..." rows="8"
                                class="resize-none" />
                        </div>

                        <!-- File Attachments -->
                        <div class="space-y-2">
                            <Label>Image Attachments (optional)</Label>
                            <p class="text-xs text-muted-foreground">You can attach screenshots or images to help
                                explain your issue</p>
                            <div class="space-y-3">
                                <Button type="button" variant="outline" @click="fileInput?.click()"
                                    class="w-full sm:w-auto">
                                    <Upload class="mr-2 h-4 w-4" />
                                    Add Files
                                </Button>
                                <input ref="fileInput" type="file" multiple @change="handleFileSelect" class="hidden"
                                    accept="image/*" />

                                <!-- File List -->
                                <div v-if="files.length > 0" class="space-y-2">
                                    <div v-for="(file, index) in files" :key="index"
                                        class="flex items-center justify-between rounded-md border p-3">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="flex-1 overflow-hidden">
                                                <p class="truncate text-sm font-medium">{{ file.name }}</p>
                                                <p class="text-xs text-muted-foreground">
                                                    {{ formatFileSize(file.size) }}
                                                </p>
                                            </div>
                                        </div>
                                        <Button type="button" variant="ghost" size="sm" @click="removeFile(index)"
                                            class="h-8 w-8 p-0">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <Button type="submit" :disabled="!canSubmit" class="min-w-32">
                                <span v-if="!form.processing">Send Message</span>
                                <span v-else>Sending...</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
