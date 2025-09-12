<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';


defineProps<{
    sidebarNavItems: NavItem[];
    title: string;
    description: string;
    avatar?: string;
}>();




const currentPath = typeof window !== undefined ? window.location.pathname + window.location.search : '';
</script>

<template>
    <div class="px-4 py-6">
        <div class="flex  gap-4 ">

        </div>
        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-64">
                <div class="flex flex-col mb-4 items-center lg:items-start text-center lg:text-left">
                    <div id="avatar-img" v-if="avatar" class="flex mb-4">
                        <img :src="avatar" :alt="title" class="w-32 h-auto overflow-hidden rounded-lg">
                    </div>
                    <div v-if="title || description">
                        <div v-text="title" class="text-lg font-bold"></div>
                        <p v-text="description" class="text-muted-foreground"></p>
                    </div>

                </div>
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems"
                        :key="typeof item.href === 'string' ? item.href : item.href?.url" variant="ghost" :class="[
                            'w-full justify-start',
                            { 'bg-muted': currentPath === (typeof item.href === 'string' ? item.href : item.href?.url) },
                            item.class,
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
