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
import {
    all as resourceLibraryAll,
    books as resourceLibraryBooks,
    podcasts as resourceLibraryPodcasts,
    videos as resourceLibraryVideos,
    courses as resourceLibraryCourses,
    articles as resourceLibraryArticles,
} from '@/actions/App/Http/Controllers/Tenant/Coach/ResourceLibraryController';

const sidebarNavItems: NavItem[] = [
    {
        title: 'All Resources',
        href: resourceLibraryAll().url,
        icon: BookOpen,
    },
    {
        title: 'Books',
        href: resourceLibraryBooks().url,
        icon: BookOpen,
    },
    {
        title: 'Podcasts',
        href: resourceLibraryPodcasts().url,
        icon: Podcast,
    },
    {
        title: 'Videos',
        href: resourceLibraryVideos().url,
        icon: Video,
    },
    {
        title: 'Courses',
        href: resourceLibraryCourses().url,
        icon: GraduationCap,
    },
    {
        title: 'Articles',
        href: resourceLibraryArticles().url,
        icon: FileText,
    }
];


</script>