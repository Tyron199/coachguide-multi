<template>

    <Head :title="`${client.name} - Contracts`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Client Contracts" description="Coaching agreements between you and your client"
                    :badge="`${contracts.length} ${contracts.length === 1 ? 'contract' : 'contracts'}`">
                    <template #actions>
                        <Link v-if="can.create" :href="contractRoutes.create(client.id).url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Contract
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <!-- Contracts List -->
                <div v-if="contracts.length > 0" class="space-y-4">
                    <div v-for="contract in contracts" :key="contract.id"
                        class="rounded-lg border bg-card p-6 hover:shadow-md transition-shadow">

                        <!-- Contract Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <Link :href="contractRoutes.show([client.id, contract.id]).url">
                                <h3 class="text-lg font-medium">

                                    {{ getTemplateName(contract.template_path) }}
                                </h3>
                                </Link>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Created {{ formatDate(contract.created_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getStatusVariant(contract.status)">
                                    {{ getStatusLabel(contract.status) }}
                                </Badge>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link :href="contractRoutes.show([client.id, contract.id]).url"
                                                class="flex items-center">
                                            <Eye class="mr-2 h-4 w-4" />
                                            View Contract
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem v-if="contract.status === 0 && contract.can.update" as-child>
                                            <Link :href="contractRoutes.edit([client.id, contract.id]).url"
                                                class="flex items-center">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit Contract
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem v-if="contract.status === 0 && contract.can.update"
                                            @click="sendContract(contract.id)">
                                            <Send class="mr-2 h-4 w-4" />
                                            Send to Client
                                        </DropdownMenuItem>
                                        <!-- Show client signing link when contract is sent but client hasn't signed -->
                                        <DropdownMenuItem
                                            v-if="contract.status >= 1 && !contract.client_signed && contract.signing_token"
                                            @click="copySigningLink(contract)">
                                            <Share class="mr-2 h-4 w-4" />
                                            Copy Client Signing Link
                                        </DropdownMenuItem>
                                        <!-- Show coach signing options when client has signed but coach hasn't -->
                                        <DropdownMenuItem
                                            v-if="contract.client_signed && !contract.coach_signed && contract.coach_signing_token"
                                            @click="copyCoachSigningLink(contract)">
                                            <Share class="mr-2 h-4 w-4" />
                                            Copy Coach Signing Link
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="contract.client_signed && !contract.coach_signed && contract.coach_signing_token"
                                            @click="signAsCoach(contract)">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Sign as Coach
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator v-if="canDownloadPdf(contract)" />
                                        <DropdownMenuItem v-if="canDownloadPdf(contract)"
                                            @click="downloadPdf(contract.id)">
                                            <Download class="mr-2 h-4 w-4" />
                                            Download PDF
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator v-if="contract.status < 2 && contract.can.delete" />
                                        <DropdownMenuItem v-if="contract.status < 2 && contract.can.delete"
                                            @click="deleteContract(contract.id)" class="text-destructive">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete Contract
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <!-- Contract Details -->
                        <div class="grid gap-4 sm:grid-cols-3 mb-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Contract Period</Label>
                                <p class="mt-1 text-sm">
                                    {{ formatDate(contract.variables?.start_date) || 'Not set' }} -
                                    {{ formatDate(contract.variables?.end_date) || 'Not set' }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Session Details</Label>
                                <p class="mt-1 text-sm">
                                    {{ contract.variables?.session_frequency || 'Not set' }},
                                    {{ contract.variables?.session_duration || 'Not set' }} min sessions
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Total Fee</Label>
                                <p class="mt-1 text-sm font-medium">
                                    {{ contract.variables?.package_fee || 'Not set' }}
                                </p>
                            </div>
                        </div>

                        <!-- Signature Status -->
                        <div v-if="contract.status >= 1" class="flex items-center gap-4 pt-4 border-t">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1">
                                    <div
                                        :class="['w-2 h-2 rounded-full', contract.coach_signed ? 'bg-green-500' : 'bg-gray-300']">
                                    </div>
                                    <span class="text-sm text-muted-foreground">Coach Signed</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1">
                                    <div
                                        :class="['w-2 h-2 rounded-full', contract.client_signed ? 'bg-green-500' : 'bg-gray-300']">
                                    </div>
                                    <span class="text-sm text-muted-foreground">Client Signed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="mx-auto w-12 h-12 bg-muted rounded-lg flex items-center justify-center mb-4">
                        <FileText class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium mb-2">No contracts yet</h3>
                    <p class="text-muted-foreground mb-4">
                        Create your first coaching contract for {{ client.name }}.
                    </p>
                    <Link v-if="can.create" :href="contractRoutes.create(client.id).url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Create Contract
                    </Button>
                    </Link>
                </div>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Plus,
    MoreHorizontal,
    Eye,
    Edit,
    Send,
    Download,
    Trash2,
    FileText,
    Share
} from 'lucide-vue-next';
import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';
import contractRoutes from '@/routes/tenant/coach/clients/contracts';
import ContractController from '@/actions/App/Http/Controllers/Tenant/Coach/ContractController';
import PageHeader from '@/components/PageHeader.vue';
import { alertConfirm } from '@/plugins/alert';

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
    can: {
        update: boolean;
        delete: boolean;
    };
}

const props = defineProps<{
    client: Client;
    contracts: Contract[];
    can: {
        create: boolean;
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
        href: contractRoutes.index(props.client.id).url,
    }
];

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getTemplateName = (templatePath: string) => {
    const templateNames: Record<string, string> = {
        'contracts.standard_coaching_agreement_1': 'Standard Coaching Agreement',
        'contracts.simple_coaching_agreement': 'Simple Coaching Agreement',
    };
    return templateNames[templatePath] || 'Coaching Contract';
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

const canDownloadPdf = (contract: Contract) => {
    return contract.is_fully_signed; // Can only download when fully signed by both parties
};



const sendContract = async (contractId: number) => {
    const confirmed = await alertConfirm({
        title: 'Send Contract',
        description: 'Are you sure you want to send this contract to the client? Once sent, the contract cannot be edited.',
        confirmText: 'Send Contract',
        variant: 'default'
    });

    if (confirmed) {
        // Option 1: Using Wayfinder action directly
        router.visit(ContractController.send([props.client.id, contractId]));

        // Option 2: Using route with manual method (alternative)
        // router.patch(contractRoutes.send([props.client.id, contractId]).url);
    }
}

const downloadPdf = (contractId: number) => {
    window.open(contractRoutes.pdf([props.client.id, contractId]).url, '_blank');
};

const copySigningLink = async (contract: Contract) => {
    if (!contract.signing_token) return;

    const signingUrl = `${window.location.origin}/client/sign-contract/${contract.signing_token}`;

    try {
        await navigator.clipboard.writeText(signingUrl);
        alert('Client signing link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = signingUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Client signing link copied to clipboard!');
    }
};

const copyCoachSigningLink = async (contract: Contract) => {
    if (!contract.coach_signing_token) return;

    const coachSigningUrl = `${window.location.origin}/coach/sign-contract/${contract.coach_signing_token}`;

    try {
        await navigator.clipboard.writeText(coachSigningUrl);
        alert('Coach signing link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = coachSigningUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Coach signing link copied to clipboard!');
    }
};

const signAsCoach = (contract: Contract) => {
    if (!contract.coach_signing_token) return;

    const coachSigningUrl = `${window.location.origin}/coach/sign-contract/${contract.coach_signing_token}`;
    window.open(coachSigningUrl, '_blank');
};

const deleteContract = async (contractId: number) => {
    const confirmed = await alertConfirm({
        title: 'Delete Contract',
        description: 'Are you sure you want to delete this contract? This action cannot be undone.',
        confirmText: 'Delete Contract',
        variant: 'destructive'
    });

    if (confirmed) {
        // Option 1: Using Wayfinder action directly (RECOMMENDED)
        router.visit(ContractController.destroy([props.client.id, contractId]));

        // Option 2: Using route with manual method (alternative)
        // router.delete(contractRoutes.destroy([props.client.id, contractId]).url);
    }
};
</script>