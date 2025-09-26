<template>
    <Card class="hover:shadow-sm transition-shadow h-full">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle class="mb-2">
                        {{ partner.name }}
                    </CardTitle>
                    <CardDescription class="line-clamp-3">
                        {{ partner.description }}
                    </CardDescription>
                </div>
                <!-- Logo in top right corner -->
                <div v-if="partner.logo" class="ml-3 flex-shrink-0">
                    <img :src="partner.logo" :alt="`${partner.name} logo`" class="h-auto w-32 object-contain" />
                </div>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <!-- Best For -->
            <div v-if="partner.bestFor && partner.bestFor.length > 0" class="space-y-2">
                <div class="flex items-center gap-2">
                    <Target class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium text-foreground">Best for:</span>
                </div>
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="type in partner.bestFor.slice(0, 3)" :key="type" variant="outline" class="text-xs">
                        {{ type }}
                    </Badge>
                    <Badge v-if="partner.bestFor.length > 3" variant="outline" class="text-xs">
                        +{{ partner.bestFor.length - 3 }} more
                    </Badge>
                </div>
            </div>
        </CardContent>

        <CardFooter>
            <a :href="partner.url" target="_blank" rel="noopener noreferrer" class="w-full">
                <Button class="w-full">
                    <ExternalLink class="mr-2 h-4 w-4" />
                    Access
                </Button>
            </a>
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Target, ExternalLink } from 'lucide-vue-next';

interface ProfilingPartner {
    name: string;
    description: string;
    url: string;
    logo?: string;
    bestFor: string[];
}

interface Props {
    partner: ProfilingPartner;
}

defineProps<Props>();
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
