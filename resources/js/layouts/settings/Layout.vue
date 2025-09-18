<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { appearance, calendarIntegrations } from '@/routes/tenant';
import avatar from '@/routes/tenant/avatar';
import { edit as editPassword } from '@/routes/tenant/password';
import { edit } from '@/routes/tenant/profile';
import { show as twoFactor } from '@/actions/App/Http/Controllers/Tenant/Settings/TwoFactorController';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { User, Lock, Sun, Calendar, Shield } from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: edit(),
        icon: User,
    },
    {
        title: 'Photo',
        href: avatar.index(),
        icon: User,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: Lock,
    },
    {
        title: '2FA',
        href: twoFactor(),
        icon: Shield,
    },
    {
        title: 'Calendar',
        href: calendarIntegrations(),
        icon: Calendar,
    },
    {
        title: 'Appearance',
        href: appearance(),
        icon: Sun,
    },
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems"
                        :key="typeof item.href === 'string' ? item.href : item.href?.url" variant="ghost" :class="[
                            'w-full justify-start',
                            { 'bg-muted': currentPath === (typeof item.href === 'string' ? item.href : item.href?.url) },
                        ]" as-child>
                        <Link :href="item.href">
                        <component :is="item.icon" class="mr-2 h-4 w-4" />
                        {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
