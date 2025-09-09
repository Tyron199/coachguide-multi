<template>
    <div class="flex flex-col items-center justify-center space-y-4">
        <!-- Animated spinner -->
        <div class="relative">
            <div class="w-16 h-16 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
            <div class="absolute inset-0 w-16 h-16 border-4 border-transparent border-r-primary/40 rounded-full animate-spin"
                style="animation-delay: -0.15s;"></div>
        </div>

        <!-- Loading text with animation -->
        <div class="text-center space-y-2">
            <h3 class="text-lg font-semibold text-foreground">{{ title }}</h3>
            <p class="text-sm text-muted-foreground max-w-md">{{ description }}</p>

            <!-- Animated dots -->
            <div class="flex justify-center space-x-1 mt-2">
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Loading...',
    description: 'Please wait while we process your request.'
});
</script>
