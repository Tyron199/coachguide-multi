<template>
    <div v-if="attachments.length > 0" class="space-y-3">
        <h4 class="text-sm font-medium text-muted-foreground">
            Attachments ({{ attachments.length }})
        </h4>
        <div class="grid gap-2">
            <div v-for="attachment in attachments" :key="attachment.id || attachment.file_name"
                class="flex items-center justify-between p-3 bg-muted/50 rounded-lg border hover:bg-muted/70 transition-colors">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <!-- File Icon -->
                    <div class="flex-shrink-0">
                        <component
                            :is="iconComponents[getAttachmentIcon(attachment).icon as keyof typeof iconComponents]"
                            :class="`h-5 w-5 ${getAttachmentIcon(attachment).color}`" />
                    </div>

                    <!-- File Info -->
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ attachment.original_name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ attachment.formatted_size || formatFileSize(attachment.file_size) }}
                            <span v-if="attachment.mime_type" class="ml-1">
                                • {{ getFileTypeLabel(attachment.mime_type) }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1 flex-shrink-0">
                    <!-- Preview/Download Button -->
                    <Button variant="ghost" size="sm" @click.prevent="handlePreview(attachment)"
                        :title="attachment.is_image ? 'Preview image' : 'Download file'">
                        <Eye v-if="attachment.is_image" class="h-4 w-4" />
                        <Download v-else class="h-4 w-4" />
                    </Button>

                    <!-- Delete Button -->
                    <Button v-if="canDelete" variant="ghost" size="sm" @click.prevent="handleDelete(attachment)"
                        title="Delete attachment" class="text-destructive hover:text-destructive">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Dialog -->
    <Dialog v-model:open="previewOpen">
        <DialogContent class="">
            <DialogHeader>
                <DialogTitle>{{ previewAttachment?.original_name }}</DialogTitle>
            </DialogHeader>
            <div class="flex justify-center">
                <img v-if="previewAttachment?.is_image" :src="previewAttachment.url"
                    :alt="previewAttachment.original_name" class=" object-contain rounded-lg" />
            </div>
            <DialogFooter>
                <Button variant="outline" @click="previewOpen = false">
                    Close
                </Button>
                <Button @click="downloadFile(previewAttachment!)">
                    <Download class="mr-2 h-4 w-4" />
                    Download
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    FileImage,
    FileText,
    FileSpreadsheet,
    Eye,
    Download,
    Trash2,
    File,
    Presentation,
    Archive,
    Video,
    Music,
    Code
} from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';
import { getFileIcon } from '@/lib/utils';
import { destroy as destroyAttachmentAction, download as downloadAttachmentAction } from '@/actions/App/Http/Controllers/Tenant/AttachmentController';

interface Attachment {
    id?: number;
    original_name: string;
    file_name: string;
    file_path: string;
    mime_type: string;
    file_size: number;
    url: string;
    formatted_size?: string;
    is_image: boolean;
}

interface Props {
    attachments: Attachment[];
    canDelete?: boolean;
}

withDefaults(defineProps<Props>(), {
    canDelete: true
});

const emit = defineEmits<{
    attachmentDeleted: [attachment: Attachment];
}>();

// Preview state
const previewOpen = ref(false);
const previewAttachment = ref<Attachment | null>(null);

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
const getAttachmentIcon = (attachment: Attachment) => {
    return getFileIcon(attachment.original_name, attachment.mime_type);
};

// File type helpers (keeping for backward compatibility if needed elsewhere)

const getFileTypeLabel = (mimeType: string) => {
    if (mimeType.startsWith('image/')) return 'Image';
    if (mimeType === 'application/pdf') return 'PDF';
    if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return 'Spreadsheet';
    if (mimeType.includes('document') || mimeType.includes('word')) return 'Document';
    if (mimeType.includes('text')) return 'Text';
    return 'File';
};

const formatFileSize = (bytes: number): string => {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;

    while (size > 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }

    return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
};

// Actions
const handlePreview = (attachment: Attachment) => {
    if (attachment.is_image) {
        previewAttachment.value = attachment;
        previewOpen.value = true;
    } else {
        downloadFile(attachment);
    }
};

const downloadFile = (attachment: Attachment) => {
    if (attachment.id) {
        // For saved attachments, use the download route
        window.open(downloadAttachmentAction(attachment.id).url, '_blank');
    } else {
        // For temporary attachments, use the direct URL
        const link = document.createElement('a');
        link.href = attachment.url;
        link.download = attachment.original_name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const handleDelete = async (attachment: Attachment) => {
    const confirmed = await alertConfirm({
        title: 'Delete Attachment',
        description: `Are you sure you want to delete "${attachment.original_name}"? This action cannot be undone.`,
        confirmText: 'Delete Attachment',
        variant: 'destructive'
    });

    if (confirmed) {
        if (attachment.id) {
            // Delete saved attachment via API
            router.visit(destroyAttachmentAction(attachment.id), {
                preserveScroll: true,
                onSuccess: () => {
                    emit('attachmentDeleted', attachment);
                },
            });
        } else {
            // For temporary attachments, just emit the event
            emit('attachmentDeleted', attachment);
        }
    }
};
</script>
