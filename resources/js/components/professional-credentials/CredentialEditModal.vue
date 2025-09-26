<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Edit Certificate - {{ provider?.name }}</DialogTitle>
                <DialogDescription>
                    Update your certificate details or replace the file.
                </DialogDescription>
            </DialogHeader>

            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-2">
                    <TabsTrigger value="edit">Edit Details</TabsTrigger>
                    <TabsTrigger value="replace">Replace File</TabsTrigger>
                </TabsList>

                <!-- Edit Details Tab -->
                <TabsContent value="edit" class="space-y-4">
                    <form @submit.prevent="handleUpdate" class="space-y-4">
                        <!-- Current File Info -->
                        <div class="rounded-lg border bg-muted/50 p-3 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span>{{ credential?.original_filename }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ credential?.formatted_file_size }} • Uploaded {{
                                formatRelativeDate(credential?.created_at) }}
                            </div>
                        </div>

                        <!-- Credential Name -->
                        <div class="space-y-2">
                            <Label for="edit_credential_name">
                                Credential Level/Type
                            </Label>
                            <Input id="edit_credential_name" v-model="editForm.credential_name" type="text"
                                placeholder="e.g., Master Coach, ACC, PCC" :disabled="editForm.processing" />
                            <InputError :message="editForm.errors.credential_name" />
                        </div>

                        <!-- Expiry Date -->
                        <div class="space-y-2">
                            <Label for="edit_expiry_date">
                                Expiry Date
                            </Label>
                            <Input id="edit_expiry_date" v-model="editForm.expiry_date" type="date"
                                :disabled="editForm.processing" />
                            <p class="text-xs text-muted-foreground">
                                Leave empty if certificate doesn't expire
                            </p>
                            <InputError :message="editForm.errors.expiry_date" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="closeModal" :disabled="editForm.processing">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="editForm.processing">
                                <LoaderCircle v-if="editForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Save Changes
                            </Button>
                        </DialogFooter>
                    </form>
                </TabsContent>

                <!-- Replace File Tab -->
                <TabsContent value="replace" class="space-y-4">
                    <form @submit.prevent="handleReplace" class="space-y-4">
                        <!-- Current File Info -->
                        <div class="rounded-lg border bg-orange-50 dark:bg-orange-950/20 p-3 space-y-2">
                            <p class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                Current Certificate
                            </p>
                            <div class="flex items-center gap-2 text-sm">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span>{{ credential?.original_filename }}</span>
                            </div>
                        </div>

                        <!-- New File Upload -->
                        <div class="space-y-2">
                            <Label for="replace_certificate">New Certificate File *</Label>
                            <input id="replace_certificate" type="file" accept=".pdf,application/pdf"
                                @change="handleReplaceFileSelect" :disabled="replaceForm.processing" required class="block w-full text-sm text-muted-foreground
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-primary file:text-primary-foreground
                                    hover:file:bg-primary/90
                                    disabled:opacity-50 disabled:cursor-not-allowed" />
                            <p class="text-xs text-muted-foreground">
                                This will replace your current certificate file
                            </p>
                            <InputError :message="replaceForm.errors.certificate" />
                        </div>

                        <!-- Selected File Preview -->
                        <div v-if="selectedReplaceFile"
                            class="rounded-lg border bg-muted/50 p-3 flex items-center gap-3">
                            <FileText class="h-8 w-8 text-muted-foreground" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ selectedReplaceFile.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatFileSize(selectedReplaceFile.size) }}
                                </p>
                            </div>
                        </div>

                        <!-- Keep existing details option -->
                        <div class="space-y-2">
                            <Label>Update Details (Optional)</Label>
                            <div class="space-y-2">
                                <Input v-model="replaceForm.credential_name" type="text"
                                    placeholder="Credential level (leave empty to keep current)"
                                    :disabled="replaceForm.processing" />
                                <Input v-model="replaceForm.expiry_date" type="date"
                                    placeholder="Expiry date (leave empty to keep current)"
                                    :disabled="replaceForm.processing" />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="closeModal"
                                :disabled="replaceForm.processing">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="!selectedReplaceFile || replaceForm.processing">
                                <LoaderCircle v-if="replaceForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Replace Certificate
                            </Button>
                        </DialogFooter>
                    </form>
                </TabsContent>
            </Tabs>

            <!-- Delete Option -->
            <div class="mt-4 pt-4 border-t">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-destructive">Remove Certificate</p>
                        <p class="text-xs text-muted-foreground">This action cannot be undone</p>
                    </div>
                    <Button variant="destructive" size="sm" @click="handleDelete" :disabled="deleteForm.processing">
                        <Trash2 v-if="!deleteForm.processing" class="mr-2 h-4 w-4" />
                        <LoaderCircle v-else class="mr-2 h-4 w-4 animate-spin" />
                        Delete
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { FileText, LoaderCircle, Trash2 } from 'lucide-vue-next';
import { type ProfessionalCredentialProvider, type UserProfessionalCredential } from '@/types/professional-credentials';
import { update, replace, destroy } from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalCredentialController';
import { formatFileSize, formatRelativeDate } from '@/lib/utils';
import { alertConfirm } from '@/plugins/alert';

interface Props {
    modelValue: boolean;
    provider: ProfessionalCredentialProvider | null;
    credential: UserProfessionalCredential | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    'success': [];
}>();

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const activeTab = ref('edit');
const selectedReplaceFile = ref<File | null>(null);

// Form for editing details
const editForm = useForm({
    credential_name: '',
    expiry_date: ''
});

// Form for replacing file
const replaceForm = useForm({
    certificate: null as File | null,
    credential_name: '',
    expiry_date: ''
});

// Form for delete
const deleteForm = useForm({});

// Initialize form data when credential changes
watch(() => props.credential, (newCredential) => {
    if (newCredential) {
        editForm.credential_name = newCredential.credential_name || '';
        editForm.expiry_date = newCredential.expiry_date || '';
    }
});

// Reset forms when modal closes
watch(isOpen, (newValue) => {
    if (!newValue) {
        resetForms();
        activeTab.value = 'edit';
    }
});

const handleUpdate = () => {
    if (!props.credential) return;

    editForm.patch(update(props.credential.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeModal();
        }
    });
};

const handleReplaceFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        // Validate file type
        if (file.type !== 'application/pdf') {
            replaceForm.setError('certificate', 'Please select a PDF file.');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            replaceForm.setError('certificate', 'File size must be less than 10MB.');
            return;
        }

        selectedReplaceFile.value = file;
        replaceForm.certificate = file;
        replaceForm.clearErrors('certificate');
    }
};

const handleReplace = () => {
    if (!props.credential || !selectedReplaceFile.value) return;

    replaceForm.post(replace(props.credential.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeModal();
        }
    });
};

const handleDelete = async () => {
    if (!props.credential) return;

    const confirmed = await alertConfirm({
        title: 'Delete Certificate?',
        text: `Are you sure you want to remove your ${props.provider?.name} certificate? This action cannot be undone.`,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });

    if (confirmed) {
        deleteForm.delete(destroy(props.credential.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                closeModal();
            }
        });
    }
};

const closeModal = () => {
    isOpen.value = false;
};

const resetForms = () => {
    editForm.reset();
    editForm.clearErrors();
    replaceForm.reset();
    replaceForm.clearErrors();
    selectedReplaceFile.value = null;
};
</script>
