<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" :title="`Tools & Models`" :description="`Enhance your coaching`">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Brain, Settings, ExternalLink } from 'lucide-vue-next';
import { models as showModels, tools as showTools, profiling as showProfiling } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingFrameworkController';
import { Hammer } from 'lucide-vue-next';

import customFrameworks from '@/routes/tenant/coach/custom-frameworks';



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
        title: 'Custom',
        href: customFrameworks.index().url,
        icon: Settings,
    },
    {
        title: 'Profiling',
        href: showProfiling().url,
        icon: ExternalLink,
    }
];


</script>