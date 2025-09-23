<template>
    <PageLayout :sidebar-width="12" :sidebar-nav-items="sidebarNavItems" title="Clients"
        description="Manage your clients">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Users, Building2, Archive, Plus } from 'lucide-vue-next';
import clients from '@/routes/tenant/coach/clients';
import companies from '@/routes/tenant/coach/companies';
const sidebarNavItems: NavItem[] = [
    {
        title: 'Client Profiles',
        href: clients.index(),
        icon: Users,
    },
    {
        title: 'Companies',
        href: companies.index(),
        icon: Building2,
    },
    {
        title: 'Archived Clients',
        href: clients.archived(),
        icon: Archive,
    },
    {
        title: 'Add Client',
        href: clients.create(),
        icon: Plus,
    },
];


</script>