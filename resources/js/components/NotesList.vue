<template>
    <div class="space-y-4">
        <!-- Empty state -->
        <div v-if="notes.length === 0" class="text-center py-12">
            <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-foreground">No notes yet</h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Notes from coaching sessions will appear here.
            </p>
        </div>

        <!-- Notes list -->
        <div v-else class="space-y-4">
            <div v-for="note in notes" :key="note.id"
                class="rounded-lg border bg-card p-4 sm:p-6 hover:shadow-sm transition-shadow">
                <!-- Note header -->
                <div class="space-y-3 sm:space-y-0 sm:flex sm:items-start sm:justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-medium text-foreground break-words">
                            {{ note.title }}
                        </h3>

                        <!-- Session badge -->
                        <div v-if="note.session" class="mt-2 mb-3">
                            <Link :href="sessions.show(note.session.id).url">
                            <Badge variant="secondary" class="cursor-pointer hover:bg-secondary/80 transition-colors">
                                <Calendar class="h-3 w-3 mr-1" />
                                Session {{ note.session.session_number }} - {{ formatDate(note.session.scheduled_at) }}
                            </Badge>
                            </Link>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-1 text-sm text-muted-foreground">
                            <span>{{ formatDate(note.created_at) }}</span>
                            <span v-if="note.coach" class="flex items-center gap-1">
                                <User class="h-3 w-3 flex-shrink-0" />
                                <span class="break-words">{{ note.coach.name }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:ml-4">
                        <Button variant="ghost" size="sm" @click="toggleExpanded(note.id)" class="min-w-0 px-2">
                            <ChevronDown class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': expandedNotes.has(note.id) }" />
                        </Button>
                        <Link v-if="canEdit" :href="editNoteAction(note.id).url"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 flex-shrink-0">
                        <Edit class="mr-1 h-3 w-3" />
                        <span class="hidden xs:inline">Edit</span>
                        </Link>
                        <Button v-if="canDelete" variant="destructive" size="sm" @click="deleteNote(note)"
                            class="flex-shrink-0">
                            <Trash2 class="mr-1 h-3 w-3" />
                            <span class="hidden xs:inline">Delete</span>
                        </Button>
                    </div>
                </div>

                <!-- Note content preview -->
                <div class="max-w-none">
                    <div v-if="!expandedNotes.has(note.id)" class="text-muted-foreground break-words">
                        {{ truncateContent(note.content) }}
                        <button v-if="note.content.length > contentPreviewLength" @click="toggleExpanded(note.id)"
                            class="text-primary hover:underline ml-1 text-sm font-medium">
                            Show more
                        </button>
                    </div>
                    <div v-else class="text-foreground whitespace-pre-wrap break-words">
                        {{ note.content }}
                        <button @click="toggleExpanded(note.id)"
                            class="text-primary hover:underline ml-2 text-sm font-medium">
                            Show less
                        </button>
                    </div>
                </div>

                <!-- Attachments -->
                <div v-if="note.attachments && note.attachments.length > 0" class="mt-4 pt-4 border-t">
                    <div class="flex items-center gap-2 mb-3">
                        <Paperclip class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                        <span class="text-sm font-medium text-muted-foreground">
                            {{ note.attachments.length }} attachment{{ note.attachments.length === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <a v-for="attachment in note.attachments" :key="attachment.id" :href="attachment.url"
                            class="flex items-center gap-2 px-3 py-2 bg-muted hover:bg-muted/80 rounded-md text-sm transition-colors min-w-0"
                            target="_blank">
                            <component
                                :is="iconComponents[getAttachmentIcon(attachment).icon as keyof typeof iconComponents]"
                                :class="`h-4 w-4 flex-shrink-0 ${getAttachmentIcon(attachment).color}`" />
                            <span class="truncate flex-1 min-w-0">{{ attachment.original_name }}</span>
                            <span class="text-xs text-muted-foreground flex-shrink-0">({{ attachment.formatted_size
                            }})</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type CoachingNote } from '@/types';
import { edit as editNoteAction, destroy as destroyNoteAction } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingNoteController';
import { FileText, Calendar, User, ChevronDown, Edit, Trash2, Paperclip, FileImage, FileSpreadsheet, File, Presentation, Archive, Video, Music, Code } from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';
import { getFileIcon } from '@/lib/utils';
import sessions from '@/routes/tenant/coach/coaching-sessions';

interface Props {
    notes: CoachingNote[];
    canEdit?: boolean;
    canDelete?: boolean;
}

withDefaults(defineProps<Props>(), {
    canEdit: true,
    canDelete: true
});

// State for expanded notes
const expandedNotes = ref(new Set<number>());
const contentPreviewLength = 300;

// Icon component mapping
const iconComponents = {
    FileImage,
    FileText,
    FileSpreadsheet,
    File,
    Presentation,
    Archive,
    Video,
    Music,
    Code
};

// Get file icon info for an attachment
const getAttachmentIcon = (attachment: any) => {
    return getFileIcon(attachment.original_name, attachment.mime_type);
};

// Toggle expanded state for a note
const toggleExpanded = (noteId: number) => {
    if (expandedNotes.value.has(noteId)) {
        expandedNotes.value.delete(noteId);
    } else {
        expandedNotes.value.add(noteId);
    }
};

// Truncate content for preview
const truncateContent = (content: string) => {
    if (content.length <= contentPreviewLength) {
        return content;
    }
    return content.substring(0, contentPreviewLength) + '...';
};

// Format date for display
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};



// Handle delete note
const deleteNote = async (note: CoachingNote) => {
    const confirmed = await alertConfirm({
        title: 'Delete Note',
        description: `Are you sure you want to delete "${note.title}"? This action cannot be undone.`,
        confirmText: 'Delete Note',
        variant: 'destructive'
    });

    if (confirmed) {
        router.visit(destroyNoteAction(note.id), {
            preserveScroll: true,
        });
    }
};

</script>
