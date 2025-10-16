<template>

    <Head title="Add Supervision Log Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Add Supervision Log Entry"
                    description="Record your supervision session and insights" />

                <div class="max-w-4xl">
                    <Form :action="SupervisionController.store()" class="space-y-8" enctype="multipart/form-data"
                        v-slot="{ errors, processing }">
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
                                        class="mt-1 block w-full" required />
                                    <InputError :message="errors.supervision_date" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="duration_minutes">Duration (minutes)</Label>
                                    <Input id="duration_minutes" name="duration_minutes" type="number" min="1" max="999"
                                        class="mt-1 block w-full" placeholder="e.g., 60" required />
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
                                        class="mt-1 block w-full" placeholder="Enter supervisor's name" required />
                                    <InputError :message="errors.supervisor_name" />
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="supervisor_contact">Contact Information</Label>
                                        <Input id="supervisor_contact" name="supervisor_contact" type="text"
                                            class="mt-1 block w-full" placeholder="Email or phone number" />
                                        <InputError :message="errors.supervisor_contact" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="supervisor_accreditation">Accreditation Level</Label>
                                        <Input id="supervisor_accreditation" name="supervisor_accreditation" type="text"
                                            class="mt-1 block w-full" placeholder="e.g., PCC, MCC" />
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
                                        required />
                                    <InputError :message="errors.themes_discussed" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="reflections">Reflections or Insights Gained</Label>
                                    <Textarea id="reflections" name="reflections"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="What insights or learnings did you gain from this session?" />
                                    <InputError :message="errors.reflections" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="action_points">Action Points / Development Goals</Label>
                                    <Textarea id="action_points" name="action_points"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="What actions will you take based on this supervision?" />
                                    <InputError :message="errors.action_points" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="ethical_considerations">Ethical Considerations Raised</Label>
                                    <Textarea id="ethical_considerations" name="ethical_considerations"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="Any ethical issues, dilemmas, or considerations discussed?" />
                                    <InputError :message="errors.ethical_considerations" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="impact_on_practice">Impact on Coaching Practice</Label>
                                    <Textarea id="impact_on_practice" name="impact_on_practice"
                                        class="mt-1 block w-full min-h-[100px]"
                                        placeholder="How will this supervision impact your coaching practice?" />
                                    <InputError :message="errors.impact_on_practice" />
                                </div>
                            </div>
                        </div>

                        <!-- Supporting Evidence -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Supporting Evidence</h2>
                                <p class="text-sm text-muted-foreground">Upload documents, audio, notes, or other
                                    supporting materials.</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="attachments">Upload Files</Label>
                                <Input id="attachments" name="attachments[]" type="file" multiple
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
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Add Entry
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>
        </GrowthTrackerLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
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
import { LoaderCircle, Plus } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

// State for Select components
const supervisionType = ref('');
const sessionFormat = ref('');

const breadcrumbs: BreadcrumbItem[] = [

    {
        title: 'Supervision Log',
        href: SupervisionController.index().url
    },
    {
        title: 'Add Entry',
        href: SupervisionController.create().url
    },
];
</script>
