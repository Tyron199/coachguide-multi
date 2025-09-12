<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ChevronLeft } from 'lucide-vue-next';

interface BreadcrumbItemType {
    title: string;
    href?: string;
}

const props = defineProps<{
    breadcrumbs: BreadcrumbItemType[];
}>();

const previousCrumb = computed<BreadcrumbItemType | null>(() => {
    const length = props.breadcrumbs?.length ?? 0;
    if (length >= 2) {
        return props.breadcrumbs[length - 2];
    }
    return null;
});

function onBack() {
    if (previousCrumb.value?.href) {
        router.visit(previousCrumb.value.href);
    } else {
        window.history.back();
    }
}
</script>

<template>
    <!-- Mobile: show compact Back control to previous item -->
    <div class="md:hidden">
        <button v-if="previousCrumb"
            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
            @click="onBack">
            <ChevronLeft class="h-4 w-4" />
            <span class="truncate max-w-[12rem]">{{ previousCrumb.title }}</span>
        </button>
    </div>

    <!-- Desktop: full breadcrumb trail -->
    <Breadcrumb class="hidden md:block">
        <BreadcrumbList>
            <template v-for="(item, index) in props.breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <template v-if="index === props.breadcrumbs.length - 1">
                        <BreadcrumbPage class="truncate max-w-[14rem]">{{ item.title }}</BreadcrumbPage>
                    </template>
                    <template v-else>
                        <BreadcrumbLink as-child>
                            <Link :href="item.href ?? '#'">
                            <span class="truncate max-w-[14rem] block">{{ item.title }}</span>
                            </Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index !== props.breadcrumbs.length - 1" />
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
