<template>
    <PageLayout :sidebar-nav-items="sidebarNavItems" title="Resource Library"
        description="Curated resources for professional coaches">
        <slot />
    </PageLayout>

</template>

<script setup lang="ts">

import PageLayout from '@/layouts/page/Layout.vue';
import { type NavItem } from '@/types';
import { BookOpen, Podcast, Video, GraduationCap, FileText } from 'lucide-vue-next';
import { index as resourceLibraryIndex } from '@/actions/App/Http/Controllers/Tenant/Coach/ResourceLibraryController';

const sidebarNavItems: NavItem[] = [
    {
        title: 'All Resources',
        href: resourceLibraryIndex().url,
        icon: BookOpen,
    },
    {
        title: 'Books',
        href: resourceLibraryIndex({ query: { type: '1' } }).url,
        icon: BookOpen,
    },
    {
        title: 'Podcasts',
        href: resourceLibraryIndex({ query: { type: '2' } }).url,
        icon: Podcast,
    },
    {
        title: 'Videos',
        href: resourceLibraryIndex({ query: { type: '3' } }).url,
        icon: Video,
    },
    {
        title: 'Courses',
        href: resourceLibraryIndex({ query: { type: '4' } }).url,
        icon: GraduationCap,
    },
    {
        title: 'Articles',
        href: resourceLibraryIndex({ query: { type: '5' } }).url,
        icon: FileText,
    }
];


</script>