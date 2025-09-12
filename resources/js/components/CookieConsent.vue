<template>
    <div v-if="show" class="fixed bottom-0 left-0 right-0 bg-background border-t shadow-lg p-4 z-50">
        <div
            class="container mx-auto max-w-7xl px-4 md:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                We use cookies to enhance your experience. By continuing to visit this site you agree to our use of
                cookies.
                <Link href="/privacy-policy" class="text-primary hover:underline">Learn more</Link>
            </div>
            <div class="flex gap-3">
                <Button variant="outline" size="sm" @click="decline">Decline</Button>
                <Button size="sm" @click="accept">Accept All</Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const show = ref(false);

onMounted(() => {
    const cookieChoice = localStorage.getItem('cookie-consent');
    if (!cookieChoice) {
        show.value = true;
    }
});

const accept = () => {
    localStorage.setItem('cookie-consent', 'accepted');
    show.value = false;
    // Initialize analytics and other cookie-dependent services
};

const decline = () => {
    localStorage.setItem('cookie-consent', 'declined');
    show.value = false;
    // Ensure no optional cookies are set
};
</script>
