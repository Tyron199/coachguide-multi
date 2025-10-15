<script setup lang="ts">
import { ref, watch } from 'vue';
import { useMagicKeys } from '@vueuse/core';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import type { SearchResponse, SearchResult } from '@/types/search';
import {
    LayoutGrid,
    Users,
    Building,
    Calendar,
    FileText,
    CheckSquare,
    Hammer,
    Award,
    GraduationCap,
    UserCheck,
    Loader2,
    Search,
} from 'lucide-vue-next';
import type { Component } from 'vue';

interface Props {
    open?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const open = ref(false);
const searchInput = ref('');
const isLoading = ref(false);
const searchResults = ref<SearchResponse['results'] | null>(null);

const { Meta_K, Ctrl_K } = useMagicKeys({
    passive: false,
    onEventFired(e) {
        if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
        }
    },
});

watch([Meta_K, Ctrl_K], (v) => {
    if (v[0] || v[1]) {
        handleOpenChange();
    }
});

watch(() => props.open, (newVal) => {
    open.value = newVal;
    if (!newVal) {
        searchInput.value = '';
        searchResults.value = null;
    }
});

watch(open, (newVal) => {
    emit('update:open', newVal);
});

const iconMap: Record<string, Component> = {
    navigation: LayoutGrid,
    client: Users,
    company: Building,
    session: Calendar,
    note: FileText,
    task: CheckSquare,
    framework: Hammer,
    credential: Award,
    professional_development: GraduationCap,
    supervision: UserCheck,
};

const groupLabels: Record<string, string> = {
    navigation: 'Navigation',
    clients: 'Clients',
    companies: 'Companies',
    sessions: 'Sessions',
    notes: 'Notes',
    tasks: 'Tasks',
    frameworks: 'Tools & Models',
    credentials: 'Credentials',
    professional_development: 'Training & Development',
    supervisions: 'Supervisions',
};

function handleOpenChange() {
    open.value = !open.value;
}

async function handleSearch() {
    if (searchInput.value.length < 2) {
        searchResults.value = null;
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get<SearchResponse>('/api/search', {
            params: { query: searchInput.value },
        });
        searchResults.value = response.data.results;
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = null;
    } finally {
        isLoading.value = false;
    }
}

function handleResultClick(result: SearchResult) {
    router.visit(result.url);
    open.value = false;
    searchInput.value = '';
    searchResults.value = null;
}

const hasResults = () => {
    if (!searchResults.value) return false;
    return Object.values(searchResults.value).some((group) => group.length > 0);
};
</script>

<template>
    <CommandDialog v-model:open="open" :shouldFilter="false">
        <!-- Custom input that doesn't bind to Command's filter state -->
        <div class="flex items-center border-b px-3" cmdk-input-wrapper>
            <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
            <input v-model="searchInput" type="text" placeholder="Type to search and press Enter..."
                class="flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                @keydown.enter="handleSearch" />
        </div>

        <CommandList>
            <div v-if="isLoading" class="flex items-center justify-center py-6">
                <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
            </div>

            <template v-else-if="searchResults && hasResults()">
                <template v-for="(results, key) in searchResults" :key="key">
                    <CommandGroup v-if="results.length > 0" :heading="groupLabels[key]">
                        <CommandItem v-for="result in results" :key="`${result.type}-${result.url}`"
                            @select="handleResultClick(result)">
                            <component :is="iconMap[result.type] || iconMap.navigation" class="mr-2 h-4 w-4" />
                            <div class="flex flex-col">
                                <span>{{ result.title }}</span>
                                <span v-if="result.subtitle" class="text-xs text-muted-foreground">
                                    {{ result.subtitle }}
                                </span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                    <CommandSeparator v-if="results.length > 0 && key !== 'supervisions'" />
                </template>
            </template>

            <CommandEmpty v-else-if="searchResults && !hasResults()">
                No results found.
            </CommandEmpty>

            <CommandEmpty v-else>
                Type at least 2 characters and press Enter to search...
            </CommandEmpty>
        </CommandList>
    </CommandDialog>
</template>
