<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" :title="`Tools & Models`" :description="`Enhance your coaching`">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Brain } from 'lucide-vue-next';
import { models as showModels, tools as showTools } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingFrameworkController';
import { Hammer } from 'lucide-vue-next';
import { BookOpen } from 'lucide-vue-next';


const sidebarNavItems: NavItem[] = [
    {
        title: 'Models',
        href: showModels().url,
        icon: Brain
    },
    {
        title: 'Tools',
        href: showTools().url,
        icon: Hammer,
    },
    {
        title: 'Training',
        href: "#",
        icon: BookOpen,
    }
];


</script>