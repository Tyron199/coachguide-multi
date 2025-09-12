<template>
    <GuestShell>
        <CookieConsent />
        <GuestHeader />
        <GuestContent>
            <slot />
        </GuestContent>
        <GuestFooter />
    </GuestShell>
    <FlashToaster />
</template>

<script setup lang="ts">
import GuestShell from '@/components/GuestShell.vue';
import GuestHeader from '@/components/GuestHeader.vue';
import GuestContent from '@/components/GuestContent.vue';
import GuestFooter from '@/components/GuestFooter.vue';
import CookieConsent from '@/components/CookieConsent.vue';
import FlashToaster from '@/components/FlashToaster.vue';

</script>
