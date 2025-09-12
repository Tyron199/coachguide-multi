<script setup lang="ts">
interface Props {
    className?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background" :class="className">
        <slot />
    </div>
</template>
