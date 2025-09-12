<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Manage Subscription" />

        <section class="py-10">
            <div class="container  px-4 md:px-6">
                <div class="">
                    <!-- Current Subscription Section -->
                    <div class="space-y-6">
                        <HeadingSmall title="Current Subscription"
                            description="Manage your subscription plan and billing details" />

                        <!-- Subscription Status Card -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <CardTitle>{{ getCurrentPlanName() }}</CardTitle>
                                        <CardDescription>
                                            <div class="flex items-center gap-2">
                                                <Badge :variant="getStatusVariant()" class="capitalize">
                                                    {{ getStatusLabel() }}
                                                </Badge>
                                                <span v-if="subscription?.on_trial"
                                                    class="text-sm text-muted-foreground">
                                                    Trial ends {{ formatDate(subscription.trial_ends_at) }}
                                                </span>
                                            </div>
                                        </CardDescription>
                                    </div>
                                    <div class="text-right">
                                        <div v-if="nextPayment" class="space-y-1">
                                            <p class="text-sm text-muted-foreground">Next payment</p>
                                            <p class="text-2xl font-bold">{{ formatCurrency(nextPayment.amount,
                                                nextPayment.currency) }}</p>
                                            <p class="text-sm text-muted-foreground">{{ formatDate(nextPayment.date) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Subscription ID</p>
                                        <p class="text-sm">{{ subscription?.paddle_id }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Started</p>
                                        <p class="text-sm">{{ formatDate(subscription?.created_at || null) }}</p>
                                    </div>
                                    <div v-if="subscription?.quantity && subscription.quantity > 1">
                                        <p class="text-sm font-medium text-muted-foreground">Quantity</p>
                                        <p class="text-sm">{{ subscription.quantity }} seats</p>
                                    </div>
                                    <div v-if="lastPayment">
                                        <p class="text-sm font-medium text-muted-foreground">Last payment</p>
                                        <p class="text-sm">{{ formatCurrency(lastPayment.amount, lastPayment.currency)
                                            }} on
                                            {{
                                                formatDate(lastPayment.date) }}</p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <Separator class="my-6" />
                                <div class="flex flex-wrap gap-3">
                                    <Button @click="updatePaymentMethod" variant="outline"
                                        class="flex items-center gap-2">
                                        <CreditCard class="h-4 w-4" />
                                        Update Payment Method
                                    </Button>

                                    <Button v-if="subscription?.on_grace_period" @click="resumeSubscription"
                                        variant="default" class="flex items-center gap-2">
                                        <Play class="h-4 w-4" />
                                        Resume Subscription
                                    </Button>

                                    <Button v-else-if="!subscription?.canceled" @click="showCancelDialog"
                                        variant="destructive" class="flex items-center gap-2">
                                        <XCircle class="h-4 w-4" />
                                        Cancel Subscription
                                    </Button>
                                </div>

                                <!-- Grace Period Notice -->
                                <Alert v-if="subscription?.on_grace_period" class="mt-4" variant="warning">
                                    <AlertCircle class="h-4 w-4" />
                                    <AlertTitle>Subscription Ending</AlertTitle>
                                    <AlertDescription>
                                        Your subscription will end on {{ formatDate(subscription.ends_at) }}. You can
                                        resume
                                        your subscription at any time before this date.
                                    </AlertDescription>
                                </Alert>

                                <!-- Past Due Notice -->
                                <Alert v-if="subscription?.past_due" class="mt-4" variant="destructive">
                                    <AlertCircle class="h-4 w-4" />
                                    <AlertTitle>Payment Required</AlertTitle>
                                    <AlertDescription>
                                        Your subscription payment failed. Please update your payment method to continue
                                        using
                                        the service.
                                    </AlertDescription>
                                </Alert>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Billing History Section -->
                    <div class="space-y-6 mt-4">
                        <HeadingSmall title="Billing History"
                            description="View and download your past invoices and receipts" />

                        <Card>
                            <CardContent class="p-0">
                                <div v-if="transactions.length > 0" class="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Invoice</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead class="text-right">Amount</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="transaction in transactions" :key="transaction.id">
                                                <TableCell>{{ formatDate(transaction.billed_at) }}</TableCell>
                                                <TableCell>
                                                    <span class="font-mono text-sm">{{ transaction.invoice_number ||
                                                        transaction.paddle_id }}</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary" class="capitalize">
                                                        {{ transaction.status }}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell class="text-right font-medium">
                                                    {{ formatCurrency(transaction.total, transaction.currency) }}
                                                    <!-- <span v-if="transaction.tax > 0"
                                                    class="text-xs text-muted-foreground block">
                                                    (incl. {{ formatCurrency(transaction.tax, transaction.currency) }}
                                                    tax)
                                                </span> -->
                                                </TableCell>
                                                <TableCell class="text-right">
                                                    <Button @click="downloadInvoice(transaction.id)" variant="ghost"
                                                        size="sm" class="flex items-center gap-2">
                                                        <Download class="h-4 w-4" />
                                                        Invoice
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                                <div v-else class="p-8 text-center">
                                    <Receipt class="mx-auto h-12 w-12 text-muted-foreground/50" />
                                    <p class="mt-4 text-sm text-muted-foreground">No billing history available yet</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Manage with Paddle Section -->
                    <!-- <div class="space-y-6">
                    <HeadingSmall title="Billing Portal"
                        description="Access Paddle's customer portal for additional billing options" />

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">Customer Portal</p>
                                    <p class="text-sm text-muted-foreground">
                                        View detailed billing information, update tax details, and manage your
                                        subscription
                                        directly with Paddle
                                    </p>
                                </div>
                                <Button @click="openPaddlePortal" variant="outline" class="flex items-center gap-2">
                                    <ExternalLink class="h-4 w-4" />
                                    Open Paddle Portal
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div> -->
                </div>
            </div>
        </section>

    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
// import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { BreadcrumbItem } from '@/types';
import { manage } from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
import { alertConfirm } from '@/plugins/alert';
import SubscriptionController from '@/actions/App/Http/Controllers/Tenant/Admin/SubscriptionController';
import {
    CreditCard,
    Download,
    XCircle,
    Play,
    AlertCircle,
    Receipt
} from 'lucide-vue-next';

interface Subscription {
    id: string;
    paddle_id: string;
    status: string;
    paddle_status: string;
    quantity: number;
    created_at: string;
    updated_at: string;
    ends_at: string | null;
    paused_at: string | null;
    trial_ends_at: string | null;
    on_trial: boolean;
    on_grace_period: boolean;
    on_paused_grace_period: boolean;
    canceled: boolean;
    active: boolean;
    past_due: boolean;
    recurring: boolean;
}

interface Transaction {
    id: string;
    paddle_id: string;
    status: string;
    total: string;
    tax: string;
    currency: string;
    billed_at: string;
    invoice_number: string | null;
}

interface Payment {
    amount: string;
    currency: string;
    date: string;
}

interface Props {
    subscription: Subscription | null;
    transactions: Transaction[];
    nextPayment: Payment | null;
    lastPayment: Payment | null;
    plans: Record<string, any>;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: manage().url,
    }
];

// Get current plan name from config
const getCurrentPlanName = () => {
    // You might need to match the subscription's price_id with your plans config
    // For now, returning a placeholder
    return 'Professional Plan';
};

// Get status variant for badge
const getStatusVariant = () => {
    if (!props.subscription) return 'secondary';
    if (props.subscription.past_due) return 'destructive';
    if (props.subscription.on_trial) return 'default';
    if (props.subscription.on_grace_period) return 'warning';
    if (props.subscription.active) return 'success';
    return 'secondary';
};

// Get human-readable status label
const getStatusLabel = () => {
    if (!props.subscription) return 'No subscription';
    if (props.subscription.past_due) return 'Past due';
    if (props.subscription.on_trial) return 'Trial';
    if (props.subscription.on_grace_period) return 'Canceling';
    if (props.subscription.active) return 'Active';
    if (props.subscription.canceled) return 'Canceled';
    return props.subscription.status;
};

// Format date
const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Format currency
const formatCurrency = (amount: string | number, currency: string) => {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || 'USD'
    }).format(numAmount ? numAmount / 100 : 0); // Paddle stores amounts in cents
};

// Update payment method
const updatePaymentMethod = () => {
    router.visit(SubscriptionController.updatePaymentMethod().url);
};

// Show cancel subscription dialog
const showCancelDialog = async () => {
    const confirmed = await alertConfirm({
        title: 'Cancel Subscription',
        description: 'Are you sure you want to cancel your subscription? You will continue to have access until the end of your billing period.',
        confirmText: 'Cancel Subscription',
        variant: 'destructive'
    });

    if (confirmed) {
        router.post(SubscriptionController.cancel(), {});
    }
};

// Resume subscription
const resumeSubscription = async () => {
    const confirmed = await alertConfirm({
        title: 'Resume Subscription',
        description: 'Would you like to resume your subscription? Your billing will continue as normal.',
        confirmText: 'Resume Subscription',
        variant: 'default'
    });

    if (confirmed) {
        router.post(SubscriptionController.resume(), {});
    }
};

// Download invoice
const downloadInvoice = (transactionId: string) => {
    window.open(SubscriptionController.downloadInvoice(transactionId).url, '_blank');
};

// Open Paddle customer portal
// const openPaddlePortal = () => {
//     // This would typically redirect to Paddle's customer portal
//     // You might need to implement a backend route that generates the portal URL
//     alertInfo('You will now be redirected to Paddle\'s customer portal');
//     router.visit(SubscriptionController.billingPortal().url);
// };
</script>