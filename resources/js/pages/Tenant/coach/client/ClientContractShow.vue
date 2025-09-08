<template>

    <Head :title="`Contract - ${client.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Contract Preview" :description="`Coaching agreement with ${client.name}`">
                    <template #actions>
                        <Link v-if="contract.status === 0 && can.update"
                            :href="contracts.edit([client.id, contract.id]).url">
                        <Button variant="outline">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Contract
                        </Button>
                        </Link>
                        <Button v-if="contract.status === 0 && can.update" @click="sendContract">
                            <Send class="mr-2 h-4 w-4" />
                            Send to Client
                        </Button>
                        <!-- Show client signing link when contract is sent but client hasn't signed -->
                        <Button v-if="contract.status >= 1 && !contract.client_signed && contract.signing_token"
                            variant="outline" @click="showCopyLinkDialog">
                            <Share class="mr-2 h-4 w-4" />
                            Copy Client Signing Link
                        </Button>
                        <!-- Show coach signing options when client has signed but coach hasn't -->
                        <template
                            v-if="contract.client_signed && !contract.coach_signed && contract.coach_signing_token">
                            <Button variant="outline" @click="showCopyCoachLinkDialog">
                                <Share class="mr-2 h-4 w-4" />
                                Copy Coach Signing Link
                            </Button>
                            <Button @click="signAsCoach">
                                <Edit class="mr-2 h-4 w-4" />
                                Sign as Coach
                            </Button>
                        </template>
                        <Button v-if="contract.is_fully_signed" variant="outline" @click="downloadPdf">
                            <Download class="mr-2 h-4 w-4" />
                            Download PDF
                        </Button>
                    </template>
                </PageHeader>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Contract Info Sidebar -->
                    <div class="lg:col-span-1 space-y-4">
                        <!-- Status Card -->
                        <div class="rounded-lg border bg-card p-4">
                            <h3 class="font-medium mb-3">Contract Status</h3>
                            <Badge :variant="getStatusVariant(contract.status)" class="mb-3">
                                {{ getStatusLabel(contract.status) }}
                            </Badge>

                            <!-- Signature Status -->
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <div
                                        :class="['w-2 h-2 rounded-full', contract.coach_signed ? 'bg-green-500' : 'bg-gray-300']">
                                    </div>
                                    <span class="text-muted-foreground">Coach Signed</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div
                                        :class="['w-2 h-2 rounded-full', contract.client_signed ? 'bg-green-500' : 'bg-gray-300']">
                                    </div>
                                    <span class="text-muted-foreground">Client Signed</span>
                                </div>
                            </div>
                        </div>

                        <!-- Contract Details Card -->
                        <div class="rounded-lg border bg-card p-4">
                            <h3 class="font-medium mb-3">Contract Details</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground">Template</Label>
                                    <p class="mt-1">{{ templateInfo?.title || 'Unknown Template' }}</p>
                                </div>
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground">Created</Label>
                                    <p class="mt-1">{{ formatDate(contract.created_at) }}</p>
                                </div>
                                <div v-if="contract.updated_at !== contract.created_at">
                                    <Label class="text-xs font-medium text-muted-foreground">Last Modified</Label>
                                    <p class="mt-1">{{ formatDate(contract.updated_at) }}</p>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Contract Preview -->
                    <div class="lg:col-span-3">
                        <div class="rounded-lg border bg-card p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-medium">Contract Preview</h3>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <FileText class="w-4 h-4" />
                                    Live Preview
                                </div>
                            </div>

                            <!-- Contract iframe -->
                            <div class="border rounded-lg overflow-hidden bg-white" style="height: 800px;">
                                <iframe :src="contracts.preview([client.id, contract.id]).url"
                                    class="w-full h-full border-0" sandbox="allow-same-origin"
                                    title="Contract Preview"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>

        <!-- Copy Link Dialog -->
        <Dialog v-model:open="showDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Share Client Signing Link</DialogTitle>
                    <DialogDescription>
                        Copy this link to share with {{ client.name }} for contract signing.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <Input :modelValue="signingUrl" readonly class="flex-1" />
                        <Button @click="copyClientLink" size="sm" :disabled="!signingUrl">
                            <Check v-if="copied" class="h-4 w-4" />
                            <Copy v-else class="h-4 w-4" />
                        </Button>
                    </div>
                    <p v-if="copied" class="text-sm text-green-600 flex items-center gap-2">
                        <Check class="h-4 w-4" />
                        Link copied to clipboard!
                    </p>
                    <p class="text-sm text-muted-foreground">
                        This link is secure and unique to {{ client.name }}. They can use it to review and sign the
                        contract.
                    </p>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Copy Coach Link Dialog -->
        <Dialog v-model:open="showCoachDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Coach Signing Link</DialogTitle>
                    <DialogDescription>
                        Copy this link for your countersignature, or use the "Sign as Coach" button to open it directly.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <Input :modelValue="coachSigningUrl" readonly class="flex-1" />
                        <Button @click="copyCoachLink" size="sm" :disabled="!coachSigningUrl">
                            <Check v-if="copied" class="h-4 w-4" />
                            <Copy v-else class="h-4 w-4" />
                        </Button>
                    </div>
                    <p v-if="copied" class="text-sm text-green-600 flex items-center gap-2">
                        <Check class="h-4 w-4" />
                        Link copied to clipboard!
                    </p>
                    <p class="text-sm text-muted-foreground">
                        This link is secure and unique for your countersignature. {{ client.name }} has already signed
                        the
                        contract.
                    </p>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Edit,
    Send,
    Download,
    FileText,
    Share,
    Copy,
    Check
} from 'lucide-vue-next';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import contracts from '@/routes/tenant/coach/clients/contracts';
import PageHeader from '@/components/PageHeader.vue';
import { alertConfirm } from '@/plugins/alert';
import axios from 'axios';
import { pdf as pdfAction, send as sendAction } from "@/actions/App/Http/Controllers/Tenant/Coach/ContractController";

interface Contract {
    id: number;
    template_path: string;
    variables: Record<string, any> | null;
    status: number;
    created_at: string;
    updated_at: string;
    coach_signed: boolean;
    client_signed: boolean;
    is_fully_signed: boolean;
    signing_token: string | null; // Client signing token
    coach_signing_token: string | null; // Coach signing token
}

const props = defineProps<{
    client: Client;
    contract: Contract;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: clients.index().url,
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url,
    },
    {
        title: 'Contracts',
        href: contracts.index(props.client.id).url,
    },
    {
        title: 'Contract Preview',
        href: contracts.show([props.client.id, props.contract.id]).url,
    }
];

const templateInfo = ref<any>(null);
const showDialog = ref(false);
const showCoachDialog = ref(false);
const copied = ref(false);

const signingUrl = computed(() => {
    if (!props.contract.signing_token) return '';
    return `${window.location.origin}/client/sign-contract/${props.contract.signing_token}`;
});

const coachSigningUrl = computed(() => {
    if (!props.contract.coach_signing_token) return '';
    return `${window.location.origin}/coach/sign-contract/${props.contract.coach_signing_token}`;
});

onMounted(async () => {
    try {
        // Get template info
        const templatesResponse = await axios.get('/api/templates');
        const templates = templatesResponse.data;
        templateInfo.value = templates.find((t: any) => t.path === props.contract.template_path);
    } catch (error) {
        console.error('Failed to load template data:', error);
    }
});

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusLabel = (status: number) => {
    const labels: Record<number, string> = {
        0: 'Draft',
        1: 'Sent',
        2: 'Viewed',
        3: 'Signed by Client',
        4: 'Countersigned',
        5: 'Active',
        6: 'Lapsed',
        7: 'Terminated',
        8: 'Void',
    };
    return labels[status] || 'Unknown';
};

const getStatusVariant = (status: number): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (status === 0) return 'secondary'; // Draft
    if (status >= 1 && status <= 2) return 'outline'; // Sent/Viewed
    if (status >= 3 && status <= 5) return 'default'; // Signed/Active
    return 'destructive'; // Ended states
};

const sendContract = async () => {
    const confirmed = await alertConfirm({
        title: 'Send Contract',
        description: 'Are you sure you want to send this contract to the client? Once sent, the contract cannot be edited.',
        confirmText: 'Send Contract',
        variant: 'default'
    });

    if (confirmed) {
        router.patch(sendAction([props.client.id, props.contract.id]).url, {}, {
            preserveScroll: true,
        });
    }
};

const downloadPdf = () => {
    window.open(pdfAction([props.client.id, props.contract.id]).url, '_blank');
};

const showCopyLinkDialog = () => {
    showDialog.value = true;
    copied.value = false;
};

const showCopyCoachLinkDialog = () => {
    showCoachDialog.value = true;
    copied.value = false;
};

const signAsCoach = () => {
    if (props.contract.coach_signing_token) {
        window.open(coachSigningUrl.value, '_blank');
    }
};

const copyToClipboard = async (url: string) => {
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};

const copyClientLink = () => copyToClipboard(signingUrl.value);
const copyCoachLink = () => copyToClipboard(coachSigningUrl.value);
</script>
