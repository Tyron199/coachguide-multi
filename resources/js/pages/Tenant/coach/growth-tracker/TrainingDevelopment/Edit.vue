<template>

    <Head title="Edit Training & Development" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Edit Training & Development Entry"
                    description="Update your professional development activity" />

                <div class="max-w-4xl">
                    <Form :action="ProfessionalDevelopmentController.update(development.id)" class="space-y-8"
                        v-slot="{ errors, processing }">
                        <!-- Training Details -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Training Details</h2>
                                <p class="text-sm text-muted-foreground">Basic information about the training or
                                    development activity.</p>
                            </div>

                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label for="course_title">Course Title</Label>
                                    <Input id="course_title" name="course_title" type="text" class="mt-1 block w-full"
                                        placeholder="Enter the name of the course or training"
                                        :model-value="development.course_title" required />
                                    <InputError :message="errors.course_title" />
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="training_provider">Training Provider</Label>
                                        <Input id="training_provider" name="training_provider" type="text"
                                            class="mt-1 block w-full" placeholder="e.g., University, ICF, Organization"
                                            :model-value="development.training_provider" required />
                                        <InputError :message="errors.training_provider" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="training_type">Training Type</Label>
                                        <Select v-model="trainingType">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Select training type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Formal Training">Formal Training</SelectItem>
                                                <SelectItem value="Webinar / workshop">Webinar / workshop</SelectItem>
                                                <SelectItem value="Books/podcasts">Books/podcasts</SelectItem>
                                                <SelectItem value="Self-study">Self-study</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <input type="hidden" name="training_type" :value="trainingType" required />
                                        <InputError :message="errors.training_type" />
                                    </div>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="date_from">Start Date</Label>
                                        <Input id="date_from" name="date_from" type="date" class="mt-1 block w-full"
                                            :model-value="development.date_from" required />
                                        <InputError :message="errors.date_from" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="date_to">End Date</Label>
                                        <Input id="date_to" name="date_to" type="date" class="mt-1 block w-full"
                                            :model-value="development.date_to" required />
                                        <InputError :message="errors.date_to" />
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Checkbox id="accredited" :model-value="accredited"
                                        @update:model-value="accredited = Boolean($event)" />
                                    <Label for="accredited" class="text-sm font-medium">
                                        This training is accredited
                                    </Label>
                                    <input type="hidden" name="accredited" :value="accredited ? '1' : '0'" />
                                </div>
                            </div>
                        </div>

                        <!-- Hours Information -->
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium">Training Hours</h2>
                                <p class="text-sm text-muted-foreground">Track the hours spent on theory and practical
                                    components.</p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="total_hours_theory">Total Theory Hours</Label>
                                    <Input id="total_hours_theory" name="total_hours_theory" type="number" step="0.1"
                                        min="0" max="99999.99" class="mt-1 block w-full" placeholder="e.g., 10.5"
                                        :model-value="development.total_hours_theory ?? undefined" />
                                    <p class="text-xs text-muted-foreground">Hours spent on theoretical learning</p>
                                    <InputError :message="errors.total_hours_theory" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="total_hours_practical">Total Practical Hours</Label>
                                    <Input id="total_hours_practical" name="total_hours_practical" type="number"
                                        step="0.1" min="0" max="99999.99" class="mt-1 block w-full"
                                        placeholder="e.g., 5.5"
                                        :model-value="development.total_hours_practical ?? undefined" />
                                    <p class="text-xs text-muted-foreground">Hours spent on practical application</p>
                                    <InputError :message="errors.total_hours_practical" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="ProfessionalDevelopmentController.index().url">
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
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import ProfessionalDevelopmentController from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalDevelopmentController';
import { LoaderCircle, SaveIcon } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

interface ProfessionalDevelopment {
    id: number;
    date_from: string;
    date_to: string;
    training_type: string;
    training_provider: string;
    course_title: string;
    accredited: boolean;
    total_hours_theory: number | null;
    total_hours_practical: number | null;
}

interface Props {
    development: ProfessionalDevelopment;
}

const props = defineProps<Props>();

// State for Select component - initialize with existing value
const trainingType = ref(props.development.training_type);

// State for Checkbox component - initialize with existing value
const accredited = ref(props.development.accredited);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Training & Development',
        href: ProfessionalDevelopmentController.index().url
    },
    {
        title: 'Edit Entry',
        href: ProfessionalDevelopmentController.edit(props.development.id).url
    },
];
</script>
