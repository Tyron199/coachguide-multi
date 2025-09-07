<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun, SidebarOpen, LayoutTemplate } from 'lucide-vue-next';

const { appearance, updateAppearance, layout, updateLayout } = useAppearance();

const appearanceTabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

const layoutTabs = [
    { value: 'sidebar', Icon: SidebarOpen, label: 'Sidebar' },
    { value: 'header', Icon: LayoutTemplate, label: 'Header' },
] as const;
</script>

<template>
    <div class="space-y-6">
        <!-- Theme Selection -->
        <div>
            <h3 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-3">Theme</h3>
            <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                <button v-for="{ value, Icon, label } in appearanceTabs" :key="value" @click="updateAppearance(value)"
                    :class="[
                        'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                        appearance === value
                            ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                            : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                    ]">
                    <component :is="Icon" class="-ml-1 h-4 w-4" />
                    <span class="ml-1.5 text-sm">{{ label }}</span>
                </button>
            </div>
        </div>

        <!-- Layout Selection -->
        <div>
            <h3 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-3">Layout</h3>
            <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                <button v-for="{ value, Icon, label } in layoutTabs" :key="value" @click="updateLayout(value)" :class="[
                    'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                    layout === value
                        ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                        : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                ]">
                    <component :is="Icon" class="-ml-1 h-4 w-4" />
                    <span class="ml-1.5 text-sm">{{ label }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
