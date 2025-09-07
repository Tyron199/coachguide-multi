<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const logoUrls = computed(() => page.props.logo as { logoUrl: string; iconUrl: string })
</script>

<template>
    <!-- Full logo - visible when sidebar is expanded -->
    <img :src="logoUrls.logoUrl" alt="CoachGuide"
        class="h-12 w-auto object-contain transition-all duration-200 group-data-[collapsible=icon]:hidden" />

    <!-- Icon logo - visible when sidebar is collapsed -->
    <img :src="logoUrls.iconUrl" alt="CoachGuide"
        class="w-6 h-auto object-contain transition-all duration-200 hidden group-data-[collapsible=icon]:block" />
</template>
