<template>
    <Card class="relative overflow-hidden transition-all hover:shadow-md">
        <div class="flex flex-col lg:flex-row">
            <!-- Left Section: Provider Info -->
            <div class="flex-1 p-4">
                <div class="flex items-start gap-3">
                    <!-- Provider Logo -->
                    <div v-if="provider.logo_url && !logoError" class="flex-shrink-0">
                        <img :src="provider.logo_url" :alt="`${provider.name} logo`"
                            class="h-16 w-16 object-contain rounded" @error="handleLogoError" />
                    </div>
                    <div v-else class="flex-shrink-0 h-12 w-12 rounded bg-muted flex items-center justify-center">
                        <span class="text-xs font-semibold text-muted-foreground">{{ provider.name.substring(0, 2)
                        }}</span>
                    </div>

                    <!-- Provider Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <CardTitle class="text-lg font-semibold">{{ provider.name }}</CardTitle>
                            <!-- Status Badge -->
                            <div v-if="provider.has_credential">
                                <div v-if="provider.credential?.is_expired"
                                    class="inline-flex items-center gap-1 bg-destructive/10 text-destructive px-2 py-0.5 text-xs font-medium rounded-full">
                                    <XCircle class="h-3 w-3" />
                                    Expired
                                </div>
                                <div v-else-if="provider.credential?.is_expiring_soon"
                                    class="inline-flex items-center gap-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-2 py-0.5 text-xs font-medium rounded-full">
                                    <AlertCircle class="h-3 w-3" />
                                    Expiring Soon
                                </div>
                                <div v-else
                                    class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 text-xs font-medium rounded-full">
                                    <CheckCircle2 class="h-3 w-3" />
                                    Active
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <CardDescription class="text-sm">{{ provider.full_name }}</CardDescription>
                            <!-- Provider Website Link -->
                            <a :href="provider.website_url" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                <ExternalLink class="h-3 w-3" />
                                Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section: Certificate Info & Actions -->
            <div class="border-t lg:border-t-0 lg:border-l p-4 lg:w-[450px]">
                <!-- Credential Status Section -->
                <div v-if="provider.has_credential" class="flex items-center gap-3">
                    <div class="flex-1 space-y-1">
                        <div v-if="provider.credential?.credential_name" class="flex items-center gap-2 text-sm">
                            <Award class="h-4 w-4 text-muted-foreground" />
                            <span class="font-medium">{{ provider.credential.credential_name }}</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <FileText class="h-4 w-4" />
                            <span class="truncate">{{ provider.credential?.original_filename }}</span>
                        </div>

                        <div v-if="provider.credential?.expiry_date" class="flex items-center gap-2 text-sm">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <span :class="{
                                'text-destructive': provider.credential.is_expired,
                                'text-orange-600 dark:text-orange-400': provider.credential.is_expiring_soon && !provider.credential.is_expired,
                                'text-muted-foreground': !provider.credential.is_expired && !provider.credential.is_expiring_soon
                            }">
                                <template v-if="provider.credential.is_expired">
                                    Expired on {{ formatDate(provider.credential.expiry_date) }}
                                </template>
                                <template v-else-if="provider.credential.days_until_expiry !== null">
                                    Expires in {{ provider.credential.days_until_expiry }} days
                                </template>
                                <template v-else>
                                    Expires {{ formatDate(provider.credential.expiry_date) }}
                                </template>
                            </span>
                        </div>

                        <div class="text-xs text-muted-foreground">
                             Uploaded {{ provider.credential?.created_at
                                ?
                                formatRelativeDate(provider.credential.created_at) : '' }}
                        </div>
                    </div>

                    <!-- Action Buttons for Existing Credential -->
                    <div class="flex flex-col gap-1.5">
                        <Button variant="outline" size="sm" @click="handleDownload" :disabled="isDownloading"
                            class="h-8">
                            <Download v-if="!isDownloading" class="mr-1.5 h-3.5 w-3.5" />
                            <LoaderCircle v-else class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                            <span class="text-xs">Download</span>
                        </Button>

                        <Button variant="outline" size="sm" @click="openEditModal" class="h-8">
                            <Edit class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-xs">Edit</span>
                        </Button>

                        <Button variant="destructive" size="sm" @click="openReplaceModal" class="h-8">
                            <RefreshCw class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-xs">Replace</span>
                        </Button>
                    </div>
                </div>

                <!-- No Credential State -->
                <div v-else class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-sm text-muted-foreground">No certificate uploaded</p>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Button @click="openUploadModal" size="sm" class="h-8">
                            <Upload class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-xs">Upload Certificate</span>
                        </Button>
                        <Button variant="outline" size="sm" class="h-8" @click="openProviderWebsite">
                            <ExternalLink class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-xs">Get Certified</span>
                        </Button>
                    </div>
                </div>

            </div>
        </div>
    </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    CheckCircle2,
    Download,
    Edit,
    RefreshCw,
    Upload,
    ExternalLink,
    FileText,
    Calendar,
    Award,
    LoaderCircle,
    XCircle,
    AlertCircle
} from 'lucide-vue-next';
import { type ProfessionalCredentialProvider } from '@/types/professional-credentials';
import { download } from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalCredentialController';
import { formatDate, formatRelativeDate } from '@/lib/utils';

interface Props {
    provider: ProfessionalCredentialProvider;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    upload: [providerId: number];
    edit: [credential: any];
    replace: [credential: any];
}>();

const isDownloading = ref(false);
const logoError = ref(false);

const handleLogoError = () => {
    logoError.value = true;
    // Optionally, you could log the error or notify the user
    console.warn(`Failed to load logo for ${props.provider.name}`);
};

const openUploadModal = () => {
    emit('upload', props.provider.id);
};

const openEditModal = () => {
    if (props.provider.credential) {
        emit('edit', props.provider.credential);
    }
};

const openReplaceModal = () => {
    if (props.provider.credential) {
        emit('replace', props.provider.credential);
    }
};

const handleDownload = async () => {
    if (!props.provider.credential) return;

    isDownloading.value = true;
    try {
        // Open download URL in new window
        window.open(download(props.provider.credential.id).url, '_blank');
    } finally {
        setTimeout(() => {
            isDownloading.value = false;
        }, 1000);
    }
};

const openProviderWebsite = () => {
    window.open(props.provider.website_url, '_blank', 'noopener,noreferrer');
};
</script>
