<template>

    <Head title="Assign Tool/Model" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader :title="title" :description="description" />

                <div class="max-w-2xl">
                    <Form :action="frameworkRoutes.storeAssignment()" class="space-y-6" v-slot="{ errors, processing }">
                        <!-- Framework Selection -->
                        <div v-if="!preSelected.framework" class="grid gap-2">
                            <Label for="framework_id">Select Tool or Model</Label>
                            <Select v-model="selectedFramework" @update:model-value="handleFrameworkChange">
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose a tool or model..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="framework in frameworks" :key="framework.id"
                                        :value="framework.id.toString()">
                                        <div class="flex items-center justify-between w-full">
                                            <span>{{ framework.name }}</span>
                                            <Badge :variant="framework.category === 'models' ? 'default' : 'secondary'"
                                                class="ml-2 text-xs">
                                                {{ framework.category === 'models' ? 'Model' : 'Tool' }}
                                            </Badge>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="framework_id" :value="selectedFramework" />
                            <InputError :message="errors.framework_id" />
                        </div>

                        <!-- Framework Info (when pre-selected) -->
                        <div v-else class="rounded-lg border p-4 bg-muted/20">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-medium text-foreground">{{ preSelected.framework.name }}</h3>
                                    <p class="text-sm text-muted-foreground mt-1">{{ preSelected.framework.description
                                    }}
                                    </p>
                                </div>
                                <Badge :variant="preSelected.framework.category === 'models' ? 'default' : 'secondary'">
                                    {{ preSelected.framework.category === 'models' ? 'Model' : 'Tool' }}
                                </Badge>
                            </div>
                            <input type="hidden" name="framework_id" :value="preSelected.framework.id" />
                        </div>

                        <!-- Client Selection -->
                        <div v-if="!preSelected.client_id" class="grid gap-2">
                            <Label for="client_id">Select Client</Label>
                            <Select v-model="selectedClient" @update:model-value="handleClientChange">
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose a client..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="client in clients" :key="client.id"
                                        :value="client.id.toString()">
                                        <div>
                                            <div class="font-medium">{{ client.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ client.email }}</div>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="client_id" :value="selectedClient" />
                            <InputError :message="errors.client_id" />
                        </div>

                        <!-- Client Info (when pre-selected) -->
                        <div v-else class="rounded-lg border p-4 bg-muted/20">
                            <div>
                                <h3 class="font-medium text-foreground">{{ getSelectedClientName() }}</h3>
                                <p class="text-sm text-muted-foreground mt-1">{{ getSelectedClientEmail() }}</p>
                            </div>
                            <input type="hidden" name="client_id" :value="preSelected.client_id" />
                        </div>

                        <!-- Session Selection -->
                        <div v-if="!preSelected.session_id && (selectedClient || preSelected.client_id)"
                            class="grid gap-2">
                            <Label for="session_id">Select Session</Label>
                            <div v-if="loadingSessions" class="flex items-center gap-2 text-sm text-muted-foreground">
                                <LoaderCircle class="h-4 w-4 animate-spin" />
                                Loading sessions...
                            </div>
                            <Select v-else-if="clientSessions && clientSessions.length > 0" v-model="selectedSession">
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose a session..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="session in clientSessions" :key="session.id"
                                        :value="session.id.toString()">
                                        <div>
                                            <div class="font-medium">Session #{{ session.session_number }}</div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ formatDate(session.scheduled_at) }} • {{ session.formatted_duration
                                                }}
                                            </div>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-else-if="clientSessions && clientSessions.length === 0"
                                class="text-sm text-muted-foreground">
                                No upcoming sessions found for this client.
                            </div>
                            <input type="hidden" name="session_id" :value="selectedSession" />
                            <InputError :message="errors.session_id" />
                        </div>

                        <!-- Session Info (when pre-selected) -->
                        <div v-else-if="preSelected.session_id && preSelected.session"
                            class="rounded-lg border p-4 bg-muted/20">
                            <div>
                                <h3 class="font-medium text-foreground">Session #{{ preSelected.session.session_number
                                }}
                                </h3>
                                <p class="text-sm text-muted-foreground mt-1">
                                    {{ formatDate(preSelected.session.scheduled_at) }} • {{
                                        preSelected.session.formatted_duration }}
                                </p>
                            </div>
                            <input type="hidden" name="session_id" :value="preSelected.session_id" />
                        </div>

                        <!-- Assignment Summary -->
                        <div v-if="canSubmit" class="rounded-lg border p-4 bg-primary/5 border-primary/20">
                            <h3 class="font-medium text-foreground mb-2">Assignment Summary</h3>
                            <div class="space-y-1 text-sm">
                                <div><strong>{{ getFrameworkTypeLabel() }}:</strong> {{ getSelectedFrameworkName() }}
                                </div>
                                <div><strong>Client:</strong> {{ getSelectedClientName() }}</div>
                                <div><strong>Session:</strong> {{ getSelectedSessionInfo() }}</div>
                            </div>
                        </div>

                        <!-- Submit Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <Button type="button" variant="outline" @click="handleCancel">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="!canSubmit || processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Assign & Start
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </CoachingFrameworksLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { LoaderCircle, Plus } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
}

interface Client {
    id: number;
    name: string;
    email: string;
}

interface Session {
    id: number;
    scheduled_at: string;
    duration: number;
    session_type: string;
    session_number: number;
    formatted_duration: string;
    client?: {
        name: string;
        email: string;
    };
}

interface PreSelected {
    framework: Framework | null;
    client_id: number | null;
    session_id: number | null;
    session: Session | null;
}

interface Props {
    frameworks: Framework[] | null;
    clients: Client[];
    preSelected: PreSelected;
    clientSessions?: Session[];
}

const props = defineProps<Props>();

// Form state
const selectedFramework = ref<string>(props.preSelected.framework?.id.toString() || '');
const selectedClient = ref<string>(props.preSelected.client_id?.toString() || '');
const selectedSession = ref<string>(props.preSelected.session_id?.toString() || '');
const loadingSessions = ref(false);

// Computed properties
const title = computed(() => {
    if (props.preSelected.framework) {
        const type = props.preSelected.framework.category === 'models' ? 'Model' : 'Tool';
        return `Assign ${type} to Session`;
    }
    return 'Assign Tool or Model';
});

const description = computed(() => {
    return 'Select the client and session to use this tool or model with';
});

const canSubmit = computed(() => {
    const hasFramework = selectedFramework.value || props.preSelected.framework;
    const hasClient = selectedClient.value || props.preSelected.client_id;
    const hasSession = selectedSession.value || props.preSelected.session_id;
    return !!(hasFramework && hasClient && hasSession);
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tools & Models',
        href: frameworkRoutes.index().url
    },
    {
        title: 'Assign to Session',
        href: frameworkRoutes.assign().url
    },
];

// Watch for client changes to load sessions
watch(selectedClient, (newClientId) => {
    if (newClientId && !props.preSelected.client_id) {
        handleClientChange(newClientId);
    }
}, { immediate: true });


// Methods
function handleFrameworkChange(frameworkId: any): void {
    selectedFramework.value = frameworkId?.toString() || '';
}

function handleClientChange(clientId: any): void {
    selectedClient.value = clientId?.toString() || '';
    selectedSession.value = ''; // Reset session selection

    if (clientId) {
        loadingSessions.value = true;

        // Partial reload to get sessions for this client
        router.reload({
            data: { client_id: clientId.toString() },
            only: ['clientSessions'],
            onFinish: () => {
                loadingSessions.value = false;
            }
        });
    }
}

function handleCancel(): void {
    // Go back to where we came from
    if (props.preSelected.framework) {
        //if category is models, go to models page, if category is tools, go to tools page
        if (props.preSelected.framework.category === 'models') {
            router.visit(frameworkRoutes.models().url);
        } else {
            router.visit(frameworkRoutes.tools().url);
        }
    } else {
        router.visit(frameworkRoutes.index().url);
    }
}

// Helper methods for display
function getSelectedFrameworkName(): string {
    if (props.preSelected.framework) return props.preSelected.framework.name;
    if (selectedFramework.value && props.frameworks) {
        const framework = props.frameworks.find(f => f.id.toString() === selectedFramework.value);
        return framework?.name || '';
    }
    return '';
}

function getFrameworkTypeLabel(): string {
    let framework = props.preSelected.framework;
    if (!framework && selectedFramework.value && props.frameworks) {
        framework = props.frameworks.find(f => f.id.toString() === selectedFramework.value) || null;
    }
    return framework?.category === 'models' ? 'Model' : 'Tool';
}

function getSelectedClientName(): string {
    if (props.preSelected.session?.client) return props.preSelected.session.client.name;
    if (props.preSelected.client_id || selectedClient.value) {
        const clientId = props.preSelected.client_id?.toString() || selectedClient.value;
        const client = props.clients.find(c => c.id.toString() === clientId);
        return client?.name || '';
    }
    return '';
}

function getSelectedClientEmail(): string {
    if (props.preSelected.session?.client) return props.preSelected.session.client.email;
    if (props.preSelected.client_id || selectedClient.value) {
        const clientId = props.preSelected.client_id?.toString() || selectedClient.value;
        const client = props.clients.find(c => c.id.toString() === clientId);
        return client?.email || '';
    }
    return '';
}

function getSelectedSessionInfo(): string {
    if (props.preSelected.session) {
        return `#${props.preSelected.session.session_number} - ${formatDate(props.preSelected.session.scheduled_at)}`;
    }
    if (selectedSession.value && props.clientSessions) {
        const session = props.clientSessions.find(s => s.id.toString() === selectedSession.value);
        return session ? `#${session.session_number} - ${formatDate(session.scheduled_at)}` : '';
    }
    return '';
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>
