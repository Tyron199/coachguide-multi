<template>
    <div class="space-y-4">
        <!-- Search and Filter Controls -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <Input v-model="localFilters.search" placeholder="Search frameworks by name or description..."
                    @input="debouncedSearch" class="max-w-sm" />
            </div>
            <div class="flex gap-2">
                <!-- Subcategory Filter -->
                <DropdownMenu v-if="props.subcategories.length > 0">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="min-w-[140px] justify-between">
                            {{ selectedSubcategoryName }}
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-[200px]">
                        <DropdownMenuItem @click="selectSubcategory(null)">
                            All Subcategories
                        </DropdownMenuItem>
                        <DropdownMenuSeparator v-if="props.subcategories.length > 0" />
                        <DropdownMenuItem v-for="subcategory in props.subcategories" :key="subcategory"
                            @click="selectSubcategory(subcategory)">
                            {{ formatSubcategory(subcategory) }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Best For Filter -->
                <DropdownMenu v-if="props.bestForOptions.length > 0">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="min-w-[140px] justify-between">
                            {{ selectedBestForName }}
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-[200px]">
                        <DropdownMenuItem @click="selectBestFor(null)">
                            All Coaching Types
                        </DropdownMenuItem>
                        <DropdownMenuSeparator v-if="props.bestForOptions.length > 0" />
                        <DropdownMenuItem v-for="type in props.bestForOptions" :key="type" @click="selectBestFor(type)">
                            {{ formatCoachingType(type) }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Frameworks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <FrameworkCard v-for="framework in props.frameworks.data" :key="framework.id" :framework="framework"
                @assign="$emit('assign', framework)" @preview="$emit('preview', framework)" />
        </div>

        <!-- Pagination -->
        <div v-if="props.frameworks.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.frameworks.total" :items-per-page="props.frameworks.per_page"
                :page="props.frameworks.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { PaginationComplete } from '@/components/ui/pagination';
import FrameworkCard from '@/components/FrameworkCard.vue';

interface Framework {
    id: number;
    slug: string;
    name: string;
    description: string;
    category: string;
    subcategory: string;
    best_for: string[];
    instances_count?: number;
    completed_instances_count?: number;
}

interface PaginatedFrameworks {
    data: Framework[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface FrameworkFilters {
    search?: string;
    category?: string;
    subcategory?: string;
    best_for?: string;
    sort_by?: string;
    sort_direction?: string;
}

interface Props {
    frameworks: PaginatedFrameworks;
    subcategories: string[];
    bestForOptions: string[];
    filters: FrameworkFilters;
}

const props = defineProps<Props>();

defineEmits<{
    assign: [framework: Framework];
    preview: [framework: Framework];
}>();

const localFilters = ref({
    search: props.filters.search || '',
    subcategory: props.filters.subcategory || '',
    best_for: props.filters.best_for || '',
    sort_by: props.filters.sort_by || 'name',
    sort_direction: props.filters.sort_direction || 'asc',
});

const selectedSubcategoryName = computed(() => {
    if (!localFilters.value.subcategory) return 'All Subcategories';
    return formatSubcategory(localFilters.value.subcategory);
});

const selectedBestForName = computed(() => {
    if (!localFilters.value.best_for) return 'All Coaching Types';
    return formatCoachingType(localFilters.value.best_for);
});

const updateFilters = () => {
    router.get(window.location.pathname, {
        search: localFilters.value.search || undefined,
        subcategory: localFilters.value.subcategory || undefined,
        best_for: localFilters.value.best_for || undefined,
        sort_by: localFilters.value.sort_by,
        sort_direction: localFilters.value.sort_direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Simple debounce function
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 300);
};

const selectSubcategory = (subcategory: string | null) => {
    localFilters.value.subcategory = subcategory || '';
    updateFilters();
};

const selectBestFor = (bestFor: string | null) => {
    localFilters.value.best_for = bestFor || '';
    updateFilters();
};

const updatePage = (page: number) => {
    router.get(window.location.pathname, {
        ...localFilters.value,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Utility functions
function formatSubcategory(subcategory: string): string {
    return subcategory
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatCoachingType(type: string): string {
    return type
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
</script>
