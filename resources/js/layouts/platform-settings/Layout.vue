<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
// import { appearance, calendarIntegrations } from '@/routes/tenant';
// import avatar from '@/routes/tenant/avatar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Palette, Image } from 'lucide-vue-next';
import ThemeController from '@/actions/App/Http/Controllers/Tenant/Admin/ThemeController';
import LogoController from '@/actions/App/Http/Controllers/Tenant/Admin/LogoController';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Theme',
        href: ThemeController.index().url,
        icon: Palette,
    },
    {
        title: 'Logo',
        href: LogoController.index().url,
        icon: Image,
    }
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Platform branding" description="Manage your platform branding" />

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

            <div class="flex-1 min-w-0">
                <section class="">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
