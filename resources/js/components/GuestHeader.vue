<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Link } from '@inertiajs/vue3';
import { Menu, Moon, Sun } from 'lucide-vue-next';
import { login, register, terms, privacy } from '@/routes/central';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, toggleAppearance } = useAppearance();

// Guest navigation items
const guestNavItems = [
    { title: 'Home', href: '/' },
    { title: 'About', href: '/#about' },
    { title: 'Features', href: '/#features' },
    { title: 'Pricing', href: '/#pricing' },
    { title: 'Contact', href: '/#contact' },
];

const footerNavItems = [
    { title: 'Terms of Service', href: terms() },
    { title: 'Privacy Policy', href: privacy() },
];
</script>

<template>
    <div>
        <div class="border-b border-border bg-background/95 backdrop-blur-sm">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogo class="w-24 h-auto" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <!-- Main Navigation -->
                                <nav class="-mx-3 space-y-1">
                                    <Link v-for="item in guestNavItems" :key="item.title" :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent">
                                    {{ item.title }}
                                    </Link>
                                </nav>

                                <!-- Auth Actions -->
                                <div class="flex flex-col space-y-3">
                                    <Link :href="login()">
                                    <Button variant="outline" class="w-full">
                                        Log in
                                    </Button>
                                    </Link>
                                    <Link :href="register()">
                                    <Button class="w-full">
                                        Sign up
                                    </Button>
                                    </Link>
                                </div>

                                <!-- Footer Links -->
                                <div class="flex flex-col space-y-2 pt-4 border-t">
                                    <Link v-for="item in footerNavItems" :key="item.title" :href="item.href"
                                        class="text-sm text-muted-foreground hover:text-foreground">
                                    {{ item.title }}
                                    </Link>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <!-- Logo -->
                <Link href="/" class="flex items-center">
                <AppLogo class="w-24 h-auto" />
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex lg:flex-1 lg:justify-center">
                    <nav class="flex items-center space-x-8">
                        <Link v-for="item in guestNavItems" :key="item.title" :href="item.href"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ item.title }}
                        </Link>
                    </nav>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2">

                    <!-- Auth Buttons (Hidden on mobile, shown in sheet) -->
                    <div class="hidden lg:flex lg:items-center lg:space-x-2">
                        <!-- Theme Toggle -->
                        <Button variant="outline" size="icon" @click="toggleAppearance()">
                            <component :is="appearance === 'light' ? Sun : Moon" class="w-4 h-4" />
                        </Button>
                        <Link :href="login()">
                        <Button variant="outline">
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
        </div>
    </div>
</template>
