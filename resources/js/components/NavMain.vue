<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    title: string;
    items: NavItem[];
}>();

const page = usePage();

// Create reactive state for collapsible items
const openStates = ref<Record<string, boolean>>({});

// Function to normalize href for comparison
const normalizeHref = (href: any): string => {
    if (typeof href === 'string') return href;
    if (typeof href === 'function') return href();
    if (href && typeof href.url === 'string') return href.url;
    return String(href);
};

// Function to check if any sub-item is active
const isAnySubItemActive = (item: NavItem): boolean => {
    if (!item.items) return false;
    return item.items.some(subItem => {
        const subItemUrl = normalizeHref(subItem.href);
        const currentUrl = page.url;
        console.log('Checking sub-item:', subItem.title, 'URL:', subItemUrl, 'Current:', currentUrl, 'Match:', subItemUrl === currentUrl);
        return subItemUrl === currentUrl;
    });
};

// Function to check if an item should be considered active
const isItemActive = (item: NavItem): boolean => {
    const itemUrl = normalizeHref(item.href);
    const currentUrl = page.url;
    const directMatch = itemUrl === currentUrl;
    const hasActiveSubItem = isAnySubItemActive(item);

    console.log('Checking item:', item.title, 'URL:', itemUrl, 'Current:', currentUrl, 'Direct match:', directMatch, 'Has active sub:', hasActiveSubItem);

    return directMatch || hasActiveSubItem;
};

// Initialize open states based on active items and keep them persistent
const initializeOpenStates = () => {
    console.log('Initializing open states for URL:', page.url);
    props.items.forEach(item => {
        if (item.items && item.items.length > 0) {
            const hasActiveSubItem = isAnySubItemActive(item);
            console.log('Item:', item.title, 'has active sub-item:', hasActiveSubItem, 'current open state:', openStates.value[item.title]);

            // If this item has an active sub-item, ensure it's open
            if (hasActiveSubItem) {
                openStates.value[item.title] = true;
                console.log('Setting', item.title, 'to open because it has active sub-item');
            } else if (openStates.value[item.title] === undefined) {
                // Only set to false if it's not already defined (preserve user's manual state)
                openStates.value[item.title] = false;
                console.log('Setting', item.title, 'to closed (default)');
            }
            // If user manually opened/closed and no active sub-item, preserve their choice
        }
    });
};

// Initialize on mount and watch for URL changes
watch(() => page.url, () => {
    console.log('URL changed to:', page.url);
    initializeOpenStates();
}, { immediate: true });
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ title }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Items with nested subitems -->
                <Collapsible v-if="item.items && item.items.length > 0" v-model:open="openStates[item.title]" as-child
                    class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :tooltip="item.title" :is-active="isItemActive(item)">
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuSubButton as-child
                                        :is-active="normalizeHref(subItem.href) === page.url">
                                        <Link :href="subItem.href">
                                        <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- Regular items without nested subitems -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="normalizeHref(item.href) === page.url"
                        :tooltip="item.title">
                        <Link :href="item.href">
                        <component :is="item.icon" v-if="item.icon" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
