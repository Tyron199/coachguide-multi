<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" title="Administrators" description="Manage your administrators">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Users, Archive, Plus } from 'lucide-vue-next';
import administrators from '@/routes/tenant/admin/administrators';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Active Administrators',
        href: administrators.index(),
        icon: Users,
    },
    {
        title: 'Archived Administrators',
        href: administrators.archived(),
        icon: Archive,
    },
    {
        title: 'Add Administrator',
        href: administrators.create(),
        icon: Plus,
    },
];

</script>
