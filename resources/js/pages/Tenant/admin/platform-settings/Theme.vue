<template>

    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Theme settings" />
        <SettingsLayout>
            <template #header>
                <h1 class="text-2xl font-semibold text-foreground">Theme Settings</h1>
                <p class="text-muted-foreground">Customize the appearance of your application.</p>
            </template>

            <div class="space-y-8">


                <!-- Available Themes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Available Themes</CardTitle>
                        <CardDescription>
                            Choose from our predefined themes or upload your own custom theme. Click any theme to
                            preview it
                            instantly.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="updateTheme" class="space-y-4">
                            <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-14 gap-4">
                                <div v-for="(theme, key) in availableThemes" :key="key"
                                    class="relative group cursor-pointer transition-all duration-200 hover:scale-105"
                                    @click="selectedTheme = key">
                                    <!-- Theme color background -->
                                    <div class="aspect-square rounded-xl border-2 transition-all duration-200" :class="{
                                        'border-foreground shadow-lg scale-105': selectedTheme === key,
                                        'border-border hover:border-muted-foreground': selectedTheme !== key
                                    }" :style="{ background: theme.preview_color }">

                                        <!-- Selected indicator -->
                                        <div v-if="selectedTheme === key"
                                            class="absolute inset-0 flex items-center justify-center">
                                            <div
                                                class="w-8 h-8 bg-background rounded-full flex items-center justify-center shadow-lg">
                                                <svg class="w-5 h-5 text-foreground" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Hidden radio input -->
                                        <input type="radio" :value="key" v-model="selectedTheme" class="sr-only">
                                    </div>

                                    <!-- Theme name -->
                                    <div class="mt-2 text-center">
                                        <p class="text-sm font-medium text-foreground">{{ theme.name }}</p>
                                        <p v-if="selectedTheme === key" class="text-xs text-primary font-medium">
                                            Selected</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <Button type="submit" :disabled="selectedTheme === currentTheme || form.processing">
                                    <span v-if="form.processing">Updating...</span>
                                    <span v-else>Apply Theme</span>
                                </Button>

                                <Button type="button" variant="outline" @click="resetToDefault"
                                    :disabled="currentTheme === 'default' || form.processing">
                                    Reset to Default
                                </Button>

                                <Button v-if="selectedTheme !== currentTheme" type="button" variant="ghost"
                                    @click="cancelPreview">
                                    Cancel Preview
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Theme Preview -->
                <Card>
                    <CardHeader>
                        <CardTitle>Theme Preview</CardTitle>
                        <CardDescription>
                            See how the selected theme will look across different components in your application.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Color palette swatches -->
                            <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-background border-2 border-border"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Background</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-foreground"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Foreground</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-primary"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Primary</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-secondary"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Secondary</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-muted"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Muted</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-accent"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Accent</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-destructive"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Destructive</p>
                                </div>
                                <div class="text-center">
                                    <div class="h-12 w-full rounded-lg bg-card border-2 border-border"></div>
                                    <p class="text-xs mt-1 text-muted-foreground">Card</p>
                                </div>
                            </div>

                            <Separator />

                            <!-- Component showcase -->
                            <div class="space-y-4">
                                <!-- Buttons row -->
                                <div class="flex flex-wrap gap-2">
                                    <Button size="sm">Primary</Button>
                                    <Button variant="secondary" size="sm">Secondary</Button>
                                    <Button variant="outline" size="sm">Outline</Button>
                                    <Button variant="ghost" size="sm">Ghost</Button>
                                    <Button variant="destructive" size="sm">Destructive</Button>
                                </div>

                                <!-- Badges row -->
                                <div class="flex flex-wrap gap-2">
                                    <Badge>Default</Badge>
                                    <Badge variant="secondary">Secondary</Badge>
                                    <Badge variant="outline">Outline</Badge>
                                    <Badge variant="destructive">Destructive</Badge>
                                </div>

                                <!-- Interactive components -->
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Input placeholder="Search..." class="h-9" />
                                        <div class="flex items-center gap-2">
                                            <Checkbox id="preview-check" />
                                            <Label for="preview-check" class="text-sm">Enable notifications</Label>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <Label for="preview-toggle" class="text-sm">Dark mode</Label>
                                            <Switch id="preview-toggle" />
                                        </div>
                                    </div>

                                    <!-- Sample card -->
                                    <Card>
                                        <CardContent class="p-3">
                                            <div class="flex items-center gap-2">
                                                <Avatar class="h-8 w-8">
                                                    <AvatarFallback class="text-xs">JD</AvatarFallback>
                                                </Avatar>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium truncate">John Doe</p>
                                                    <p class="text-xs text-muted-foreground">Client Manager</p>
                                                </div>
                                                <Badge variant="outline" class="text-xs">Active</Badge>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </SettingsLayout>
    </AppLayout>

</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/platform-settings/Layout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Switch } from '@/components/ui/switch'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Separator } from '@/components/ui/separator'
import ThemeController from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController'
import { type BreadcrumbItem } from '@/types'

interface ThemeData {
    name: string
    preview_color: string
}

interface Props {
    currentTheme: string
    availableThemes: Record<string, ThemeData>
    themePaths: Record<string, string>
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Platform settings',
        href: ThemeController.index().url,
    },
    {
        title: 'Theme',
        href: ThemeController.index().url,
    },
];

const selectedTheme = ref(props.currentTheme)
const originalThemeLink = ref<HTMLLinkElement | null>(null)

// Form for updating predefined themes
const form = useForm({
    theme: props.currentTheme
})


// Theme preview functionality
const previewTheme = (themeName: string) => {
    const themeLink = document.getElementById('theme-css') as HTMLLinkElement
    if (!themeLink) {
        console.warn('Theme CSS link element not found')
        return
    }

    if (themeName === props.currentTheme) {
        // Reset to original theme
        resetPreview()
        return
    }

    const themePath = props.themePaths[themeName]
    if (!themePath) {
        console.warn(`Theme path not found for: ${themeName}`)
        return
    }

    try {
        // Store original href if not already stored
        if (!originalThemeLink.value) {
            originalThemeLink.value = themeLink.cloneNode() as HTMLLinkElement
        }

        // Update the theme CSS link href to preview the new theme
        themeLink.href = themePath

    } catch (error) {
        console.error('Failed to preview theme:', error)
        // Reset to current theme if preview fails
        selectedTheme.value = props.currentTheme
    }
}

const resetPreview = () => {
    const themeLink = document.getElementById('theme-css') as HTMLLinkElement

    // Reset to original theme path
    if (originalThemeLink.value && themeLink) {
        themeLink.href = originalThemeLink.value.href
    }
}


// Watch for theme selection changes
watch(selectedTheme, (newTheme) => {
    previewTheme(newTheme)
})

const cancelPreview = () => {
    selectedTheme.value = props.currentTheme
    resetPreview()
}



// Cleanup on component unmount
onUnmounted(() => {
    resetPreview()
})

function updateTheme() {
    form.theme = selectedTheme.value
    form.post(ThemeController.update().url, {
        onSuccess: () => {
            // Update the original theme reference to the new theme
            // so we don't reset back to the old theme on unmount
            const themeLink = document.getElementById('theme-css') as HTMLLinkElement
            if (themeLink && originalThemeLink.value) {
                originalThemeLink.value.href = themeLink.href
            }
        }
    })
}

function resetToDefault() {
    form.post(ThemeController.resetToDefault().url, {
        onSuccess: () => {
            selectedTheme.value = 'default'
            // Update the original theme reference to the default theme
            const themeLink = document.getElementById('theme-css') as HTMLLinkElement
            if (themeLink && originalThemeLink.value) {
                originalThemeLink.value.href = '/css/themes/default.css'
            }
        }
    })
}


</script>
