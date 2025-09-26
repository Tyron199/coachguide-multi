<template>

    <Head title="Professional Credentials" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Professional Credentials"
                    description="Manage your professional coaching certifications" :badge="badgeText">
                    <template #actions>
                        <div class="flex items-center gap-2">
                            <!-- Stats Pills -->
                            <div v-if="stats.expiring_soon_count > 0"
                                class="px-3 py-1 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-medium">
                                {{ stats.expiring_soon_count }} expiring soon
                            </div>
                            <div v-if="stats.expired_count > 0"
                                class="px-3 py-1 rounded-full bg-destructive/10 text-destructive text-sm font-medium">
                                {{ stats.expired_count }} expired
                            </div>
                        </div>
                    </template>
                </PageHeader>

                <!-- Providers List -->
                <div v-if="providers.length > 0" class="space-y-3">
                    <CredentialProviderCard v-for="provider in providers" :key="provider.id" :provider="provider"
                        @upload="openUploadModal" @edit="openEditModal" @replace="openReplaceModal" />
                </div>

                <!-- Empty State -->
                <div v-else
                    class="relative flex justify-center items-center min-h-96 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-700">
                    <PlaceholderPattern />
                    <div class="relative z-10 text-center">
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                            No credential providers found. Please contact support if this persists.
                        </p>
                    </div>
                </div>

                <!-- Upload Modal -->
                <CredentialUploadModal v-model="showUploadModal" :provider="selectedProvider"
                    @success="handleUploadSuccess" />

                <!-- Edit/Replace Modal -->
                <CredentialEditModal v-model="showEditModal" :provider="selectedProviderForEdit"
                    :credential="selectedCredential" @success="handleEditSuccess" />
            </div>
        </GrowthTrackerLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import CredentialProviderCard from '@/components/professional-credentials/CredentialProviderCard.vue';
import CredentialUploadModal from '@/components/professional-credentials/CredentialUploadModal.vue';
import CredentialEditModal from '@/components/professional-credentials/CredentialEditModal.vue';
import { type BreadcrumbItem } from '@/types';
import {
    type ProfessionalCredentialsPageProps,
    type ProfessionalCredentialProvider,
    type UserProfessionalCredential
} from '@/types/professional-credentials';
import { index } from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalCredentialController';
import { alertSuccess } from '@/plugins/alert';

const props = defineProps<ProfessionalCredentialsPageProps>();

// Modal states
const showUploadModal = ref(false);
const showEditModal = ref(false);
const selectedProvider = ref<ProfessionalCredentialProvider | null>(null);
const selectedProviderForEdit = ref<ProfessionalCredentialProvider | null>(null);
const selectedCredential = ref<UserProfessionalCredential | null>(null);

// Computed
const badgeText = computed(() => {
    const uploaded = props.stats.uploaded_count;
    const total = props.stats.total_providers;
    return `${uploaded} of ${total} uploaded`;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Growth Tracker',
        href: index().url
    },
    {
        title: 'Professional Credentials',
        href: index().url
    },
];

// Methods
const openUploadModal = (providerId: number) => {
    selectedProvider.value = props.providers.find(p => p.id === providerId) || null;
    showUploadModal.value = true;
};

const openEditModal = (credential: UserProfessionalCredential) => {
    selectedCredential.value = credential;
    // Find the provider for this credential
    selectedProviderForEdit.value = props.providers.find(p =>
        p.credential && p.credential.id === credential.id
    ) || null;
    showEditModal.value = true;
};

const openReplaceModal = (credential: UserProfessionalCredential) => {
    // Use the same edit modal but it will default to replace tab
    openEditModal(credential);
};

const handleUploadSuccess = () => {
    alertSuccess('Certificate uploaded successfully');
    router.reload({ preserveScroll: true });
};

const handleEditSuccess = () => {
    alertSuccess('Certificate updated successfully');
    router.reload({ preserveScroll: true });
};
</script>