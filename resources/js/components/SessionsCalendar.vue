<template>
    <div class="space-y-4">
        <!-- Filter Controls -->
        <SessionFilters :clients="props.clients" :coaches="props.coaches" :filters="props.filters"
            :show-filters="props.showFilters" />

        <!-- Calendar Header with Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold">
                    {{ formatWeekRange(currentWeekStart) }}
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="goToPreviousWeek" :disabled="!canGoBackward">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" @click="goToCurrentWeek" :disabled="isShowingToday">
                    {{ isShowingToday ? 'Today' : 'Go to Today' }}
                </Button>
                <Button variant="outline" size="sm" @click="goToNextWeek" :disabled="!canGoForward">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="border border-border rounded-lg overflow-hidden bg-background">
            <!-- Day Headers with Dates -->
            <div class="grid grid-cols-5 border-b border-border">
                <div v-for="(date, index) in weekDates" :key="date.toISOString()"
                    class="p-4 text-center border-r border-border last:border-r-0"
                    :class="{ 'bg-primary/5': isToday(date) }">
                    <div class="font-semibold text-sm text-foreground">{{ weekDays[index] }}</div>
                    <div class="text-2xl font-bold mt-1"
                        :class="{ 'text-primary': isToday(date), 'text-foreground': !isToday(date) }">
                        {{ formatDayNumber(date) }}
                    </div>
                    <div class="text-xs text-muted-foreground">{{ formatMonth(date) }}</div>
                </div>
            </div>

            <!-- Session Cells -->
            <div class="grid grid-cols-5 min-h-[200px]">
                <div v-for="date in weekDates" :key="`sessions-${date.toISOString()}`"
                    class="border-r border-border last:border-r-0 p-2 space-y-1 relative"
                    :class="{ 'bg-primary/5': isToday(date) }">

                    <!-- Sessions for this date -->
                    <div v-for="session in getSessionsForDate(date)" :key="session.id" class="relative group">
                        <SessionCard :session="session" :can-see-coach-column="props.canSeeCoachColumn"
                            :selectable="props.selectable" :selected="selectedRows.includes(session.id)"
                            :is-active="session.is_active"
                            @toggle-selection="(checked) => toggleRowSelection(session.id, checked)" />
                    </div>

                    <!-- Empty state for days with no sessions -->
                    <div v-if="getSessionsForDate(date).length === 0"
                        class="text-xs text-muted-foreground text-center py-8 italic">
                        No sessions scheduled
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-6 text-sm bg-muted/30 p-3 rounded-lg">
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-primary"></div>
                <span class="text-muted-foreground">In Person</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-secondary"></div>
                <span class="text-muted-foreground">Online</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full border-2 border-border bg-background"></div>
                <span class="text-muted-foreground">Hybrid</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import SessionCard from '@/components/SessionCard.vue';
import SessionFilters from '@/components/SessionFilters.vue';


interface CoachingSession {
    id: number;
    session_number: number;
    client: {
        id: number;
        name: string;
    };
    coach: {
        id: number;
        name: string;
    };
    scheduled_at: string;
    duration: number;
    formatted_duration: string;
    session_type: 'in_person' | 'online' | 'hybrid';
    client_attended: boolean | null;
    created_at: string;
    is_active: boolean;
}

interface Props {
    sessions: CoachingSession[];
    clients?: { id: number; name: string }[];
    coaches?: { id: number; name: string }[];
    filters: {
        search?: string;
        client_id?: number | null;
        coach_id?: number | null;
        session_type?: string | null;
        client_attended?: boolean | null;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
        view?: 'table' | 'calendar';
        upcoming?: boolean;
        date_from?: string;
        date_to?: string;
    };
    canSeeCoachColumn?: boolean;
    selectable?: boolean;
    modelValue?: number[];
    showFilters?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canSeeCoachColumn: false,
    selectable: false,
    modelValue: () => [],
    showFilters: true,
    clients: () => [],
    coaches: () => []
});

const emit = defineEmits<{
    'update:modelValue': [value: number[]]
    'week-changed': [weekStart: Date]
}>();

// Calendar state
const currentWeekStart = ref(new Date());

// Selection state management
const selectedRows = computed({
    get: () => props.modelValue,
    set: (value: number[]) => {
        emit('update:modelValue', [...value]);
    }
});

// Get all dates for the current 5-day period starting from currentWeekStart
const weekDates = computed(() => {
    const dates = [];
    const start = new Date(currentWeekStart.value);

    for (let i = 0; i < 5; i++) {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        dates.push(date);
    }

    return dates;
});

// Generate dynamic day headers based on the current dates
const weekDays = computed(() => {
    return weekDates.value.map(date => {
        return date.toLocaleDateString('en-US', { weekday: 'long' });
    });
});

// Get sessions for a specific date
const getSessionsForDate = (date: Date) => {
    const targetDate = date.toDateString();

    return props.sessions.filter(session => {
        // Browser automatically converts UTC session time to local timezone
        const sessionDate = new Date(session.scheduled_at);
        return sessionDate.toDateString() === targetDate;
    });
};

// Date formatting functions
const formatWeekRange = (weekStart: Date) => {
    const start = new Date(weekStart);
    const end = new Date(weekStart);
    end.setDate(start.getDate() + 4);

    const startStr = start.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: start.getFullYear() !== end.getFullYear() ? 'numeric' : undefined
    });
    const endStr = end.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });

    return `${startStr} - ${endStr}`;
};

const formatDayNumber = (date: Date) => {
    return date.getDate().toString();
};

const formatMonth = (date: Date) => {
    return date.toLocaleDateString('en-US', { month: 'short' });
};

const isToday = (date: Date) => {
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

const isShowingToday = computed(() => {
    const today = new Date();
    // Check if today falls within the current 5-day period
    const start = new Date(currentWeekStart.value);
    const end = new Date(currentWeekStart.value);
    end.setDate(start.getDate() + 4);
    return today >= start && today <= end;
});

// Calendar can navigate freely in both directions
const canGoBackward = computed(() => {
    return true; // Always allow going backward
});

const canGoForward = computed(() => {
    return true; // Always allow going forward
});

// Navigation functions
const goToPreviousWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() - 5);
    currentWeekStart.value = newDate;
    emit('week-changed', newDate);
};

const goToNextWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() + 5);
    currentWeekStart.value = newDate;
    emit('week-changed', newDate);
};

const goToCurrentWeek = () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset to start of day
    // Start the 5-day period with today as the first day
    currentWeekStart.value = today;
    emit('week-changed', currentWeekStart.value);
};

// Selection functions
const toggleRowSelection = (sessionId: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedRows.value];

    if (isChecked) {
        if (!currentSelection.includes(sessionId)) {
            currentSelection.push(sessionId);
        }
    } else {
        const index = currentSelection.indexOf(sessionId);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedRows.value = currentSelection;
};

// Initialize to start the 5-day period with today
onMounted(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset to start of day
    // Start the 5-day period with today as the first day
    currentWeekStart.value = today;
});
</script>
