<template>
    <PageLayout :sidebar-width="12" :sidebar-nav-items="sidebarNavItems" :title="client.name"
        description="Manage client" :avatar="userAvatar">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem, type Client } from '@/types';
import { User, Target, FileText, CheckSquare, HandshakeIcon, Calendar1Icon } from 'lucide-vue-next';
import clients from '@/routes/tenant/coach/clients';
import contracts from '@/routes/tenant/coach/clients/contracts';
import { computed } from 'vue';

const props = defineProps<{
    client: Client
}>();

const userAvatar = computed(() => props.client.avatar || 'https://api.dicebear.com/9.x/shapes/svg?seed=' + props.client.name);

const sidebarNavItems: NavItem[] = [
    {
        title: 'Details',
        href: clients.show(props.client.id),
        icon: User,
    },
    {
        title: 'Objectives',
        href: clients.objectives(props.client.id),
        icon: Target,
    },
    {
        title: 'Sessions',
        href: clients.sessions(props.client.id),
        icon: Calendar1Icon,
    },
    {
        title: 'Notes',
        href: clients.notes(props.client.id),
        icon: FileText,
    },
    {
        title: 'Tasks',
        href: clients.tasks(props.client.id),
        icon: CheckSquare,
    },
    {
        title: 'Contracts',
        href: contracts.index(props.client.id),
        icon: HandshakeIcon,
    }
];


</script>