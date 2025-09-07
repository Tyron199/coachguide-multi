<template>
    <TableHead 
        :class="cn('cursor-pointer select-none hover:bg-muted/50 transition-colors', className)"
        @click="handleSort"
    >
        <div class="flex items-center justify-between">
            <span>{{ label }}</span>
            <div class="ml-2 flex flex-col">
                <ChevronUp 
                    :class="cn(
                        'h-3 w-3 transition-colors',
                        currentSort === sortKey && currentDirection === 'asc' 
                            ? 'text-foreground' 
                            : 'text-muted-foreground/40'
                    )" 
                />
                <ChevronDown 
                    :class="cn(
                        'h-3 w-3 -mt-1 transition-colors',
                        currentSort === sortKey && currentDirection === 'desc' 
                            ? 'text-foreground' 
                            : 'text-muted-foreground/40'
                    )" 
                />
            </div>
        </div>
    </TableHead>
</template>

<script setup lang="ts">
import { TableHead } from '@/components/ui/table'
import { ChevronUp, ChevronDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
    label: string
    sortKey: string
    currentSort?: string | null
    currentDirection?: 'asc' | 'desc' | null
    className?: string
}

interface Emits {
    (e: 'sort', sortKey: string, direction: 'asc' | 'desc'): void
}

const props = withDefaults(defineProps<Props>(), {
    currentSort: null,
    currentDirection: null,
    className: ''
})

const emit = defineEmits<Emits>()

const handleSort = () => {
    let newDirection: 'asc' | 'desc' = 'asc'
    
    if (props.currentSort === props.sortKey) {
        // If clicking the same column, toggle direction
        newDirection = props.currentDirection === 'asc' ? 'desc' : 'asc'
    }
    // If clicking a different column, default to 'asc'
    
    emit('sort', props.sortKey, newDirection)
}
</script>
