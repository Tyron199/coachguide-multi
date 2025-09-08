<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background" :class="class">
        <slot />
    </div>
</template>
