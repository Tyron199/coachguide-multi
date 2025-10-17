<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { BreadcrumbItem } from '@/types';
import { dashboard } from "@/routes/tenant";
import { index as tasksIndex } from '@/actions/App/Http/Controllers/Tenant/Client/TaskController';
import { store as submitTask, destroy as deleteSubmission } from '@/actions/App/Http/Controllers/Tenant/Client/TaskActionController';
import { show as sessionShow } from '@/actions/App/Http/Controllers/Tenant/Client/SessionController';
import {
    CheckSquare, Clock, User, Calendar,
    Upload, X, FileIcon, Download, Trash2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { alertConfirm } from '@/plugins/alert';

interface TaskAction {
    id: number;
    content: string;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
    attachments: Array<{
        id: number;
        filename: string;
        size: number;
        mime_type: string;
        download_url: string;
    }>;
}

interface Task {
    id: number;
    title: string;
    description: string;
    deadline: string | null;
    status: string;
    evidence_required: boolean;
    send_reminders: boolean;
    created_at: string;
    completed_at: string | null;
    coach: { id: number; name: string } | null;
    session: { id: number; session_number: number; scheduled_at: string } | null;
    reminders: Array<{
        id: number;
        remind_at: string;
        status: string;
        label: string;
    }>;
    actions: TaskAction[];
    is_overdue: boolean;
}

interface Props {
    task: Task;
}

const props = defineProps<Props>();

const page = usePage();
const currentUserId = computed(() => page.props.auth.user.id);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Tasks',
        href: tasksIndex().url,
    },
    {
        title: props.task.title,
        href: '#',
    },
];

const form = useForm({
    content: '',
    attachments: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = [...selectedFiles.value, ...Array.from(target.files)];
        form.attachments = selectedFiles.value;
    }
};

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
    form.attachments = selectedFiles.value;
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const submitEvidence = () => {
    form.post(submitTask(props.task.id).url, {
        preserveScroll: true,
    });
};

const canSubmit = computed(() => {
    if (props.task.evidence_required) {
        return form.content.trim().length > 0 && selectedFiles.value.length > 0;
    }
    return true;
});

const isTaskCompleted = computed(() => {
    return ['completed', 'cancelled'].includes(props.task.status);
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending': return 'outline' as const;
        case 'in_progress': return 'secondary' as const;
        case 'review': return 'default' as const;
        case 'completed': return 'default' as const;
        default: return 'outline' as const;
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'in_progress': return 'In Progress';
        case 'review': return 'Under Review';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
};

const handleDeleteSubmission = async (actionId: number) => {
    const confirmed = await alertConfirm({
        title: 'Delete Submission?',
        description: 'Are you sure you want to delete this submission? All attached files will be removed. This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        router.delete(deleteSubmission({ task: props.task.id, action: actionId }).url, {
            preserveScroll: true,
        });
    }
};
</script>

<template>

    <Head :title="task.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 max-w-4xl mx-auto">
            <!-- Task Details Card -->
            <Card>
                <CardHeader>
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <CardTitle class="text-2xl">{{ task.title }}</CardTitle>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge :variant="getStatusVariant(task.status)">
                                {{ getStatusLabel(task.status) }}
                            </Badge>
                            <Badge v-if="task.evidence_required" variant="outline">
                                Evidence Required
                            </Badge>
                            <Badge v-if="task.is_overdue && task.status !== 'completed'" variant="destructive">
                                Overdue
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Description -->
                    <div>
                        <h3 class="font-semibold mb-2">Description</h3>
                        <p class="text-muted-foreground whitespace-pre-wrap">{{ task.description }}</p>
                    </div>

                    <Separator />

                    <!-- Task Metadata -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div v-if="task.deadline" class="flex items-start gap-2">
                            <Clock class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Deadline</p>
                                <p class="text-sm text-muted-foreground"
                                    :class="{ 'text-destructive font-medium': task.is_overdue }">
                                    {{ formatDate(task.deadline) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="task.coach" class="flex items-start gap-2">
                            <User class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Assigned By</p>
                                <p class="text-sm text-muted-foreground">{{ task.coach.name }}</p>
                            </div>
                        </div>

                        <div v-if="task.session" class="flex items-start gap-2">
                            <Calendar class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Related Session</p>
                                <Link :href="sessionShow(task.session.id).url"
                                    class="text-sm text-primary hover:underline">
                                Session #{{ task.session.session_number }}
                                </Link>
                            </div>
                        </div>

                        <div v-if="task.completed_at" class="flex items-start gap-2">
                            <CheckSquare class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Completed</p>
                                <p class="text-sm text-muted-foreground">{{ formatDate(task.completed_at) }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Previous Submissions -->
            <Card v-if="task.actions.length > 0">
                <CardHeader>
                    <CardTitle>Submissions</CardTitle>
                    <CardDescription>Your previous submissions for this task</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-for="action in task.actions" :key="action.id" class="border rounded-lg p-4 space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium">{{ action.user.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(action.created_at) }}
                                </p>
                            </div>
                            <Button v-if="action.user.id === currentUserId && !isTaskCompleted" variant="ghost"
                                size="sm" @click="handleDeleteSubmission(action.id)"
                                class="text-destructive hover:text-destructive hover:bg-destructive/10">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <p class="text-sm whitespace-pre-wrap">{{ action.content }}</p>

                        <!-- Attachments -->
                        <div v-if="action.attachments.length > 0" class="space-y-2">
                            <p class="text-sm font-medium">Attachments:</p>
                            <div class="space-y-2">
                                <a v-for="attachment in action.attachments" :key="attachment.id"
                                    :href="attachment.download_url" target="_blank"
                                    class="flex items-center gap-2 text-sm p-2 rounded border hover:bg-accent transition-colors">
                                    <FileIcon class="h-4 w-4 flex-shrink-0" />
                                    <span class="flex-1 truncate">{{ attachment.filename }}</span>
                                    <span class="text-muted-foreground">{{ formatFileSize(attachment.size) }}</span>
                                    <Download class="h-4 w-4 flex-shrink-0" />
                                </a>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Submission Form -->
            <Card v-if="!isTaskCompleted">
                <CardHeader>
                    <CardTitle>
                        {{ task.evidence_required ? 'Submit Evidence' : 'Complete Task' }}
                    </CardTitle>
                    <CardDescription>
                        {{ task.evidence_required
                            ? 'Upload files and add a comment about your submission'
                            : 'Add an optional comment and mark this task as complete'
                        }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitEvidence" class="space-y-6">
                        <!-- Comment Field -->
                        <div class="space-y-2">
                            <Label for="content">
                                Comment {{ task.evidence_required ? '*' : '(Optional)' }}
                            </Label>
                            <Textarea id="content" v-model="form.content" :placeholder="task.evidence_required
                                ? 'Describe what you\'ve done and any relevant details...'
                                : 'Add any notes about completing this task...'" rows="4"
                                :class="{ 'border-destructive': form.errors.content }" />
                            <p v-if="form.errors.content" class="text-sm text-destructive">
                                {{ form.errors.content }}
                            </p>
                        </div>

                        <!-- File Upload -->
                        <div class="space-y-2">
                            <Label>
                                Attachments {{ task.evidence_required ? '*' : '(Optional)' }}
                            </Label>

                            <!-- File Input Button -->
                            <div class="flex flex-col gap-3">
                                <Button type="button" variant="outline" @click="fileInput?.click()"
                                    class="w-full sm:w-auto">
                                    <Upload class="mr-2 h-4 w-4" />
                                    Choose Files
                                </Button>
                                <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileSelect"
                                    accept="*/*" />
                                <p class="text-sm text-muted-foreground">
                                    Max file size: 10MB per file
                                </p>
                            </div>

                            <!-- Selected Files List -->
                            <div v-if="selectedFiles.length > 0" class="space-y-2 mt-3">
                                <div v-for="(file, index) in selectedFiles" :key="index"
                                    class="flex items-center gap-2 p-2 rounded border bg-muted/50">
                                    <FileIcon class="h-4 w-4 flex-shrink-0" />
                                    <span class="flex-1 truncate text-sm">{{ file.name }}</span>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatFileSize(file.size) }}
                                    </span>
                                    <Button type="button" variant="ghost" size="sm" @click="removeFile(index)"
                                        class="h-6 w-6 p-0">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <p v-if="form.errors.attachments" class="text-sm text-destructive">
                                {{ form.errors.attachments }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-3">
                            <Button type="submit" :disabled="!canSubmit || form.processing" class="w-full sm:w-auto">
                                <CheckSquare class="mr-2 h-4 w-4" />
                                {{ task.evidence_required ? 'Submit Evidence' : 'Mark as Complete' }}
                            </Button>

                            <Link :href="tasksIndex().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Completed Message -->
            <Card v-else class="border-primary/50 bg-primary/5">
                <CardContent>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                            <CheckSquare class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <p class="font-semibold">Task {{ task.status === 'completed' ? 'Completed' : 'Cancelled' }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                This task has been {{ task.status }}.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
