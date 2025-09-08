<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" title="Sessions" description="Manage your sessions">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Calendar1Icon, Clock, Calendar } from 'lucide-vue-next';
import sessions from '@/routes/tenant/coach/coaching-sessions';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Upcoming Sessions',
        href: sessions.index(),
        icon: Clock,
    },
    {
        title: 'Past Sessions',
        href: sessions.past(),
        icon: Calendar1Icon,
    },
    {
        title: 'Calendar',
        href: sessions.calendar(),
        icon: Calendar,
    }
];


</script>