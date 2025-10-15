<template>
    <div class="space-y-4">
        <!-- Filter Controls -->
        <TaskFilters :clients="props.clients" :filters="props.filters" />

        <!-- Table View -->
        <div class="rounded-md border hidden md:block">
            <Table>
                <TableHeader>
                    <TableRow>
                        <SortableTableHead label="Title" sort-key="title"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Client" sort-key="client"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Deadline" sort-key="deadline"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <SortableTableHead label="Status" sort-key="status"
                            :current-sort="props.filters.sort_by" :current-direction="props.filters.sort_direction"
                            @sort="handleSort" />
                        <TableHead>Evidence</TableHead>
                        <TableHead>Session</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in props.tasks.data" :key="task.id"
                        :class="{ 'bg-destructive/5 border-destructive/20': isOverdue(task) }">
                        <TableCell class="font-medium">
                            <div class="flex items-center gap-2">
                                <Link :href="showTask(task.id).url" class="hover:underline">
                                {{ task.title }}
                                </Link>
                                <Badge v-if="isOverdue(task)" variant="destructive" class="text-xs">
                                    OVERDUE
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Link v-if="task.client" :href="clientRoutes.show(task.client.id).url"
                                class="hover:underline">
                            {{ task.client.name }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <div v-if="task.deadline" class="space-y-1">
                                <div class="font-medium">{{ formatDate(task.deadline) }}</div>
                                <div class="text-sm text-muted-foreground">{{ getDeadlineLabel(task.deadline) }}</div>
                            </div>
                            <span v-else class="text-muted-foreground">No deadline</span>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="getStatusVariant(task.status)">
                                {{ formatStatus(task.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <Badge v-if="task.evidence_required" variant="outline" class="text-xs">
                                <FileCheck class="mr-1 h-3 w-3" />
                                Required
                            </Badge>
                            <span v-else class="text-muted-foreground text-sm">-</span>
                        </TableCell>
                        <TableCell>
                            <Link v-if="task.session_id" :href="sessionRoutes.show(task.session_id).url"
                                class="text-primary hover:underline text-sm inline-flex items-center gap-1">
                            <ExternalLink class="h-3 w-3" />
                            View Session
                            </Link>
                            <span v-else class="text-muted-foreground text-sm">-</span>
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="props.tasks.data.length === 0" :colspan="6">
                        <div class="text-center py-8">
                            <p class="text-muted-foreground">No tasks found</p>
                            <p class="text-sm text-muted-foreground mt-1">
                                Try adjusting your search or filter criteria
                            </p>
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <!-- Grid for mobile -->
        <div class="md:hidden space-y-4">
            <Card v-for="task in props.tasks.data" :key="task.id"
                :class="{ 'border-destructive bg-destructive/5': isOverdue(task) }">
                <CardHeader>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <CardTitle class="truncate">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <Link :href="showTask(task.id).url" class="hover:underline">
                                    {{ task.title }}
                                    </Link>
                                    <Badge v-if="isOverdue(task)" variant="destructive" class="text-xs">
                                        OVERDUE
                                    </Badge>
                                </div>
                            </CardTitle>
                            <p class="text-sm text-muted-foreground truncate">
                                <Link v-if="task.client" :href="clientRoutes.show(task.client.id).url"
                                    class="hover:underline">
                                {{ task.client.name }}
                                </Link>
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Deadline</span>
                            <div v-if="task.deadline" class="font-medium text-right">
                                <div>{{ formatDate(task.deadline) }}</div>
                                <div class="text-xs text-muted-foreground">{{ getDeadlineLabel(task.deadline) }}</div>
                            </div>
                            <span v-else class="text-muted-foreground">No deadline</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="getStatusVariant(task.status)">
                                {{ formatStatus(task.status) }}
                            </Badge>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Evidence</span>
                            <Badge v-if="task.evidence_required" variant="outline" class="text-xs">
                                <FileCheck class="mr-1 h-3 w-3" />
                                Required
                            </Badge>
                            <span v-else class="text-muted-foreground">-</span>
                        </div>

                        <div v-if="task.session_id" class="flex items-center justify-between gap-4">
                            <span class="text-muted-foreground">Session</span>
                            <Link :href="sessionRoutes.show(task.session_id).url"
                                class="text-primary hover:underline inline-flex items-center gap-1">
                            <ExternalLink class="h-3 w-3" />
                            View
                            </Link>
                        </div>

                        <div class="pt-2">
                            <Link :href="showTask(task.id).url" class="text-primary hover:underline">
                            View details
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="props.tasks.last_page > 1" class="flex justify-center">
            <PaginationComplete :total="props.tasks.total" :items-per-page="props.tasks.per_page"
                :page="props.tasks.current_page" @update:page="updatePage" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { ExternalLink, FileCheck } from 'lucide-vue-next';

import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
    SortableTableHead,
} from '@/components/ui/table';
import { PaginationComplete } from '@/components/ui/pagination';
import TaskFilters from '@/components/TaskFilters.vue';
import clientRoutes from '@/routes/tenant/coach/clients';
import sessionRoutes from '@/routes/tenant/coach/coaching-sessions';
import { show as showTask } from '@/actions/App/Http/Controllers/Tenant/Coach/CoachingTaskController';

interface CoachingTask {
    id: number;
    title: string;
    description: string | null;
    client: {
        id: number;
        name: string;
    };
    session_id: number | null;
    deadline: string | null;
    status: 'pending' | 'in_progress' | 'review' | 'completed' | 'cancelled';
    evidence_required: boolean;
    completed_at: string | null;
    created_at: string;
}

interface PaginatedTasks {
    data: CoachingTask[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface TaskFilters {
    search?: string;
    client_id?: number | null;
    status?: string | null;
    evidence_required?: boolean | null;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    view: 'pending' | 'overdue' | 'completed';
}

interface Props {
    tasks: PaginatedTasks;
    clients?: { id: number; name: string }[];
    filters: TaskFilters;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getDeadlineLabel = (dateString: string) => {
    const deadline = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    deadline.setHours(0, 0, 0, 0);
    
    const diffTime = deadline.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) return 'Overdue';
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    if (diffDays <= 7) return `In ${diffDays} days`;
    return '';
};

const isOverdue = (task: CoachingTask) => {
    if (!task.deadline) return false;
    if (task.status === 'completed' || task.status === 'cancelled') return false;
    return new Date(task.deadline) < new Date();
};

const formatStatus = (status: string) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'in_progress': return 'In Progress';
        case 'review': return 'In Review';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending': return 'outline' as const;
        case 'in_progress': return 'secondary' as const;
        case 'review': return 'default' as const;
        case 'completed': return 'default' as const;
        case 'cancelled': return 'destructive' as const;
        default: return 'outline' as const;
    }
};

const handleSort = (sortKey: string, direction: 'asc' | 'desc') => {
    // Preserve existing query parameters
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    currentParams.forEach((value, key) => {
        if (key !== 'sort_by' && key !== 'sort_direction') {
            preservedParams[key] = value;
        }
    });

    router.get(window.location.pathname, {
        ...preservedParams,
        sort_by: sortKey,
        sort_direction: direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updatePage = (page: number) => {
    // Preserve existing query parameters
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams: Record<string, string> = {};

    currentParams.forEach((value, key) => {
        if (key !== 'page') {
            preservedParams[key] = value;
        }
    });

    router.get(window.location.pathname, {
        ...preservedParams,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

