<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/platform-settings/Layout.vue'
import CompanyNameController from '@/actions/App/Http/Controllers/Tenant/Admin/CompanyNameController'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { type BreadcrumbItem } from '@/types'

interface Props {
    currentCompanyName: string
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Platform branding',
        href: CompanyNameController.index().url,
    },
    {
        title: 'Company Name',
        href: CompanyNameController.index().url,
    },
];

const form = useForm({
    company_name: props.currentCompanyName || ''
})

const remainingCharacters = computed(() => {
    return 30 - (form.company_name?.length || 0)
})

const isOverLimit = computed(() => {
    return remainingCharacters.value < 0
})

const previewCompanyName = computed(() => {
    return form.company_name || props.currentCompanyName || 'Your Company Name'
})

const previewSubject = computed(() => {
    return `You're invited to join ${previewCompanyName.value} as a Coach`
})

function updateCompanyName() {
    form.post(CompanyNameController.update().url)
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Company Name Settings" />

        <SettingsLayout>
            <template #header>
                <h1 class="text-2xl font-semibold text-foreground">Company Name</h1>
                <p class="text-muted-foreground">
                    Set your company name to personalize the platform. This appears in the browser title, emails, and
                    throughout the application.
                </p>
            </template>

            <div class="space-y-8">

                <!-- Current Company Name Display -->
                <Card>
                    <CardHeader>
                        <CardTitle>Current Company Name</CardTitle>
                        <CardDescription>
                            Your company name is displayed across the platform for consistent branding
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="p-4 bg-muted rounded-lg">
                            <p class="text-sm text-muted-foreground mb-1">Current name:</p>
                            <p class="text-2xl font-semibold text-foreground">
                                {{ currentCompanyName || 'Not set' }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Update Company Name Form -->
                <Card>
                    <CardHeader>
                        <CardTitle>Update Company Name</CardTitle>
                        <CardDescription>
                            Choose a name that represents your organization. Limited to 30 characters for optimal
                            display across all
                            features.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="updateCompanyName" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="company_name" class="text-sm font-medium">
                                    Company Name
                                </Label>
                                <Input id="company_name" v-model="form.company_name" type="text"
                                    placeholder="Enter company name" maxlength="30" required class="max-w-md" />
                                <div class="flex items-center justify-between max-w-md">
                                    <InputError :message="form.errors.company_name" />
                                    <p class="text-xs"
                                        :class="isOverLimit ? 'text-destructive' : 'text-muted-foreground'">
                                        {{ remainingCharacters }} characters remaining
                                    </p>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    This name represents your brand throughout the platform, including browser tabs,
                                    emails, and
                                    user-facing content.
                                </p>
                            </div>

                            <div class="flex space-x-3">
                                <Button type="submit"
                                    :disabled="form.processing || !form.company_name || form.company_name === currentCompanyName || isOverLimit">
                                    <span v-if="form.processing">Updating...</span>
                                    <span v-else>Update Company Name</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Preview Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Branding Preview</CardTitle>
                        <CardDescription>
                            See how your company name appears throughout the platform
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="p-4 bg-muted rounded-lg">
                                <p class="text-xs text-muted-foreground mb-1">Browser title:</p>
                                <p class="text-sm font-medium">
                                    Dashboard - {{ previewCompanyName }}
                                </p>
                            </div>
                            <div class="p-4 bg-muted rounded-lg">
                                <p class="text-xs text-muted-foreground mb-1">Email sender:</p>
                                <p class="text-sm font-medium">
                                    {{ previewCompanyName }}
                                </p>
                            </div>
                            <div class="p-4 bg-muted rounded-lg">
                                <p class="text-xs text-muted-foreground mb-1">Email subject example:</p>
                                <p class="text-sm">
                                    {{ previewSubject }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>

    </AppLayout>
</template>
