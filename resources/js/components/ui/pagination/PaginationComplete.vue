<template>
    <Pagination v-slot="{ page }" :total="total" :items-per-page="itemsPerPage" :page="currentPage"
        @update:page="(newPage: number) => $emit('update:page', newPage)">
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious />

            <template v-for="(item, index) in items" :key="index">
                <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                    {{ item.value }}
                </PaginationItem>
                <PaginationEllipsis v-else-if="item.type === 'ellipsis'" :index="index" />
            </template>

            <PaginationNext />
        </PaginationContent>
    </Pagination>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'

interface Props {
    total: number
    itemsPerPage: number
    page: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:page': [page: number]
}>()

const currentPage = computed(() => props.page)
</script>