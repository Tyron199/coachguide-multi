<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                <Badge v-if="badge" :variant="badgeVariant" class="text-sm">
                    {{ badge }}
                </Badge>
            </div>
            <p v-if="description" class="text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap gap-2 w-full sm:w-auto sm:justify-end">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

interface Props {
    title: string;
    description?: string;
    badge?: string;
    badgeVariant?: 'default' | 'secondary' | 'destructive';
}

defineProps<Props>();
</script>
