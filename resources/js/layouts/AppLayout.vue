<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import FlashToaster from '@/components/FlashToaster.vue';
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';

import { AlertProvider } from '@/plugins/alert';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { layout } = useAppearance();

const LayoutComponent = computed(() => {
    return layout.value === 'sidebar' ? AppSidebarLayout : AppHeaderLayout;
});
</script>

<template>
    <component :is="LayoutComponent" :breadcrumbs="props.breadcrumbs">
        <slot />
    </component>

    <FlashToaster />
    <AlertProvider />
</template>
