<template>

    <Head title="Growth Tracker - Coaching Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Coaching Log" description="View your coaching session summary by client"
                    :badge="`${coachingLog.length} clients`">
                </PageHeader>

                <!-- Date Range Filter -->
                <div class="bg-background border rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <DateRangePicker v-model="dateRange" @update:model-value="onDateRangeChange"
                            class="flex-shrink-0" />
                        <Button @click="applyFilters" :disabled="isLoading" class="flex-shrink-0">
                            <Search class="h-4 w-4 mr-2" />
                            {{ isLoading ? 'Loading...' : 'Apply Filter' }}
                        </Button>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-muted/50 rounded-lg p-4" v-if="coachingLog.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold">{{ coachingLog.length }}</div>
                            <div class="text-sm text-muted-foreground">Clients Coached</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ totalSessions }}</div>
                            <div class="text-sm text-muted-foreground">Total Sessions</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ totalHours }}h</div>
                            <div class="text-sm text-muted-foreground">Total Hours</div>
                        </div>
                    </div>
                </div>

                <!-- Coaching Log Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Client Name</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead class="text-right">Sessions</TableHead>
                                <TableHead class="text-right">Total Hours</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="coachingLog.length === 0">
                                <TableCell colspan="4" class="text-center py-8 text-muted-foreground">
                                    No coaching sessions found in the selected date range.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="entry in coachingLog" :key="entry.client.id">
                                <TableCell class="font-medium">
                                    {{ entry.client.name }}
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ entry.client.company?.name || 'No Company' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ entry.session_count }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ entry.total_hours }}h
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </GrowthTrackerLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CalendarDate, getLocalTimeZone, today, fromDate } from '@internationalized/date';
import { toDate } from 'reka-ui/date';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import { index } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingLogController';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Search } from 'lucide-vue-next';
import type { DateRange } from "reka-ui";

interface CoachingLogEntry {
    client: {
        id: number;
        name: string;
        company: {
            id: number;
            name: string;
        } | null;
    };
    session_count: number;
    total_hours: number;
}

interface CoachingLogFilters {
    start_date: string;
    end_date: string;
}

interface Props {
    coachingLog: CoachingLogEntry[];
    filters: CoachingLogFilters;
}

const props = defineProps<Props>();

const isLoading = ref(false);

// Initialize date range from props
const dateRange = ref<DateRange>({
    start: fromDate(new Date(props.filters.start_date), getLocalTimeZone()),
    end: fromDate(new Date(props.filters.end_date), getLocalTimeZone())
});

// Computed totals for summary
const totalSessions = computed(() => {
    return props.coachingLog.reduce((total, entry) => total + entry.session_count, 0);
});

const totalHours = computed(() => {
    const total = props.coachingLog.reduce((sum, entry) => sum + entry.total_hours, 0);
    return Math.round(total * 10) / 10; // Round to 1 decimal place
});

// Handle date range changes
const onDateRangeChange = (newRange: DateRange) => {
    dateRange.value = newRange;
};

// Apply filters function
const applyFilters = () => {
    isLoading.value = true;

    const startDate = toDate(dateRange.value.start).toISOString().split('T')[0];
    const endDate = toDate(dateRange.value.end).toISOString().split('T')[0];

    router.get(window.location.pathname, {
        start_date: startDate,
        end_date: endDate,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const breadcrumbs: BreadcrumbItem[] = [

    {
        title: 'Coaching Log',
        href: index().url
    },
];
</script>