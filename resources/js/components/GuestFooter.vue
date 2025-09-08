<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
import { terms, privacy } from '@/routes/central';
</script>

<template>
    <footer class="py-12 bg-muted/50 border-t border-border">
        <div class="container mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <AppLogo class="w-32 h-auto" />
                </div>
                <div class="flex space-x-6">
                    <Link href="/" class="text-sm text-muted-foreground hover:text-primary">Home</Link>
                    <Link :href="terms()" class="text-sm text-muted-foreground hover:text-primary">Terms of Service
                    </Link>
                    <Link :href="privacy()" class="text-sm text-muted-foreground hover:text-primary">Privacy Policy
                    </Link>
                </div>
            </div>
            <div class="mt-8 text-center text-sm text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} CoachGuide. All rights reserved.
            </div>
        </div>
    </footer>
</template>
