<template>

    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <component :is="layoutComponent" v-bind="layoutProps">
            <div class="space-y-6">
                <PageHeader title="Edit Note" :description="pageDescription" />

                <div class="rounded-lg border bg-card p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="title" class="text-sm font-medium">Note Title</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1"
                                placeholder="Enter note title..." :class="{ 'border-destructive': form.errors.title }"
                                required />
                            <InputError :message="form.errors.title" class="mt-1" />
                        </div>

                        <div v-if="note.session">
                            <Label class="text-sm font-medium">Associated Session</Label>
                            <div class="mt-1 p-3 bg-muted rounded-md">
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="h-4 w-4" />
                                    <span class="font-medium">Session {{ note.session.session_number }}</span>
                                    <span class="text-muted-foreground">-</span>
                                    <span>{{ formatDate(note.session.scheduled_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label for="content" class="text-sm font-medium">Note Content</Label>
                            <Textarea id="content" v-model="form.content" class="mt-1 min-h-[200px]"
                                placeholder="Enter your coaching notes here..."
                                :class="{ 'border-destructive': form.errors.content }" required />
                            <InputError :message="form.errors.content" class="mt-1" />
                        </div>

                        <!-- Existing Attachments -->
                        <div v-if="note.attachments && note.attachments.length > 0">
                            <Label class="text-sm font-medium">Current Attachments</Label>
                            <div class="mt-2">
                                <AttachmentList :attachments="note.attachments" :can-delete="true"
                                    @attachment-deleted="handleExistingAttachmentDeleted" />
                            </div>
                        </div>

                        <!-- Add New Attachments -->
                        <div>
                            <Label class="text-sm font-medium">Add New Attachments</Label>
                            <div class="mt-2 space-y-4">
                                <AttachmentPicker :current-attachments="selectedAttachments"
                                    @attachments-selected="handleAttachmentsSelected">
                                    <Button type="button" variant="outline">
                                        <Paperclip class="mr-2 h-4 w-4" />
                                        Add Attachments
                                    </Button>
                                </AttachmentPicker>

                                <AttachmentList v-if="selectedAttachments.length > 0" :attachments="selectedAttachments"
                                    :can-delete="true" @attachment-deleted="handleAttachmentDeleted" />
                            </div>
                        </div>

                        <div class="text-xs text-muted-foreground">
                            Created {{ formatDate(note.created_at) }}
                            <span v-if="note.updated_at !== note.created_at">
                                • Last updated {{ formatDate(note.updated_at) }}
                            </span>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link v-if="note.session_id" :href="sessionNotes(note.session_id).url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Link v-else :href="clients.notes(client.id).url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button v-if="canDelete" type="button" variant="destructive" @click="deleteNote"
                                :disabled="form.processing">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Update Note
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </component>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem, type Client, type CoachingNote } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import sessions from '@/routes/tenant/coach/coaching-sessions';
import { sessionNotes, destroy as destroyNoteAction, update as updateNoteAction } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingNoteController';
import { destroy as destroyAttachmentAction } from '@/actions/App/Http/Controllers/Tenant/AttachmentController';
import { Calendar, LoaderCircle, Trash2, Paperclip } from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';
import AttachmentPicker from '@/components/AttachmentPicker.vue';
import AttachmentList from '@/components/AttachmentList.vue';

const props = defineProps<{
    note: CoachingNote;
    client: Client;
    canDelete?: boolean;
}>();

const form = useForm({
    title: props.note.title,
    content: props.note.content,
    attachments: [] as File[],
});

const selectedAttachments = ref<any[]>([]);

// Dynamic layout selection based on context
const layoutComponent = computed(() => {
    return props.note.session ? CoachingSessionLayout : ClientLayout;
});

const layoutProps = computed(() => {
    return props.note.session ? { session: props.note.session } : { client: props.client };
});

const pageDescription = computed(() => {
    return props.note.session
        ? 'Update this session note'
        : 'Update this coaching note';
});

const pageTitle = computed(() => {
    return props.note.session
        ? `Edit Note - Session ${props.note.session.session_number}`
        : `Edit Note - ${props.client.name}`;
});

const breadcrumbs = computed((): BreadcrumbItem[] => {
    if (props.note.session) {
        // Session context breadcrumbs
        return [
            {
                title: 'Sessions',
                href: sessions.index().url,
            },
            {
                title: `Session ${props.note.session.session_number}`,
                href: sessions.show(props.note.session.id).url,
            },
            {
                title: 'Notes',
                href: sessions.notes(props.note.session.id).url,
            },
            {
                title: 'Edit Note',
                href: '#',
            }
        ];
    } else {
        // Client context breadcrumbs
        const clientBreadcrumbs: BreadcrumbItem[] = [
            {
                title: props.client.archived ? 'Archived Clients' : 'Clients',
                href: props.client.archived ? clients.archived().url : clients.index().url,
            },
            {
                title: props.client.name,
                href: clients.show(props.client.id).url,
            },
            {
                title: 'Notes',
                href: clients.notes(props.client.id).url,
            },
            {
                title: 'Edit Note',
                href: '#',
            }
        ];



        return clientBreadcrumbs;
    }
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const handleAttachmentsSelected = (attachments: any[]) => {
    selectedAttachments.value = attachments;
    // Extract the File objects for the form
    form.attachments = attachments.map(attachment => attachment.file).filter(Boolean);
};

const handleAttachmentDeleted = (attachment: any) => {
    const index = selectedAttachments.value.findIndex(a => a.file_name === attachment.file_name);
    if (index > -1) {
        selectedAttachments.value.splice(index, 1);
        // Update form attachments
        form.attachments = selectedAttachments.value.map(attachment => attachment.file).filter(Boolean);
    }
};

const handleExistingAttachmentDeleted = (attachment: any) => {
    // Delete existing attachment via API
    router.visit(destroyAttachmentAction(attachment.id), {
        preserveScroll: true,
        onSuccess: () => {
            // The page will be refreshed with updated data, so no need to mutate props
        }
    });
};

const submit = () => {
    // Add _method to the form data for method spoofing
    const formData = {
        ...form.data(),
        _method: 'put'
    };

    router.post(updateNoteAction(props.note.id).url, formData, {
        forceFormData: true,
        onSuccess: () => {
            // Will redirect based on context in controller
        },
    });
};



const deleteNote = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Note',
        description: `Are you sure you want to delete this note? This action cannot be undone.`,
        confirmText: 'Delete Note',
        variant: 'destructive'
    });

    if (confirmed) {
        router.visit(destroyNoteAction(props.note.id));
    }
};
</script>
