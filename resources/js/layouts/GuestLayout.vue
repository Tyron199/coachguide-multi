<template>
    <div class="min-h-screen bg-background">
        <CookieConsent />
        <!-- Header/Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 py-4 border-b border-border bg-background/95 backdrop-blur-sm">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex justify-between items-center">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">

                        <Link href="/">
                        <AppLogo class="w-24 h-auto" />
                        </Link>
                    </div>

                    <!-- Navigation Links (optional) -->
                    <slot name="links"></slot>

                    <!-- Login Button -->
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="outline" size="icon" @click="toggleAppearance()">
                            <component :is="appearance === 'light' ? Sun : Moon" class="w-4 h-4" />
                        </Button>
                        <Link :href="login()">
                        <Button variant="outline" class="mr-2">
                            Log in
                        </Button>
                        </Link>
                        <Link :href="register()">
                        <Button>
                            Sign up
                        </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Spacer to prevent content from hiding behind fixed header -->
        <div class="h-16"></div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="py-12 bg-muted/50 border-t border-border">
            <div class="container mx-auto max-w-7xl px-4 md:px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <AppLogo class="w-32 h-auto" />

                    </div>
                    <div class="flex space-x-6">
                        <Link href="/" class="text-sm text-gray-500 hover:text-primary">Home</Link>
                        <Link :href="terms()" class="text-sm text-gray-500 hover:text-primary">Terms of
                        Service</Link>
                        <Link :href="privacy()" class="text-sm text-gray-500 hover:text-primary">Privacy
                        Policy</Link>
                    </div>
                </div>
                <div class="mt-8 text-center text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} CoachGuide. All rights reserved.
                </div>
            </div>
        </footer>
    </div>

    <SonnerToaster />
</template>

<script setup>
import { Button } from '@/components/ui/button';
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
import CookieConsent from '@/components/CookieConsent.vue';
import { Toaster as SonnerToaster } from '@/components/ui/sonner'
import { login, register, terms, privacy } from '@/routes/central';
import 'vue-sonner/style.css'
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';


const { appearance, toggleAppearance } = useAppearance();


</script>
