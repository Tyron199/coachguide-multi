<template>

    <Head title="Profiling" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <CoachingFrameworksLayout>
            <div class="space-y-6">
                <PageHeader title="Profiling Partners"
                    description="Access our external profiling partners for comprehensive assessments and insights"
                    :badge="`${partners.length} ${partners.length === 1 ? 'partner' : 'partners'}`">
                </PageHeader>

                <!-- Partners Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-6">
                    <ProfilingCard v-for="partner in partners" :key="partner.name" :partner="partner" />
                </div>
            </div>
        </CoachingFrameworksLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CoachingFrameworksLayout from '@/layouts/coaching-frameworks/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import ProfilingCard from '@/components/ProfilingCard.vue';
import { type BreadcrumbItem } from '@/types';

interface ProfilingPartner {
    name: string;
    description: string;
    url: string;
    logo?: string;
    bestFor: string[];
}

// Hardcoded partner data
const partners: ProfilingPartner[] = [
    {
        name: 'iEQ9 Enneagram',
        description: 'The Integrative Enneagram offers a comprehensive personality assessment that reveals core motivations, fears, and behavioral patterns for deeper self-awareness and development.',
        url: 'https://www.integrative9.com/',
        bestFor: ['Leadership Development', 'Team Dynamics', 'Personal Growth', 'Conflict Resolution'],
        logo: "/images/profiling-logos/ieq9.png"
    },
    {
        name: 'ELEA',
        description: 'AI-powered assessment platform that provides deep insights into personality, cognitive abilities, and potential through advanced psychometric testing and analysis.',
        url: 'https://www.elea.ai/',
        bestFor: ['Talent Assessment', 'Career Development', 'Leadership Potential', 'Cognitive Analysis'],
        logo: "/images/profiling-logos/elea.png"
    },
    {
        name: 'ShadowMatch',
        description: 'Behavioral benchmarking system that matches individual work habits and behaviors to ideal performance patterns for specific roles and environments.',
        url: 'https://www.shadowmatch.com/',
        bestFor: ['Recruitment', 'Team Optimization', 'Performance Management', 'Cultural Fit'],
        logo: "/images/profiling-logos/shadowmatch.png"
    },
    {
        name: 'Entrepleio',
        description: 'Entrepreneurial assessment tool that evaluates entrepreneurial potential, mindset, and capabilities for business success and innovation.',
        url: 'https://entrepleio.com/',
        bestFor: ['Entrepreneurship', 'Innovation Assessment', 'Business Development', 'Startup Readiness'],
        logo: "/images/profiling-logos/entrepleio.png"
    }
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profiling',
        href: '#'
    },
];
</script>
