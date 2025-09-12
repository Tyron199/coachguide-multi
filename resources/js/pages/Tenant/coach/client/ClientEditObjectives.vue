<template>

    <Head :title="`Edit ${client.name} - Objectives`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ClientLayout :client="client">
            <div class="space-y-6">
                <PageHeader title="Edit Client Objectives"
                    description="Update coaching goals, objectives, and focus areas" />

                <Form :action="ClientController.updateObjectives(client.id)" class="space-y-6"
                    v-slot="{ errors, processing }">
                    <!-- Goal Summary Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Goal Summary</h2>
                        <div class="grid gap-2">
                            <Label for="goal_summary">Overall Coaching Goals</Label>
                            <Textarea id="goal_summary" name="goal_summary"
                                :model-value="client.profile?.goal_summary || ''"
                                placeholder="Describe the client's overall coaching goals and what they want to achieve..."
                                rows="4" />
                            <InputError :message="errors.goal_summary" />
                        </div>
                    </div>

                    <!-- Objectives Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Specific Objectives</h2>
                        <div class="grid gap-2">
                            <Label for="objectives">Coaching Objectives</Label>
                            <Textarea id="objectives" name="objectives" :model-value="client.profile?.objectives || ''"
                                placeholder="Describe the specific objectives for coaching sessions..." rows="6" />
                            <InputError :message="errors.objectives" />
                        </div>
                    </div>

                    <!-- Focus Areas Section -->
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium mb-4">Coaching Focus Areas</h2>
                        <div class="grid gap-2">
                            <Label>Select areas to focus on during coaching sessions</Label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div v-for="area in focusAreaOptions" :key="area" class="flex items-center space-x-2">
                                    <input type="checkbox" :id="`focus-${area.replace(/\s+/g, '-').toLowerCase()}`"
                                        :value="area" :checked="isAreaSelected(area)"
                                        @change="(e) => toggleFocusArea(area, (e.target as HTMLInputElement).checked)"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <Label :for="`focus-${area.replace(/\s+/g, '-').toLowerCase()}`"
                                        class="text-sm font-normal cursor-pointer">
                                        {{ area }}
                                    </Label>
                                </div>
                            </div>
                            <!-- Hidden inputs for selected focus areas -->
                            <input v-for="(area, index) in selectedFocusAreas" :key="index" type="hidden"
                                name="focus_areas[]" :value="area" />
                            <InputError :message="errors.focus_areas" />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="clients.objectives(client.id).url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                        </Link>
                        <Button type="submit" :disabled="processing">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update Objectives
                        </Button>
                    </div>
                </Form>
            </div>
        </ClientLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientLayout from '@/layouts/client/Layout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ClientController from '@/actions/App/Http/Controllers/Tenant/Coach/ClientController';

import { type BreadcrumbItem, type Client } from '@/types';
import clients from '@/routes/tenant/coach/clients';

import { LoaderCircle } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';

interface Props {
    client: Client;
}

const props = defineProps<Props>();

const focusAreaOptions = [
    'Confidence & Self-Belief',
    'Leadership Development',
    'Communication Skills',
    'Emotional Intelligence',
    'Career Direction / Change',
    'Work-Life Integration',
    'Team Relationships',
    'Decision-Making',
    'Stress Management',
    'Sleep Quality',
    'Mental Health',
    'Flexibility',
];

// Ensure focus_areas is always an array
const initialFocusAreas = Array.isArray(props.client.profile?.focus_areas)
    ? props.client.profile.focus_areas
    : [];

const selectedFocusAreas = ref<string[]>([...initialFocusAreas]);

const isAreaSelected = (area: string) => {
    return selectedFocusAreas.value.includes(area);
};

const toggleFocusArea = (area: string, checked: boolean) => {
    if (checked) {
        if (!selectedFocusAreas.value.includes(area)) {
            selectedFocusAreas.value.push(area);
        }
    } else {
        const index = selectedFocusAreas.value.indexOf(area);
        if (index > -1) {
            selectedFocusAreas.value.splice(index, 1);
        }
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.client.archived ? 'Archived Clients' : 'Clients',
        href: props.client.archived ? clients.archived().url : clients.index().url,
    },
    {
        title: props.client.name,
        href: clients.show(props.client.id).url
    },
    {
        title: 'Objectives',
        href: clients.objectives(props.client.id).url
    },
    {
        title: 'Edit',
        href: ClientController.editObjectives(props.client.id).url
    },
];
</script>
