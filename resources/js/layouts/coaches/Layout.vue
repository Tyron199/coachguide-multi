<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" title="Coaches" description="Manage your coaches">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Users, Archive, Plus } from 'lucide-vue-next';
import coaches from '@/routes/tenant/admin/coaches';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Active Coaches',
        href: coaches.index(),
        icon: Users,
    },
    {
        title: 'Archived Coaches',
        href: coaches.archived(),
        icon: Archive,
    },
    {
        title: 'Add Coach',
        href: coaches.create(),
        icon: Plus,
    },
];

</script>
