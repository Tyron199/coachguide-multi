<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" :title="company.name" description="Manage company">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem, type Company } from '@/types';
import { Building2, Users } from 'lucide-vue-next';
import companies from '@/routes/tenant/companies';

const props = defineProps<{
    company: Company
}>();

const sidebarNavItems: NavItem[] = [
    {
        title: 'Details',
        href: companies.show(props.company.id),
        icon: Building2,
    },
    {
        title: 'Employees',
        href: companies.employees(props.company.id),
        icon: Users,
    }
];


</script>