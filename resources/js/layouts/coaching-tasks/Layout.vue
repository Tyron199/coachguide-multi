<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" title="Tasks" description="Manage coaching tasks">
        <slot />
    </PageLayout>
</template>

<script setup lang="ts">
import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Clock, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import tasks from '@/routes/tenant/coach/coaching-tasks';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Pending',
        href: tasks.index({ view: 'pending' }),
        icon: Clock,
    },
    {
        title: 'Overdue',
        href: tasks.index({ view: 'overdue' }),
        icon: AlertTriangle,
    },
    {
        title: 'Completed',
        href: tasks.index({ view: 'completed' }),
        icon: CheckCircle,
    }
];
</script>

