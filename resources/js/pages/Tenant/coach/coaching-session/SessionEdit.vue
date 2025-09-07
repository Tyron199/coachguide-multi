<template>

    <Head :title="`Edit Session with ${session.client?.name} - Session`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader title="Edit Session" description="Update session scheduling and details" />

                <Form :action="CoachingSessionController.update(session.id)" class="space-y-6"
                    v-slot="{ errors, processing }">
                    <!-- Session Information Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Session Information</h2>

                        <!-- Client Information (Read-only) -->
                        <div class="mb-6 p-4 bg-muted/50 rounded-lg">
                            <h3 class="text-sm font-medium text-muted-foreground mb-2">Client</h3>
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-medium">{{ session.client?.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ session.client?.email }}</p>
                                </div>
                                <Badge variant="outline">Session #{{ session.session_number }}</Badge>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="scheduled_date">Scheduled Date</Label>
                                <Input id="scheduled_date" name="scheduled_date" type="date" v-model="formScheduledDate"
                                    required />
                                <InputError :message="errors.scheduled_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="scheduled_time">Scheduled Time</Label>
                                <Input id="scheduled_time" name="scheduled_time" type="time" v-model="formScheduledTime"
                                    required />
                                <InputError :message="errors.scheduled_time" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="duration">Duration</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            {{ selectedDurationName }}
                                            <ChevronDown class="ml-2 h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-full min-w-[400px]">
                                        <DropdownMenuItem @click="selectDuration(30)">
                                            30 minutes
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectDuration(45)">
                                            45 minutes
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectDuration(60)">
                                            1 hour
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectDuration(90)">
                                            1 hour 30 minutes
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectDuration(120)">
                                            2 hours
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                                <input type="hidden" name="duration" :value="formData.duration" />
                                <InputError :message="errors.duration" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="session_type">Session Type</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            {{ selectedSessionTypeName }}
                                            <ChevronDown class="ml-2 h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-full min-w-[400px]">
                                        <DropdownMenuItem @click="selectSessionType('in_person')">
                                            <div class="flex items-center">
                                                <Users class="mr-2 h-4 w-4" />
                                                <div class="flex flex-col items-start">
                                                    <span class="font-medium">In Person</span>
                                                    <span class="text-sm text-muted-foreground">Face-to-face
                                                        meeting</span>
                                                </div>
                                            </div>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectSessionType('online')">
                                            <div class="flex items-center">
                                                <Video class="mr-2 h-4 w-4" />
                                                <div class="flex flex-col items-start">
                                                    <span class="font-medium">Online</span>
                                                    <span class="text-sm text-muted-foreground">Video call
                                                        session</span>
                                                </div>
                                            </div>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="selectSessionType('hybrid')">
                                            <div class="flex items-center">
                                                <MonitorSpeaker class="mr-2 h-4 w-4" />
                                                <div class="flex flex-col items-start">
                                                    <span class="font-medium">Hybrid</span>
                                                    <span class="text-sm text-muted-foreground">Mix of in-person and
                                                        online</span>
                                                </div>
                                            </div>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                                <input type="hidden" name="session_type" :value="formData.session_type" />
                                <InputError :message="errors.session_type" />
                            </div>

                            <!-- Attendance Toggle (only for past sessions) -->
                            <div v-if="isSessionInPast" class="grid gap-2">
                                <Label>Client Attendance</Label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" :value="true" v-model="formData.client_attended"
                                            class="sr-only" />
                                        <div class="flex items-center space-x-2 px-3 py-2 rounded-md border transition-colors"
                                            :class="formData.client_attended === true ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                                            <div class="w-2 h-2 rounded-full"
                                                :class="formData.client_attended === true ? 'bg-green-500' : 'bg-gray-300'">
                                            </div>
                                            <span class="text-sm font-medium">Attended</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" :value="false" v-model="formData.client_attended"
                                            class="sr-only" />
                                        <div class="flex items-center space-x-2 px-3 py-2 rounded-md border transition-colors"
                                            :class="formData.client_attended === false ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                                            <div class="w-2 h-2 rounded-full"
                                                :class="formData.client_attended === false ? 'bg-red-500' : 'bg-gray-300'">
                                            </div>
                                            <span class="text-sm font-medium">Did Not Attend</span>
                                        </div>
                                    </label>
                                </div>
                                <input type="hidden" name="client_attended" :value="formData.client_attended ? 1 : 0" />
                                <InputError :message="errors.client_attended" />
                            </div>
                        </div>

                        <!-- Computed End Time Display -->
                        <div v-if="computedEndTime" class="p-4 bg-muted rounded-lg mt-2">
                            <div class="flex items-center gap-2">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium">Session will end at:</span>
                                <span class="text-sm text-muted-foreground">{{ computedEndTime }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Coach Information (Read-only) -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Coach Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Coach Name</Label>
                                <p class="mt-1 font-medium">{{ session.coach?.name || 'No coach assigned' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Coach Email</Label>
                                <p class="mt-1">{{ session.coach?.email || 'Not available' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="sessions.show(session.id).url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                        </Link>
                        <Button type="submit" :disabled="processing">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            <SaveIcon class="mr-2 h-4 w-4" />
                            Update Session
                        </Button>
                    </div>
                </Form>
            </div>
        </CoachingSessionLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type BreadcrumbItem, type CoachingSession } from '@/types';
import sessions from '@/routes/tenant/coaching-sessions';

import CoachingSessionController from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingSessionController';
import { LoaderCircle, SaveIcon, ChevronDown, Clock, Users, Video, MonitorSpeaker } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
    session: CoachingSession;
}>();

const formData = ref({
    session_type: props.session.session_type,
    duration: props.session.duration || 60,
    client_attended: props.session.client_attended,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: sessions.index().url
    },
    {
        title: `Session with ${props.session.client?.name}`,
        href: sessions.show(props.session.id).url
    },
    {
        title: 'Edit',
        href: sessions.edit(props.session.id).url
    },
];

const formatDateForInput = (dateString: string | undefined) => {
    if (!dateString) return '';
    return new Date(dateString).toISOString().split('T')[0];
};

const formatTimeForInput = (dateString: string | undefined) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toTimeString().slice(0, 5); // Format as HH:MM
};

const selectedDurationName = computed(() => {
    const duration = formData.value.duration;
    if (duration < 60) {
        return `${duration} minutes`;
    } else if (duration === 60) {
        return '1 hour';
    } else {
        const hours = Math.floor(duration / 60);
        const minutes = duration % 60;
        if (minutes === 0) {
            return `${hours} ${hours === 1 ? 'hour' : 'hours'}`;
        } else {
            return `${hours} hour${hours === 1 ? '' : 's'} ${minutes} minutes`;
        }
    }
});

const selectedSessionTypeName = computed(() => {
    switch (formData.value.session_type) {
        case 'in_person':
            return 'In Person';
        case 'online':
            return 'Online';
        case 'hybrid':
            return 'Hybrid';
        default:
            return 'Select session type';
    }
});

const selectDuration = (duration: number) => {
    formData.value.duration = duration;
};

const selectSessionType = (sessionType: 'in_person' | 'online' | 'hybrid') => {
    formData.value.session_type = sessionType;
};

const formScheduledDate = ref(formatDateForInput(props.session.scheduled_at));
const formScheduledTime = ref(formatTimeForInput(props.session.scheduled_at));

const computedEndTime = computed(() => {
    if (!formScheduledDate.value || !formScheduledTime.value || !formData.value.duration) {
        return null;
    }

    try {
        const startDateTime = new Date(`${formScheduledDate.value}T${formScheduledTime.value}`);
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

const isSessionInPast = computed(() => {
    if (!props.session.scheduled_at) return false;
    return new Date(props.session.scheduled_at) < new Date();
});
</script>
