<script setup lang="ts">
import { ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import GlobalSearch from '@/components/GlobalSearch.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Search } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const searchOpen = ref(false);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex flex-1 items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
            <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer" @click="searchOpen = true">
                <Search class="size-5 opacity-80 group-hover:opacity-100" />
            </Button>
        </div>

        <GlobalSearch v-model:open="searchOpen" />
    </header>
</template>
