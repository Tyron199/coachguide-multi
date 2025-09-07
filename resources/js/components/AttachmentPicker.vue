<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child @click="openDialog">
            <slot>
                <Button variant="outline">
                    <Paperclip class="mr-2 h-4 w-4" />
                    Add Attachments
                </Button>
            </slot>
        </DialogTrigger>
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Add Attachments</DialogTitle>
                <DialogDescription>
                    Upload files to attach to this item. Maximum file size: 10MB per file.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Drop Zone -->
                <div ref="dropZone"
                    class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8 text-center transition-colors"
                    :class="{
                        'border-primary bg-primary/5': isDragOver,
                        'border-muted-foreground/25': !isDragOver
                    }" @dragover.prevent="handleDragOver" @dragleave.prevent="handleDragLeave"
                    @drop.prevent="handleDrop">
                    <Upload class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                    <div class="space-y-2">
                        <p class="text-lg font-medium">Drop files here or click to browse</p>
                        <p class="text-sm text-muted-foreground">
                            Supports images, documents, and other file types
                        </p>
                    </div>
                    <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileSelect"
                        accept="*/*" />
                    <Button type="button" variant="outline" class="mt-4" @click="$refs.fileInput.click()">
                        Browse Files
                    </Button>
                </div>

                <!-- Selected Files List -->
                <div v-if="selectedFiles.length > 0" class="space-y-2">
                    <h4 class="font-medium">Selected Files ({{ selectedFiles.length }})</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2">
                        <div v-for="(file, index) in selectedFiles" :key="index"
                            class="flex items-center justify-between p-3 bg-muted rounded-lg">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="flex-shrink-0">
                                    <component
                                        :is="iconComponents[getAttachmentIcon(file).icon as keyof typeof iconComponents]"
                                        :class="`h-5 w-5 ${getAttachmentIcon(file).color}`" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium truncate">{{ file.original_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ file.formatted_size }}</p>
                                </div>
                            </div>
                            <Button type="button" variant="ghost" size="sm" @click="removeFile(index)"
                                class="flex-shrink-0">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>



                <!-- Error Message -->
                <div v-if="errorMessage" class="p-3 bg-destructive/10 border border-destructive/20 rounded-lg">
                    <p class="text-sm text-destructive">{{ errorMessage }}</p>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeDialog">
                    Cancel
                </Button>
                <Button @click="confirmSelection" :disabled="selectedFiles.length === 0">
                    Add {{ selectedFiles.length }} File{{ selectedFiles.length === 1 ? '' : 's' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Upload,
    Paperclip,
    FileImage,
    FileText,
    X,

    File,
    FileSpreadsheet,
    Presentation,
    Archive,
    Video,
    Music,
    Code
} from 'lucide-vue-next';
import { getFileIcon } from '@/lib/utils';

interface AttachmentFile {
    original_name: string;
    file_name: string;
    file_path: string;
    mime_type: string;
    file_size: number;
    url: string;
    formatted_size: string;
    is_image: boolean;
}

interface Props {
    existingAttachments?: AttachmentFile[];
    currentAttachments?: AttachmentFile[];
}

const props = withDefaults(defineProps<Props>(), {
    existingAttachments: () => [],
    currentAttachments: () => []
});

const emit = defineEmits<{
    attachmentsSelected: [attachments: AttachmentFile[]];
}>();

// State
const isOpen = ref(false);
const isDragOver = ref(false);
const selectedFiles = ref<AttachmentFile[]>([]);
const errorMessage = ref('');

// Refs
const dropZone = ref<HTMLElement>();
const fileInput = ref<HTMLInputElement>();

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
const getAttachmentIcon = (attachment: AttachmentFile) => {
    return getFileIcon(attachment.original_name, attachment.mime_type);
};

// Drag and drop handlers
const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    // Only set to false if we're leaving the drop zone entirely
    if (!dropZone.value?.contains(e.relatedTarget as Node)) {
        isDragOver.value = false;
    }
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = false;

    const files = Array.from(e.dataTransfer?.files || []);
    if (files.length > 0) {
        processFiles(files);
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    if (files.length > 0) {
        processFiles(files);
    }
    // Reset input
    target.value = '';
};

const processFiles = (files: File[]) => {
    if (files.length === 0) return;

    // Validate file sizes
    const maxSize = 10 * 1024 * 1024; // 10MB
    const oversizedFiles = files.filter(file => file.size > maxSize);
    if (oversizedFiles.length > 0) {
        errorMessage.value = `Some files are too large. Maximum size is 10MB per file.`;
        return;
    }

    errorMessage.value = '';

    // Convert File objects to our attachment format
    const newAttachments = files.map(file => ({
        original_name: file.name,
        file_name: file.name, // Will be processed by parent
        file_path: '', // Will be set by parent after upload
        mime_type: file.type,
        file_size: file.size,
        url: URL.createObjectURL(file), // Temporary URL for preview
        formatted_size: formatFileSize(file.size),
        is_image: file.type.startsWith('image/'),
        file: file // Keep the original File object for the parent
    }));

    selectedFiles.value.push(...newAttachments);
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

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
};

const confirmSelection = () => {
    emit('attachmentsSelected', [...selectedFiles.value]);
    closeDialog();
};

const closeDialog = () => {
    isOpen.value = false;
    // Reset state when closing
    nextTick(() => {
        selectedFiles.value = [];
        errorMessage.value = '';
    });
};

// Initialize with current attachments when dialog opens
const openDialog = () => {
    isOpen.value = true;
    // Start with current attachments from parent
    selectedFiles.value = [...props.currentAttachments];
};
</script>
