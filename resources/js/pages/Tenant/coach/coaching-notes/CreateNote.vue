<template>

    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <component :is="layoutComponent" v-bind="layoutProps">
            <div class="space-y-6">
                <PageHeader title="Add Note" :description="pageDescription" />

                <div class="rounded-lg border bg-card p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="title" class="text-sm font-medium">Note Title</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1"
                                placeholder="Enter note title..." :class="{ 'border-destructive': form.errors.title }"
                                required />
                            <InputError :message="form.errors.title" class="mt-1" />
                        </div>

                        <div v-if="session">
                            <Label class="text-sm font-medium">Associated Session</Label>
                            <div class="mt-1 p-3 bg-muted rounded-md">
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="h-4 w-4" />
                                    <span class="font-medium">Session {{ session.session_number }}</span>
                                    <span class="text-muted-foreground">-</span>
                                    <span>{{ formatDate(session.scheduled_at) }}</span>
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

                        <div>
                            <Label class="text-sm font-medium">Attachments</Label>
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

                        <div class="flex justify-end gap-3 pt-4">
                            <Button type="button" variant="outline" @click="cancel">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Save Note
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </component>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem, type Client, type CoachingSession } from '@/types';
import clients from '@/routes/tenant/clients';
import sessions from '@/routes/tenant/coaching-sessions';
import { sessionNotes, store as storeNoteAction } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingNoteController';
import { computed } from 'vue';
import { Calendar, LoaderCircle, Paperclip } from 'lucide-vue-next';
import AttachmentPicker from '@/components/AttachmentPicker.vue';
import AttachmentList from '@/components/AttachmentList.vue';

const props = defineProps<{
    client: Client;
    session?: CoachingSession;
}>();

const form = useForm({
    title: '',
    content: '',
    client_id: props.client.id,
    session_id: props.session?.id || null,
    attachments: [] as File[],
});

const selectedAttachments = ref<any[]>([]);

// Dynamic layout selection based on context
const layoutComponent = computed(() => {
    return props.session ? CoachingSessionLayout : ClientLayout;
});

const layoutProps = computed(() => {
    return props.session ? { session: props.session } : { client: props.client };
});

const pageDescription = computed(() => {
    return props.session
        ? 'Create a new note for this coaching session'
        : 'Create a new coaching note for this client';
});

const pageTitle = computed(() => {
    return props.session
        ? `Add Note - Session ${props.session.session_number}`
        : `Add Note - ${props.client.name}`;
});

const breadcrumbs = computed((): BreadcrumbItem[] => {
    if (props.session) {
        // Session context breadcrumbs
        return [
            {
                title: 'Sessions',
                href: sessions.index().url,
            },
            {
                title: `Session ${props.session.session_number}`,
                href: sessions.show(props.session.id).url,
            },
            {
                title: 'Notes',
                href: sessions.notes(props.session.id).url,
            },
            {
                title: 'Add Note',
                href: '#',
            }
        ];
    } else {
        // Client context breadcrumbs
        const clientBreadcrumbs: BreadcrumbItem[] = [
            {
                title: 'Clients',
                href: clients.index().url,
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
                title: 'Add Note',
                href: '#',
            }
        ];

        // If client is archived, insert archived clients breadcrumb
        if (props.client.archived) {
            clientBreadcrumbs.splice(1, 0, {
                title: 'Archived Clients',
                href: clients.archived().url,
            });
        }

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

const submit = () => {
    form.post(storeNoteAction().url, {
        forceFormData: true,
        onSuccess: () => {
            // Will redirect based on context in controller
        },
    });
};

const cancel = () => {
    if (props.session) {
        router.visit(sessionNotes(props.session.id).url);
    } else {
        router.visit(clients.notes(props.client.id).url);
    }
};
</script>
