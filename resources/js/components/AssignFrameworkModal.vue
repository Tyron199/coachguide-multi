<template>
    <Dialog :open="props.isOpen" @update:open="handleClose">
        <DialogContent class="sm:max-w-md" @click="handleClickOutside">
            <DialogHeader>
                <DialogTitle>Assign Framework to Session</DialogTitle>
                <DialogDescription>
                    Select a framework, client, and session to create a new assignment.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                    <span class="ml-2 text-sm text-muted-foreground">Loading...</span>
                </div>

                <!-- Form -->
                <div v-else class="space-y-4">
                    <!-- Framework Selection -->
                    <div v-if="!preSelectedFramework" class="space-y-2">
                        <Label for="framework">Select Framework</Label>
                        <Select v-model="selectedFramework" @update:model-value="handleFrameworkChange">
                            <SelectTrigger>
                                <SelectValue placeholder="Choose a framework..." />
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
                        <div v-if="errors.framework_id" class="text-sm text-destructive">{{ errors.framework_id }}</div>

                        <!-- Explore Frameworks Link -->
                        <div class="mt-2">
                            <Link :href="frameworkRoutes.index().url"
                                class="inline-flex items-center text-xs text-muted-foreground hover:text-foreground transition-colors">
                            <ExternalLink class="mr-1 h-3 w-3" />
                            Explore all frameworks
                            </Link>
                        </div>
                    </div>

                    <!-- Framework Info (when pre-selected) -->
                    <div v-else class="rounded-lg border p-3 bg-muted/20">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-medium text-sm">{{ preSelectedFramework.name }}</h4>
                                <p class="text-xs text-muted-foreground mt-1">{{ preSelectedFramework.description }}</p>
                            </div>
                            <Badge :variant="preSelectedFramework.category === 'models' ? 'default' : 'secondary'">
                                {{ preSelectedFramework.category === 'models' ? 'Model' : 'Tool' }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Client Selection -->
                    <div v-if="!preSelectedClient" class="space-y-2">
                        <Label for="client">Select Client</Label>
                        <div class="relative" @click.stop>
                            <Button variant="outline" role="combobox" :aria-expanded="clientComboboxOpen"
                                class="w-full justify-between text-left font-normal"
                                @click="clientComboboxOpen = !clientComboboxOpen">
                                <span v-if="selectedClientName" class="truncate">{{ selectedClientName }}</span>
                                <span v-else class="text-muted-foreground">Choose a client...</span>
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>

                            <!-- Custom Dropdown -->
                            <div v-if="clientComboboxOpen"
                                class="absolute z-50 w-full mt-1 bg-background border rounded-md shadow-lg" @click.stop>
                                <div class="p-2">
                                    <Input v-model="clientSearchQuery" placeholder="Search clients..." class="text-sm"
                                        ref="searchInput" />
                                </div>
                                <div class="max-h-60 overflow-auto">
                                    <div v-if="filteredClients.length === 0"
                                        class="px-3 py-2 text-sm text-muted-foreground">
                                        No clients found
                                    </div>
                                    <div v-for="client in filteredClients" :key="client.id"
                                        class="px-3 py-2 cursor-pointer hover:bg-accent hover:text-accent-foreground text-sm"
                                        @click="() => {
                                            handleClientChange(client.id);
                                            clientComboboxOpen = false;
                                            clientSearchQuery = '';
                                        }">
                                        <div class="font-medium">{{ client.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ client.email }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="errors.client_id" class="text-sm text-destructive">{{ errors.client_id }}</div>
                    </div>

                    <!-- Client Info (when pre-selected) -->
                    <div v-else class="rounded-lg border p-3 bg-muted/20">
                        <h4 class="font-medium text-sm">{{ preSelectedClient.name }}</h4>
                        <p class="text-xs text-muted-foreground">{{ preSelectedClient.email }}</p>
                    </div>

                    <!-- Session Selection -->
                    <div v-if="!preSelectedSession && (selectedClient || preSelectedClient)" class="space-y-2">
                        <Label for="session">Select Session</Label>
                        <div v-if="loadingSessions" class="flex items-center gap-2 text-sm text-muted-foreground">
                            <LoaderCircle class="h-4 w-4 animate-spin" />
                            Loading sessions...
                        </div>
                        <Select v-else-if="clientSessions && clientSessions.length > 0" v-model="selectedSession">
                            <SelectTrigger>
                                <div v-if="selectedSessionInfo" class="flex flex-col items-start text-left">
                                    <span class="font-medium text-sm">Session #{{ selectedSessionInfo.number }}</span>
                                    <span class="text-xs text-muted-foreground">{{ selectedSessionInfo.date }} • {{
                                        selectedSessionInfo.duration }}</span>
                                </div>
                                <span v-else class="text-muted-foreground">Choose a session...</span>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="session in clientSessions" :key="session.id"
                                    :value="session.id.toString()">
                                    <div>
                                        <div class="font-medium text-sm">Session #{{ session.session_number }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ formatDate(session.scheduled_at) }} • {{ session.formatted_duration }}
                                        </div>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-else-if="clientSessions && clientSessions.length === 0"
                            class="text-sm text-muted-foreground">
                            No upcoming sessions found for this client.
                        </div>
                        <div v-if="errors.session_id" class="text-sm text-destructive">{{ errors.session_id }}</div>
                    </div>

                    <!-- Session Info (when pre-selected) -->
                    <div v-else-if="preSelectedSession" class="rounded-lg border p-3 bg-muted/20">
                        <h4 class="font-medium text-sm">Session #{{ preSelectedSession.session_number }}</h4>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(preSelectedSession.scheduled_at) }} • {{ preSelectedSession.formatted_duration
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleCancel" :disabled="submitting">
                    Cancel
                </Button>
                <Button @click="handleSubmit" :disabled="!canSubmit || submitting">
                    <LoaderCircle v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
                    <Plus v-else class="mr-2 h-4 w-4" />
                    Assign Framework
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChevronsUpDown, LoaderCircle, Plus, ExternalLink } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { Link } from '@inertiajs/vue3';
import frameworkRoutes from '@/routes/tenant/coach/coaching-frameworks';

interface Framework {
    id: number;
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
}

interface Props {
    isOpen: boolean;
    preSelectedFramework?: Framework | null;
    preSelectedClient?: Client | null;
    preSelectedSession?: Session | null;
}

const props = withDefaults(defineProps<Props>(), {
    preSelectedFramework: null,
    preSelectedClient: null,
    preSelectedSession: null,
});

const emit = defineEmits<{
    'update:isOpen': [value: boolean];
    'success': [instance: any];
}>();

// State
const loading = ref(false);
const submitting = ref(false);
const loadingSessions = ref(false);
const frameworks = ref<Framework[]>([]);
const clients = ref<Client[]>([]);
const clientSessions = ref<Session[]>([]);
const selectedFramework = ref<string>(props.preSelectedFramework?.id.toString() || '');
const selectedClient = ref<string>(props.preSelectedClient?.id.toString() || '');
const selectedSession = ref<string>(props.preSelectedSession?.id.toString() || '');
const errors = ref<Record<string, string>>({});
const clientComboboxOpen = ref<boolean>(false);
const clientSearchQuery = ref<string>('');
const searchInput = ref<HTMLInputElement | null>(null);

// Computed
const canSubmit = computed(() => {
    const hasFramework = selectedFramework.value || props.preSelectedFramework;
    const hasClient = selectedClient.value || props.preSelectedClient;
    const hasSession = selectedSession.value || props.preSelectedSession;
    return !!(hasFramework && hasClient && hasSession) && !loading.value;
});

const selectedClientName = computed(() => {
    if (props.preSelectedClient) return props.preSelectedClient.name;
    if (!selectedClient.value) return '';

    const client = clients.value.find(c => c.id.toString() === selectedClient.value);
    return client ? client.name : '';
});

const selectedSessionInfo = computed(() => {
    if (props.preSelectedSession) {
        return {
            number: props.preSelectedSession.session_number,
            date: formatDate(props.preSelectedSession.scheduled_at),
            duration: props.preSelectedSession.formatted_duration
        };
    }
    if (!selectedSession.value) return null;

    const session = clientSessions.value.find(s => s.id.toString() === selectedSession.value);
    if (!session) return null;

    return {
        number: session.session_number,
        date: formatDate(session.scheduled_at),
        duration: session.formatted_duration
    };
});

const filteredClients = computed(() => {
    if (!clientSearchQuery.value) {
        return clients.value;
    }

    const query = clientSearchQuery.value.toLowerCase();
    return clients.value.filter(client =>
        client.name.toLowerCase().includes(query) ||
        client.email.toLowerCase().includes(query)
    );
});

// Watch for modal opening
watch(() => props.isOpen, (isOpen) => {
    if (isOpen && !loading.value) {
        loadModalData();
    }
});

// Watch for client changes
watch(selectedClient, (newClientId) => {
    if (newClientId && !props.preSelectedClient) {
        loadClientSessions(newClientId);
    }
});

// Watch for combobox opening to focus search input
watch(clientComboboxOpen, async (isOpen) => {
    if (isOpen) {
        await nextTick();
        searchInput.value?.focus();
    }
});

// Methods
async function loadModalData() {
    loading.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/coaching-frameworks/modal-data', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin', // Include session cookies
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        frameworks.value = data.frameworks;
        clients.value = data.clients;

        // If we have a pre-selected client, load their sessions
        if (props.preSelectedClient) {
            await loadClientSessions(props.preSelectedClient.id.toString());
        } else if (selectedClient.value) {
            await loadClientSessions(selectedClient.value);
        }
    } catch (error) {
        console.error('Failed to load modal data:', error);
        toast.error('Failed to load form data');
    } finally {
        loading.value = false;
    }
}

async function loadClientSessions(clientId: string) {
    if (!clientId) return;

    loadingSessions.value = true;
    selectedSession.value = ''; // Reset session selection

    try {
        const response = await fetch(`/api/coaching-sessions/for-client/${clientId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        clientSessions.value = data.sessions;
    } catch (error) {
        console.error('Failed to load client sessions:', error);
        toast.error('Failed to load sessions for selected client');
        clientSessions.value = [];
    } finally {
        loadingSessions.value = false;
    }
}

async function handleSubmit() {
    if (!canSubmit.value) return;

    submitting.value = true;
    errors.value = {};

    const formData = {
        framework_id: props.preSelectedFramework?.id || parseInt(selectedFramework.value),
        client_id: props.preSelectedClient?.id || parseInt(selectedClient.value),
        session_id: props.preSelectedSession?.id || parseInt(selectedSession.value),
    };

    try {
        const response = await fetch('/api/coaching-frameworks/assign', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                // Validation errors
                errors.value = data.errors;
                toast.error('Please check the form for errors');
            } else {
                // Other errors
                const errorMessage = data.message || 'Failed to assign framework';
                toast.error(errorMessage);
                console.error('Assignment failed:', errorMessage);
            }
            return;
        }

        // Success!
        toast.success(data.message || 'Framework assigned successfully!');
        emit('success', data.instance);
        emit('update:isOpen', false);

        // Refresh the current page data
        router.reload({ only: ['instances', 'frameworks'] });

    } catch (error) {
        console.error('Failed to assign framework:', error);
        toast.error('Network error: Failed to assign framework');
    } finally {
        submitting.value = false;
    }
}

function handleFrameworkChange(frameworkId: any) {
    selectedFramework.value = frameworkId?.toString() || '';
    if (errors.value.framework_id) {
        delete errors.value.framework_id;
    }
}

function handleClientChange(clientId: any) {
    selectedClient.value = clientId?.toString() || '';
    selectedSession.value = ''; // Reset session when client changes
    if (errors.value.client_id) {
        delete errors.value.client_id;
    }
}

function handleClose(isOpen: boolean) {
    emit('update:isOpen', isOpen);

    // Reset form state when closing
    if (!isOpen) {
        selectedFramework.value = props.preSelectedFramework?.id.toString() || '';
        selectedClient.value = props.preSelectedClient?.id.toString() || '';
        selectedSession.value = props.preSelectedSession?.id.toString() || '';
        clientSessions.value = [];
        errors.value = {};
        clientComboboxOpen.value = false;
        clientSearchQuery.value = '';
    }
}

function handleCancel() {
    emit('update:isOpen', false);
    // Reset form state
    selectedFramework.value = props.preSelectedFramework?.id.toString() || '';
    selectedClient.value = props.preSelectedClient?.id.toString() || '';
    selectedSession.value = props.preSelectedSession?.id.toString() || '';
    clientSessions.value = [];
    errors.value = {};
    clientComboboxOpen.value = false;
    clientSearchQuery.value = '';
}

// Close dropdown when clicking outside
function handleClickOutside() {
    clientComboboxOpen.value = false;
    clientSearchQuery.value = '';
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
