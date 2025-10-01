<template>

    <Head title="Edit Supervision Log Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Edit Supervision Log Entry" description="Update your supervision session record" />

                <div class="max-w-4xl">
                    <Form :action="SupervisionController.update(supervision.id)" class="space-y-8"
                        enctype="multipart/form-data" v-slot="{ errors, processing }">
                        <!-- Session Details -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Session Details</h2>
                                <p class="text-sm text-muted-foreground">Basic information about the supervision
                                    session.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="supervision_date">Date of Supervision</Label>
                                    <Input id="supervision_date" name="supervision_date" type="date"
                                        class="mt-1 block w-full" :model-value="supervision.supervision_date"
                                        required />
                                    <InputError :message="errors.supervision_date" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="duration_minutes">Duration (minutes)</Label>
                                    <Input id="duration_minutes" name="duration_minutes" type="number" min="1" max="999"
                                        class="mt-1 block w-full" placeholder="e.g., 60"
                                        :model-value="supervision.duration_minutes" required />
                                    <InputError :message="errors.duration_minutes" />
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="supervision_type">Type of Supervision</Label>
                                    <Select v-model="supervisionType">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select supervision type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Developmental">Developmental</SelectItem>
                                            <SelectItem value="Normative">Normative</SelectItem>
                                            <SelectItem value="Restorative">Restorative</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="supervision_type" :value="supervisionType" required />
                                    <InputError :message="errors.supervision_type" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="session_format">Session Format</Label>
                                    <Select v-model="sessionFormat">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select session format" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Face-to-Face">Face-to-Face</SelectItem>
                                            <SelectItem value="Online">Online</SelectItem>
                                            <SelectItem value="Peer">Peer</SelectItem>
                                            <SelectItem value="Group">Group</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="session_format" :value="sessionFormat" required />
                                    <InputError :message="errors.session_format" />
                                </div>
                            </div>
                        </div>

                        <!-- Supervisor Details -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Supervisor Details</h2>
                                <p class="text-sm text-muted-foreground">Information about your supervisor.</p>
                            </div>

                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label for="supervisor_name">Supervisor Name</Label>
                                    <Input id="supervisor_name" name="supervisor_name" type="text"
                                        class="mt-1 block w-full" placeholder="Enter supervisor's name"
                                        :model-value="supervision.supervisor_name" required />
                                    <InputError :message="errors.supervisor_name" />
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="supervisor_contact">Contact Information</Label>
                                        <Input id="supervisor_contact" name="supervisor_contact" type="text"
                                            class="mt-1 block w-full" placeholder="Email or phone number"
                                            :model-value="supervision.supervisor_contact ?? ''" />
                                        <InputError :message="errors.supervisor_contact" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="supervisor_accreditation">Accreditation Level</Label>
                                        <Input id="supervisor_accreditation" name="supervisor_accreditation" type="text"
                                            class="mt-1 block w-full" placeholder="e.g., PCC, MCC"
                                            :model-value="supervision.supervisor_accreditation ?? ''" />
                                        <InputError :message="errors.supervisor_accreditation" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Content -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Session Content</h2>
                                <p class="text-sm text-muted-foreground">What was discussed and learned during the
                                    session.</p>
                            </div>

                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label for="themes_discussed">Main Coaching Themes or Cases Discussed</Label>
                                    <Textarea id="themes_discussed" name="themes_discussed"
                                        class="mt-1 block w-full min-h-[120px]"
                                        placeholder="Describe the main themes, cases, or topics discussed..."
                                        :model-value="supervision.themes_discussed" required />
                                    <InputError :message="errors.themes_discussed" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="reflections">Reflections or Insights Gained</Label>
                                    <Textarea id="reflections" name="reflections"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="What insights or learnings did you gain from this session?"
                                        :model-value="supervision.reflections ?? ''" />
                                    <InputError :message="errors.reflections" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="action_points">Action Points / Development Goals</Label>
                                    <Textarea id="action_points" name="action_points"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="What actions will you take based on this supervision?"
                                        :model-value="supervision.action_points ?? ''" />
                                    <InputError :message="errors.action_points" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="ethical_considerations">Ethical Considerations Raised</Label>
                                    <Textarea id="ethical_considerations" name="ethical_considerations"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="Any ethical issues, dilemmas, or considerations discussed?"
                                        :model-value="supervision.ethical_considerations ?? ''" />
                                    <InputError :message="errors.ethical_considerations" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="impact_on_practice">Impact on Coaching Practice</Label>
                                    <Textarea id="impact_on_practice" name="impact_on_practice"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="How will this supervision impact your coaching practice?"
                                        :model-value="supervision.impact_on_practice ?? ''" />
                                    <InputError :message="errors.impact_on_practice" />
                                </div>
                            </div>
                        </div>

                        <!-- Existing Attachments -->
                        <div v-if="supervision.attachments && supervision.attachments.length > 0" class="space-y-4">
                            <div>
                                <h2 class="text-lg font-medium">Current Attachments</h2>
                                <p class="text-sm text-muted-foreground">Files attached to this supervision log.</p>
                            </div>

                            <div class="grid gap-3">
                                <div v-for="attachment in supervision.attachments" :key="attachment.id"
                                    class="flex items-center justify-between p-3 border rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <div class="text-sm font-medium">{{ attachment.original_name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ attachment.formatted_size }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a :href="attachment.url" target="_blank">
                                            <Button type="button" variant="outline" size="sm">
                                                <Download class="h-4 w-4 mr-2" />
                                                Download
                                            </Button>
                                        </a>
                                        <Button type="button" variant="destructive" size="sm"
                                            @click="handleDeleteAttachment(attachment.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add New Attachments -->
                        <div class="space-y-4">
                            <div>
                                <h2 class="text-lg font-medium">Add New Files</h2>
                                <p class="text-sm text-muted-foreground">Upload additional supporting evidence.</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_attachments">Upload Files</Label>
                                <Input id="new_attachments" name="attachments[]" type="file" multiple
                                    class="mt-1 block w-full" />
                                <p class="text-xs text-muted-foreground">You can upload multiple files (PDFs, documents,
                                    images, audio files)</p>
                                <InputError :message="errors.attachments" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="SupervisionController.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            </Link>
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <SaveIcon v-else class="mr-2 h-4 w-4" />
                                Update Entry
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </GrowthTrackerLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import SupervisionController from '@/actions/App/Http/Controllers/Tenant/Coach/SupervisionController';
import AttachmentController from '@/actions/App/Http/Controllers/Tenant/AttachmentController';
import { LoaderCircle, SaveIcon, FileText, Download, Trash2 } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { alertConfirm } from '@/plugins/alert';

interface SupervisionAttachment {
    id: number;
    original_name: string;
    formatted_size: string;
    is_image: boolean;
    url: string;
}

interface Supervision {
    id: number;
    supervision_date: string;
    duration_minutes: number;
    supervisor_name: string;
    supervisor_contact: string | null;
    supervisor_accreditation: string | null;
    supervision_type: string;
    session_format: string;
    themes_discussed: string;
    reflections: string | null;
    action_points: string | null;
    ethical_considerations: string | null;
    impact_on_practice: string | null;
    attachments: SupervisionAttachment[];
}

interface Props {
    supervision: Supervision;
}

const props = defineProps<Props>();

// State for Select components - initialize with existing values
const supervisionType = ref(props.supervision.supervision_type);
const sessionFormat = ref(props.supervision.session_format);

const handleDeleteAttachment = async (attachmentId: number) => {
    const confirmed = await alertConfirm({
        title: 'Delete File',
        description: 'Are you sure you want to delete this file? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(AttachmentController.destroy(attachmentId).url, {
            preserveScroll: true,
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Growth Tracker',
        href: SupervisionController.index().url
    },
    {
        title: 'Supervision Log',
        href: SupervisionController.index().url
    },
    {
        title: 'Edit Entry',
        href: SupervisionController.edit(props.supervision.id).url
    },
];
</script>
