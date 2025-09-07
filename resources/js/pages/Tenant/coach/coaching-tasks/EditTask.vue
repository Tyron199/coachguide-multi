<template>

    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <component :is="layoutComponent" v-bind="layoutProps">
            <div class="space-y-6">
                <PageHeader title="Edit Task" :description="pageDescription" />

                <div class="rounded-lg border bg-card p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Task Information -->
                        <div class="space-y-4">
                            <div>
                                <Label for="title" class="text-sm font-medium">Task Title</Label>
                                <Input id="title" v-model="form.title" type="text" class="mt-1"
                                    placeholder="Enter task title..."
                                    :class="{ 'border-destructive': form.errors.title }" required />
                                <InputError :message="form.errors.title" class="mt-1" />
                            </div>

                            <div v-if="task.session">
                                <Label class="text-sm font-medium">Associated Session</Label>
                                <div class="mt-1 p-3 bg-muted rounded-md">
                                    <div class="flex items-center gap-2 text-sm">
                                        <Calendar class="h-4 w-4" />
                                        <span class="font-medium">Session {{ task.session.session_number }}</span>
                                        <span class="text-muted-foreground">-</span>
                                        <span>{{ formatDate(task.session.scheduled_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Label for="description" class="text-sm font-medium">Task Description</Label>
                                <Textarea id="description" v-model="form.description" class="mt-1 min-h-[120px]"
                                    placeholder="Describe what the client needs to do..."
                                    :class="{ 'border-destructive': form.errors.description }" required />
                                <InputError :message="form.errors.description" class="mt-1" />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="evidence_required" :model-value="form.evidence_required"
                                    @update:model-value="form.evidence_required = Boolean($event)" />
                                <Label for="evidence_required" class="text-sm font-medium">
                                    Require evidence submission to complete task
                                </Label>
                            </div>

                            <!-- Task Status -->
                            <div>
                                <Label for="status" class="text-sm font-medium">Status</Label>
                                <Select :model-value="form.status"
                                    @update:model-value="(value: any) => form.status = value" class="mt-1">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="review">In Review</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" class="mt-1" />
                            </div>
                        </div>

                        <!-- Deadline and Reminders -->
                        <div class="space-y-4 pt-4 border-t">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="has_deadline" :model-value="hasDeadline"
                                    @update:model-value="hasDeadline = Boolean($event)" />
                                <Label for="has_deadline" class="text-sm font-medium">Set deadline</Label>
                            </div>

                            <div v-if="hasDeadline" class="space-y-4 ml-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label for="deadline_date" class="text-sm font-medium">Date</Label>
                                        <Input id="deadline_date" v-model="deadlineDate" type="date" class="mt-1"
                                            :class="{ 'border-destructive': form.errors.deadline }" />
                                    </div>
                                    <div>
                                        <Label for="deadline_time" class="text-sm font-medium">Time</Label>
                                        <Input id="deadline_time" v-model="deadlineTime" type="time" class="mt-1"
                                            :class="{ 'border-destructive': form.errors.deadline }" />
                                    </div>
                                </div>
                                <InputError :message="form.errors.deadline" class="mt-1" />

                                <div class="space-y-3">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="send_reminders" :model-value="form.send_reminders"
                                            @update:model-value="form.send_reminders = Boolean($event)" />
                                        <Label for="send_reminders" class="text-sm font-medium">Send email
                                            reminders</Label>
                                    </div>

                                    <div v-if="form.send_reminders" class="ml-6 space-y-2">
                                        <Label class="text-sm font-medium">Reminder Schedule</Label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="reminder_1h" :model-value="reminderSelections['1h']"
                                                    @update:model-value="reminderSelections['1h'] = Boolean($event)" />
                                                <Label for="reminder_1h" class="text-sm">1 hour before</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="reminder_1d" :model-value="reminderSelections['1d']"
                                                    @update:model-value="reminderSelections['1d'] = Boolean($event)" />
                                                <Label for="reminder_1d" class="text-sm">1 day before</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="reminder_2d" :model-value="reminderSelections['2d']"
                                                    @update:model-value="reminderSelections['2d'] = Boolean($event)" />
                                                <Label for="reminder_2d" class="text-sm">2 days before</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="reminder_1w" :model-value="reminderSelections['1w']"
                                                    @update:model-value="reminderSelections['1w'] = Boolean($event)" />
                                                <Label for="reminder_1w" class="text-sm">1 week before</Label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Button type="button" variant="outline" @click="cancel">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Update Task
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </component>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem, type CoachingTask } from '@/types';
import clients from '@/routes/tenant/clients';
import sessions from '@/routes/tenant/coaching-sessions';
import taskRoutes from '@/routes/tenant/coaching-tasks';
import { Calendar, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    task: CoachingTask;
    client: any;
}>();

const form = useForm({
    title: props.task.title,
    description: props.task.description,
    deadline: '',
    evidence_required: props.task.evidence_required,
    send_reminders: props.task.send_reminders,
    reminders: [] as string[],
    status: props.task.status,
});

// Deadline handling
const hasDeadline = ref(!!props.task.deadline);
const deadlineDate = ref('');
const deadlineTime = ref('');

// Reminder selections
const reminderSelections = ref({
    '1h': false,
    '1d': false,
    '2d': false,
    '1w': false,
});

// Initialize form data from existing task
onMounted(() => {
    if (props.task.deadline) {
        const deadline = new Date(props.task.deadline);
        deadlineDate.value = deadline.toISOString().split('T')[0];
        deadlineTime.value = deadline.toTimeString().slice(0, 5);
        form.deadline = props.task.deadline;
    }

    // Initialize reminder selections from existing reminders
    if (props.task.reminders) {
        const existingReminders = new Set();
        props.task.reminders.forEach(reminder => {
            if (reminder.label.includes('1 hour')) existingReminders.add('1h');
            if (reminder.label.includes('1 day')) existingReminders.add('1d');
            if (reminder.label.includes('2 days')) existingReminders.add('2d');
            if (reminder.label.includes('1 week')) existingReminders.add('1w');
        });

        reminderSelections.value = {
            '1h': existingReminders.has('1h'),
            '1d': existingReminders.has('1d'),
            '2d': existingReminders.has('2d'),
            '1w': existingReminders.has('1w'),
        };
    }
});

// Watch deadline changes to update form
watch([deadlineDate, deadlineTime], () => {
    if (hasDeadline.value && deadlineDate.value) {
        const time = deadlineTime.value || '09:00';
        form.deadline = `${deadlineDate.value} ${time}`;
    } else {
        form.deadline = '';
    }
});

// Watch reminder selections to update form
watch(reminderSelections, (newSelections) => {
    form.reminders = Object.keys(newSelections).filter(key => newSelections[key as keyof typeof newSelections]);
}, { deep: true });

// Watch hasDeadline to reset form fields
watch(hasDeadline, (newValue) => {
    if (!newValue) {
        form.deadline = '';
        form.send_reminders = false;
        deadlineDate.value = '';
        deadlineTime.value = '';
        // Reset reminder selections
        Object.keys(reminderSelections.value).forEach(key => {
            reminderSelections.value[key as keyof typeof reminderSelections.value] = false;
        });
    }
});

// Dynamic layout selection based on context
const layoutComponent = computed(() => {
    return props.task.session ? CoachingSessionLayout : ClientLayout;
});

const layoutProps = computed(() => {
    if (props.task.session) {
        return { session: props.task.session };
    } else {
        return { client: props.client };
    }
});

const pageDescription = computed(() => {
    return props.task.session
        ? 'Update this session task'
        : 'Update this client task';
});

const pageTitle = computed(() => {
    return props.task.session
        ? `Edit Task - Session ${props.task.session.session_number}`
        : `Edit Task - ${props.client.name}`;
});

const breadcrumbs = computed((): BreadcrumbItem[] => {
    if (props.task.session) {
        // Session context breadcrumbs
        return [
            {
                title: 'Sessions',
                href: sessions.index().url,
            },
            {
                title: `Session ${props.task.session.session_number}`,
                href: sessions.show(props.task.session.id).url,
            },
            {
                title: 'Tasks',
                href: sessions.tasks(props.task.session.id).url,
            },
            {
                title: 'Edit Task',
                href: '#',
            }
        ];
    } else {
        // Client context breadcrumbs
        const clientBreadcrumbs: BreadcrumbItem[] = [
            {
                title: 'Clients',
                href: clients.index().url,
            },
            {
                title: props.client.name,
                href: clients.show(props.client.id).url,
            },
            {
                title: 'Tasks',
                href: clients.tasks(props.client.id).url,
            },
            {
                title: 'Edit Task',
                href: '#',
            }
        ];

        // If client is archived, insert archived clients breadcrumb
        if (props.client.archived) {
            clientBreadcrumbs.splice(1, 0, {
                title: 'Archived Clients',
                href: clients.archived().url,
            });
        }

        return clientBreadcrumbs;
    }
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};



const submit = () => {
    form.put(taskRoutes.update(props.task.id).url, {
        onSuccess: () => {
            // Will redirect based on context in controller
        },
    });
};

const cancel = () => {
    if (props.task.session) {
        router.visit(sessions.tasks(props.task.session.id).url);
    } else {
        router.visit(clients.tasks(props.client.id).url);
    }
};
</script>
