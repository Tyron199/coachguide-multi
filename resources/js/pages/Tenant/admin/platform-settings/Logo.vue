<script setup lang="ts">
import { useForm, Head, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/platform-settings/Layout.vue'
import LogoController from '@/actions/App/Http/Controllers/Tenant/Admin/LogoController'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { type BreadcrumbItem } from '@/types'

interface Props {
    hasCustomLogo: boolean
    hasCustomIcon: boolean
}

const page = usePage()
const logoUrls = computed(() => page.props.logo as { logoUrl: string; iconUrl: string })

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Platform branding',
        href: LogoController.index().url,
    },
    {
        title: 'Logo',
        href: LogoController.index().url,
    },
];

defineProps<Props>()

const logoForm = useForm({
    logo: null as File | null,
    type: 'logo' as 'logo' | 'icon'
})

const iconForm = useForm({
    logo: null as File | null,
    type: 'icon' as 'logo' | 'icon'
})

const logoPreview = ref<string | null>(null)
const iconPreview = ref<string | null>(null)

function handleLogoSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (file) {
        logoForm.logo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    }
}

function handleIconSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (file) {
        iconForm.logo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            iconPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    }
}

function uploadLogo() {
    logoForm.post(LogoController.upload().url, {
        onSuccess: () => {
            logoForm.reset()
            logoPreview.value = null
        }
    })
}

function uploadIcon() {
    iconForm.post(LogoController.upload().url, {
        onSuccess: () => {
            iconForm.reset()
            iconPreview.value = null
        }
    })
}

function resetLogo() {
    useForm({ type: 'logo' }).post(LogoController.reset().url)
}

function resetIcon() {
    useForm({ type: 'icon' }).post(LogoController.reset().url)
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Logo Settings" />

        <SettingsLayout>
            <template #header>
                <h1 class="text-2xl font-semibold text-foreground">Logo Settings</h1>
                <p class="text-muted-foreground">Customize your application's logo and icon. Upload custom images or
                    reset to defaults.</p>
            </template>

            <div class="space-y-8">

                <!-- Main Logo Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Main Logo</CardTitle>
                        <CardDescription>
                            Upload a custom logo for your application. This will be displayed in the sidebar when
                            expanded.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-start space-x-6">
                            <!-- Current Logo Display -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-32 h-20 border-2 border-dashed border-border rounded-lg flex items-center justify-center bg-muted">
                                    <img :src="logoPreview || logoUrls.logoUrl" alt="Current Logo"
                                        class="max-w-full max-h-full object-contain" />
                                </div>
                                <p class="mt-2 text-xs text-muted-foreground text-center">Current Logo</p>
                            </div>

                            <!-- Upload Form -->
                            <div class="flex-1">
                                <form @submit.prevent="uploadLogo" class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium mb-2">
                                            Upload New Logo
                                        </Label>
                                        <input type="file" accept="image/*" @change="handleLogoSelect"
                                            class="block w-full text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Recommended: PNG or SVG format, max 2MB
                                        </p>
                                    </div>

                                    <div class="flex space-x-3">
                                        <Button type="submit" :disabled="!logoForm.logo || logoForm.processing">
                                            <span v-if="logoForm.processing">Uploading...</span>
                                            <span v-else>Upload Logo</span>
                                        </Button>

                                        <Button type="button" variant="outline" @click="resetLogo">
                                            Reset to Default
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Icon Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>App Icon</CardTitle>
                        <CardDescription>
                            Upload a custom icon for your application. This will be displayed in the sidebar when
                            collapsed and as
                            the app favicon.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-start space-x-6">
                            <!-- Current Icon Display -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-16 h-16 border-2 border-dashed border-border rounded-lg flex items-center justify-center bg-muted">
                                    <img :src="iconPreview || logoUrls.iconUrl" alt="Current Icon"
                                        class="max-w-full max-h-full object-contain" />
                                </div>
                                <p class="mt-2 text-xs text-muted-foreground text-center">Current Icon</p>
                            </div>

                            <!-- Upload Form -->
                            <div class="flex-1">
                                <form @submit.prevent="uploadIcon" class="space-y-4">
                                    <div>
                                        <Label class="text-sm font-medium mb-2">
                                            Upload New Icon
                                        </Label>
                                        <input type="file" accept="image/*" @change="handleIconSelect"
                                            class="block w-full text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Recommended: Square ratio, PNG or SVG format, max 2MB
                                        </p>
                                    </div>

                                    <div class="flex space-x-3">
                                        <Button type="submit" :disabled="!iconForm.logo || iconForm.processing">
                                            <span v-if="iconForm.processing">Uploading...</span>
                                            <span v-else>Upload Icon</span>
                                        </Button>

                                        <Button type="button" variant="outline" @click="resetIcon">
                                            Reset to Default
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Preview Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Preview</CardTitle>
                        <CardDescription>
                            See how your logo and icon will appear in different contexts within the application.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <!-- Sidebar expanded preview -->
                            <div class="p-4 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground mb-2">Sidebar Expanded:</p>
                                <img :src="logoPreview || logoUrls.logoUrl" alt="Logo Preview"
                                    class="w-26 h-auto object-contain" />
                            </div>

                            <!-- Sidebar collapsed preview -->
                            <div class="p-4 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground mb-2">Sidebar Collapsed:</p>
                                <img :src="iconPreview || logoUrls.iconUrl" alt="Icon Preview"
                                    class="w-6 h-auto object-contain" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>

    </AppLayout>
</template>
