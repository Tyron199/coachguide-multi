<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Upload Certificate - {{ provider?.name }}</DialogTitle>
                <DialogDescription>
                    Upload your {{ provider?.full_name }} certificate. Only PDF files are accepted (max 10MB).
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- File Upload -->
                <div class="space-y-2">
                    <Label for="certificate">Certificate File *</Label>
                    <div class="relative">
                        <input id="certificate" type="file" accept=".pdf,application/pdf" @change="handleFileSelect"
                            :disabled="form.processing" required class="block w-full text-sm text-muted-foreground
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary file:text-primary-foreground
                                hover:file:bg-primary/90
                                disabled:opacity-50 disabled:cursor-not-allowed" />
                        <p class="text-xs text-muted-foreground mt-1">
                            PDF format only, maximum 10MB
                        </p>
                    </div>
                    <InputError :message="form.errors.certificate" />
                </div>

                <!-- Selected File Preview -->
                <div v-if="selectedFile" class="rounded-lg border bg-muted/50 p-3 flex items-center gap-3">
                    <FileText class="h-8 w-8 text-muted-foreground" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ selectedFile.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatFileSize(selectedFile.size) }}</p>
                    </div>
                    <Button type="button" variant="ghost" size="sm" @click="clearFile" :disabled="form.processing">
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Credential Name (Optional) -->
                <div class="space-y-2">
                    <Label for="credential_name">
                        Credential Level/Type
                        <span class="text-xs text-muted-foreground">(optional)</span>
                    </Label>
                    <Input id="credential_name" v-model="form.credential_name" type="text"
                        placeholder="e.g., Master Coach, ACC, PCC" :disabled="form.processing" />
                    <p class="text-xs text-muted-foreground">
                        Specify your certification level if applicable
                    </p>
                    <InputError :message="form.errors.credential_name" />
                </div>

                <!-- Expiry Date (Optional) -->
                <div class="space-y-2">
                    <Label for="expiry_date">
                        Expiry Date
                        <span class="text-xs text-muted-foreground">(optional)</span>
                    </Label>
                    <Input id="expiry_date" v-model="form.expiry_date" type="date" :min="tomorrow"
                        :disabled="form.processing" />
                    <p class="text-xs text-muted-foreground">
                        Set if your certificate has an expiration date
                    </p>
                    <InputError :message="form.errors.expiry_date" />
                </div>

                <!-- General Error Message -->
                <InputError :message="form.errors.professional_credential_provider_id" />

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal" :disabled="form.processing">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!selectedFile || form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Upload Certificate
                    </Button>
                </DialogFooter>
            </form>
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
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { FileText, X, LoaderCircle } from 'lucide-vue-next';
import { type ProfessionalCredentialProvider } from '@/types/professional-credentials';
import { store } from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalCredentialController';
import { formatFileSize } from '@/lib/utils';

interface Props {
    modelValue: boolean;
    provider: ProfessionalCredentialProvider | null;
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

const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement>();

const form = useForm({
    professional_credential_provider_id: null as number | null,
    credential_name: '',
    expiry_date: '',
    certificate: null as File | null
});

// Tomorrow's date for min attribute
const tomorrow = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
});

// Update provider ID when modal opens
watch(() => props.provider, (newProvider) => {
    if (newProvider) {
        form.professional_credential_provider_id = newProvider.id;
    }
});

// Reset form when modal closes
watch(isOpen, (newValue) => {
    if (!newValue) {
        resetForm();
    }
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        // Validate file type
        if (file.type !== 'application/pdf') {
            form.setError('certificate', 'Please select a PDF file.');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            form.setError('certificate', 'File size must be less than 10MB.');
            return;
        }

        selectedFile.value = file;
        form.certificate = file;
        form.clearErrors('certificate');
    }
};

const clearFile = () => {
    selectedFile.value = null;
    form.certificate = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleSubmit = () => {
    if (!selectedFile.value || !form.professional_credential_provider_id) {
        return;
    }

    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeModal();
        },
        onError: (errors) => {
            console.error('Upload errors:', errors);
        }
    });
};

const closeModal = () => {
    isOpen.value = false;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>
