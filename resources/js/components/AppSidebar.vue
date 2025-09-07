<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { dashboard } from '@/routes/tenant';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { mainNavItems, footerNavItems, adminNavItems } from '@/components/NavItems';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const isAdmin = user.roles.includes('admin');
const isCoach = user.roles.includes('coach');

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <div className="flex justify-center py-4">
                <Link :href="dashboard()">
                <AppLogo />
                </Link>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain title="Coach" :items="mainNavItems" v-if="isCoach" />
            <NavMain title="Admin" :items="adminNavItems" v-if="isAdmin" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
