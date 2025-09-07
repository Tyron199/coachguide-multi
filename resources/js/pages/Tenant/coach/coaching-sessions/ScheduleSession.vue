<template>

    <Head title="Schedule Session" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionsLayout>
            <div class="space-y-6">
                <PageHeader title="Schedule Session" description="Schedule a new coaching session with a client" />

                <div class="max-w-2xl">
                    <Form :action="storeSessionAction()" class="space-y-6" v-slot="{ errors, processing }">
                        <div class="grid gap-2">
                            <Label for="client_id">Client</Label>
                            <Select :model-value="formData.client_id?.toString()" @update:model-value="selectClient">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="selectedClient">{{ selectedClient.name }} ({{ selectedClient.email
                                        }})</span>
                                        <span v-else class="text-muted-foreground">Select a client</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="client in clients" :key="client.id"
                                        :value="client.id.toString()">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ client.name }}</span>
                                            <span class="text-sm text-muted-foreground">{{ client.email }}</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="client_id" :value="formData.client_id" />
                            <InputError :message="errors.client_id" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="scheduled_date">Date</Label>
                                <Input id="scheduled_date" name="scheduled_date" type="date" class="mt-1 block w-full"
                                    v-model="formData.scheduled_date" required />
                                <InputError :message="errors.scheduled_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="scheduled_time">Time</Label>
                                <Input id="scheduled_time" name="scheduled_time" type="time" class="mt-1 block w-full"
                                    v-model="formData.scheduled_time" required />
                                <InputError :message="errors.scheduled_time" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="duration">Duration</Label>
                            <Select :model-value="formData.duration.toString()" @update:model-value="selectDuration">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select duration" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="30">30 minutes</SelectItem>
                                    <SelectItem value="45">45 minutes</SelectItem>
                                    <SelectItem value="60">1 hour</SelectItem>
                                    <SelectItem value="90">1 hour 30 minutes</SelectItem>
                                    <SelectItem value="120">2 hours</SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="duration" :value="formData.duration" />
                            <InputError :message="errors.duration" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="session_type">Session Type</Label>
                            <Select :model-value="formData.session_type" @update:model-value="selectSessionType">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="selectedSessionType" class="flex items-center">
                                            <component :is="selectedSessionType.icon" class="mr-2 h-4 w-4" />
                                            {{ selectedSessionType.label }}
                                        </span>
                                        <span v-else class="text-muted-foreground">Select session type</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="in_person">
                                        <div class="flex items-center">
                                            <Users class="mr-2 h-4 w-4" />
                                            <div class="flex flex-col items-start">
                                                <span class="font-medium">In Person</span>
                                                <span class="text-sm text-muted-foreground">Face-to-face meeting</span>
                                            </div>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="online">
                                        <div class="flex items-center">
                                            <Video class="mr-2 h-4 w-4" />
                                            <div class="flex flex-col items-start">
                                                <span class="font-medium">Online</span>
                                                <span class="text-sm text-muted-foreground">Video call session</span>
                                            </div>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="hybrid">
                                        <div class="flex items-center">
                                            <MonitorSpeaker class="mr-2 h-4 w-4" />
                                            <div class="flex flex-col items-start">
                                                <span class="font-medium">Hybrid</span>
                                                <span class="text-sm text-muted-foreground">Mix of in-person and
                                                    online</span>
                                            </div>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="session_type" :value="formData.session_type" />
                            <InputError :message="errors.session_type" />
                        </div>

                        <!-- Computed End Time Display -->
                        <div v-if="computedEndTime" class="p-4 bg-muted rounded-lg">
                            <div class="flex items-center gap-2">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium">Session will end at:</span>
                                <span class="text-sm text-muted-foreground">{{ computedEndTime }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="coachingSessions.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing || !isFormValid">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Calendar v-else class="mr-2 h-4 w-4" />
                                Schedule Session
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </CoachingSessionsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionsLayout from '@/layouts/coaching-sessions/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import coachingSessions from '@/routes/tenant/coaching-sessions';
import { store as storeSessionAction } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import { LoaderCircle, Calendar, Clock, Users, Video, MonitorSpeaker } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

interface Client {
    id: number;
    name: string;
    email: string;
}

interface Props {
    clients: Client[];
}

const props = defineProps<Props>();

const formData = ref({
    client_id: null as number | null,
    scheduled_date: '',
    scheduled_time: '',
    duration: 60 as number, // Default to 1 hour
    session_type: 'online' as 'in_person' | 'online' | 'hybrid', // Default to online
});

// Check for client_id in URL params and pre-fill if it exists
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const clientIdParam = urlParams.get('client_id');

    if (clientIdParam) {
        const clientId = Number(clientIdParam);
        // Verify the client exists in our list
        const client = props.clients.find(c => c.id === clientId);
        if (client) {
            formData.value.client_id = clientId;
        }
    }

    // Set default date to today
    const today = new Date();
    formData.value.scheduled_date = today.toISOString().split('T')[0];

    // Set default time to next hour
    const nextHour = new Date();
    nextHour.setHours(nextHour.getHours() + 1, 0, 0, 0);
    formData.value.scheduled_time = nextHour.toTimeString().slice(0, 5);
});

const selectedClient = computed(() => {
    if (formData.value.client_id === null) return null;
    return props.clients.find(c => c.id === Number(formData.value.client_id)) || null;
});

const selectedSessionType = computed(() => {
    if (!formData.value.session_type) return null;

    const sessionTypes = {
        'in_person': { label: 'In Person', description: 'Face-to-face meeting', icon: Users },
        'online': { label: 'Online', description: 'Video call session', icon: Video },
        'hybrid': { label: 'Hybrid', description: 'Mix of in-person and online', icon: MonitorSpeaker }
    };

    return sessionTypes[formData.value.session_type] || null;
});

const computedEndTime = computed(() => {
    if (!formData.value.scheduled_date || !formData.value.scheduled_time || !formData.value.duration) {
        return null;
    }

    try {
        const startDateTime = new Date(`${formData.value.scheduled_date}T${formData.value.scheduled_time}`);
        const endDateTime = new Date(startDateTime.getTime() + (formData.value.duration * 60 * 1000));

        return endDateTime.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch {
        return null;
    }
});



const isFormValid = computed(() => {
    return formData.value.client_id !== null &&
        formData.value.scheduled_date !== '' &&
        formData.value.scheduled_time !== '' &&
        formData.value.duration > 0 &&
        formData.value.session_type;
});

const selectClient = (clientId: any) => {
    formData.value.client_id = clientId ? Number(clientId) : null;
};

const selectDuration = (duration: any) => {
    if (duration) {
        formData.value.duration = Number(duration);
    }
};

const selectSessionType = (sessionType: any) => {
    if (sessionType) {
        formData.value.session_type = sessionType as 'in_person' | 'online' | 'hybrid';
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: coachingSessions.index().url
    },
    {
        title: 'Schedule Session',
        href: coachingSessions.create().url
    },
];
</script>
