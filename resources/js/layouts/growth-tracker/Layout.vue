<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" :title="`Growth Tracker`" :sidebar-width="14"
        :description="`Keep track of your coaching development and growth​`">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
// import { models as showModels, tools as showTools } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingFrameworkController';
import {
    NotebookPen,
    GraduationCap,
    Video,
    BookOpenCheck,
    Award
} from 'lucide-vue-next';
import CoachingLogController from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingLogController';
import ProfessionalDevelopmentController from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalDevelopmentController';
import SupervisionController from '@/actions/App/Http/Controllers/Tenant/Coach/SupervisionController';
import ProfessionalCredentialController from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalCredentialController';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Coaching Log',
        href: CoachingLogController.index().url,
        icon: NotebookPen
    },
    {
        title: 'Training & Development',
        href: ProfessionalDevelopmentController.index().url,
        icon: GraduationCap,
    },

    {
        title: 'Supervision Sessions',
        href: SupervisionController.index().url,
        icon: Video,
    },
    {
        title: 'Professional Credentials',
        href: ProfessionalCredentialController.index().url,
        icon: Award,
    }
];


</script>