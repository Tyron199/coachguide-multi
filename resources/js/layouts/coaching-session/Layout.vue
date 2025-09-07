<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" :title="`${session.client?.name}`"
        :description="`Coaching Session #${session.session_number}`">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { Clock, FileText, CheckSquare } from 'lucide-vue-next';
import sessions from '@/routes/tenant/coaching-sessions';
import { type CoachingSession } from '@/types';

const props = defineProps<{
    session: CoachingSession;
}>();

const sidebarNavItems: NavItem[] = [
    {
        title: 'Details',
        href: sessions.show(props.session.id),
        icon: Clock,
    },
    {
        title: 'Notes',
        href: sessions.notes(props.session.id),
        icon: FileText,
    },
    {
        title: 'Tasks',
        href: sessions.tasks(props.session.id),
        icon: CheckSquare,
    }
];


</script>