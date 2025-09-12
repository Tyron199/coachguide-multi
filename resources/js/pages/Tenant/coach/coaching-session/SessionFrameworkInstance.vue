<template>

    <Head :title="`${instance.framework.name} - ${session.client?.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingSessionLayout :session="session">
            <div class="space-y-6">
                <PageHeader :title="instance.framework.name" :description="instance.framework.description"
                    :badge="instance.completed_at ? 'Completed' : 'In Progress'"
                    :badge-variant="instance.completed_at ? 'default' : 'secondary'">
                    <template #actions>
                        <Badge :variant="instance.framework.category === 'models' ? 'default' : 'secondary'">
                            {{ instance.framework.category === 'models' ? 'Model' : 'Tool' }}
                        </Badge>
                    </template>
                </PageHeader>

                <!-- Progress Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Progress</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Completion</span>
                                <span class="font-medium">{{ progressPercentage }}%</span>
                            </div>
                            <div class="w-full bg-secondary rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${progressPercentage}%` }"></div>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ completedFieldsCount }} of {{ totalFieldsCount }} {{ totalFieldsCount === 1 ? 'field'
                                    :
                                    'fields' }} completed
                            </div>
                        </div>

                        <!-- Session Context -->
                        <div class="pt-4 border-t border-border">
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <User class="h-4 w-4" />
                                    <span>{{ session.client?.name }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    <span>Session #{{ session.session_number }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-4 w-4" />
                                    <span>{{ formatDate(session.scheduled_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Dynamic Form -->
                <Form @submit="handleSubmit" class="space-y-6" v-slot="{ errors, processing }">
                    <Card>
                        <CardHeader>
                            <CardTitle>Framework Questions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-6">
                                <div v-for="[fieldKey, fieldSchema] in formFields" :key="fieldKey" class="space-y-2">
                                    <Label :for="fieldKey" class="text-sm font-medium">
                                        {{ fieldSchema.title }}
                                    </Label>

                                    <div class="space-y-1">
                                        <textarea :id="fieldKey" :name="`form_data.${fieldKey}`"
                                            :placeholder="fieldSchema.description" :value="formData[fieldKey] || ''"
                                            @input="updateFieldValue(fieldKey, ($event.target as HTMLTextAreaElement).value)"
                                            @blur="handleFieldBlur"
                                            class="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-y"
                                            :class="{ 'border-destructive': errors[`form_data.${fieldKey}`] }" />

                                        <div v-if="fieldSchema.description" class="text-xs text-muted-foreground">
                                            {{ fieldSchema.description }}
                                        </div>

                                        <div v-if="errors[`form_data.${fieldKey}`]" class="text-xs text-destructive">
                                            {{ errors[`form_data.${fieldKey}`] }}
                                        </div>
                                    </div>

                                    <!-- Field completion indicator -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full transition-colors"
                                                :class="getFieldCompletionClass(fieldKey)">
                                            </div>
                                            <span class="text-xs text-muted-foreground">
                                                {{ getFieldCompletionText(fieldKey) }}
                                            </span>
                                        </div>

                                        <div v-if="formData[fieldKey]" class="text-xs text-muted-foreground">
                                            {{ getFieldCharacterCount(fieldKey) }} characters
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Save Controls -->
                    <Card>
                        <CardContent class="">
                            <div class="flex items-center justify-between">
                                <div v-if="lastSaved" class="text-xs text-muted-foreground">
                                    Saved {{ lastSaved }}
                                </div>
                                <Button type="submit" :disabled="processing || saving">
                                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                    <CheckCircle v-else class="mr-2 h-4 w-4" />
                                    Save & Close
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </Form>
            </div>
        </CoachingSessionLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingSessionLayout from '@/layouts/coaching-session/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    CheckCircle,
    Clock,
    Calendar,
    User,
    LoaderCircle
} from 'lucide-vue-next';
import { type BreadcrumbItem, type CoachingSession } from '@/types';
import sessions from '@/routes/tenant/coach/coaching-sessions';
import frameworkInstanceRoutes from '@/routes/tenant/coach/coaching-framework-instances';

interface FrameworkSchema {
    properties: {
        [key: string]: {
            type: string;
            title: string;
            description: string;
        }
    };
}

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
}

interface FrameworkInstance {
    id: number;
    framework_id: number;
    session_id: number;
    coach_id: number;
    client_id: number;
    schema_snapshot: FrameworkSchema;
    form_data: Record<string, string>;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    framework: Framework;
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    instance: FrameworkInstance;
    framework: Framework;
    session: CoachingSession;
    client: User;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

// Form state
const formData = ref<Record<string, string>>({ ...props.instance.form_data });
const saving = ref<boolean>(false);
const lastSaved = ref<string>('');

// Computed properties
const formFields = computed(() => {
    if (!props.instance.schema_snapshot?.properties) return [];
    return Object.entries(props.instance.schema_snapshot.properties);
});

const totalFieldsCount = computed(() => {
    return formFields.value.length;
});

const completedFieldsCount = computed(() => {
    return Object.values(formData.value).filter(value => value && value.trim() !== '').length;
});

const progressPercentage = computed(() => {
    if (totalFieldsCount.value === 0) return 0;
    return Math.round((completedFieldsCount.value / totalFieldsCount.value) * 100);
});

const isFullyCompleted = computed(() => {
    return progressPercentage.value === 100;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.session.is_past ? 'Past Sessions' : 'Sessions',
        href: props.session.is_past ? sessions.past().url : sessions.index().url,
    },
    {
        title: `Session #${props.session.session_number} with ${props.session.client?.name}`,
        href: sessions.show(props.session.id).url,
    },
    {
        title: 'Frameworks',
        href: sessions.frameworks(props.session.id).url,
    },
    {
        title: props.instance.framework.name,
        href: frameworkInstanceRoutes.show(props.instance.id).url,
    }
];

// Helper functions for field display
function getFieldCompletionClass(fieldKey: string): string {
    const isCompleted = formData.value[fieldKey] && formData.value[fieldKey].trim() !== '';
    return isCompleted ? 'bg-primary' : 'bg-muted';
}

function getFieldCompletionText(fieldKey: string): string {
    const isCompleted = formData.value[fieldKey] && formData.value[fieldKey].trim() !== '';
    return isCompleted ? 'Completed' : 'Not completed';
}

function getFieldCharacterCount(fieldKey: string): number {
    return formData.value[fieldKey] ? formData.value[fieldKey].length : 0;
}

// Methods
function updateFieldValue(fieldKey: string, value: string): void {
    formData.value[fieldKey] = value;
}

async function handleFieldBlur(_e?: FocusEvent): Promise<void> {
    // Auto-save on field blur
    await autoSave();
}

async function autoSave(): Promise<void> {
    if (saving.value) return;

    saving.value = true;
    try {
        await router.patch(
            frameworkInstanceRoutes.update(props.instance.id).url,
            {
                form_data: formData.value,
                completed: isFullyCompleted.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    updateLastSaved();
                },
                onError: (errors) => {
                    console.error('Auto-save failed:', errors);
                }
            }
        );
    } finally {
        saving.value = false;
    }
}


function handleSubmit(): void {
    router.patch(
        frameworkInstanceRoutes.update(props.instance.id).url,
        {
            form_data: formData.value,
            completed: isFullyCompleted.value,
            redirect_to_list: true, // Flag to indicate we want to redirect
        }
    );
}

function updateLastSaved(): void {
    const now = new Date();
    lastSaved.value = now.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

// Initialize last saved time
onMounted(() => {
    if (props.instance.updated_at) {
        const updatedAt = new Date(props.instance.updated_at);
        lastSaved.value = updatedAt.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
});
</script>
