<template>

    <Head title="Supervision Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Supervision Log"
                    description="Track your supervision sessions and professional development"
                    :badge="`${props.supervisions.length} ${props.supervisions.length === 1 ? 'entry' : 'entries'}`">
                    <template #actions>
                        <!-- Delete Selected Button -->
                        <Button v-if="selectedSupervisions.length > 0" variant="destructive"
                            @click="handleDeleteSelected">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete ({{ selectedSupervisions.length }})
                        </Button>

                        <Link :href="SupervisionController.create().url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Entry
                        </Button>
                        </Link>
                    </template>
                </PageHeader>

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <Input v-model="searchQuery" placeholder="Search by supervisor, themes, or type..."
                            @input="debouncedSearch" class="max-w-sm" />
                    </div>
                    <div class="flex gap-2">
                        <Select v-model="supervisionType" @update:model-value="applyFilters">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="Developmental">Developmental</SelectItem>
                                <SelectItem value="Normative">Normative</SelectItem>
                                <SelectItem value="Restorative">Restorative</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button v-if="hasActiveFilters" variant="outline" @click="clearFilters">
                            <X class="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="bg-muted/50 rounded-lg p-4" v-if="props.supervisions.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold">{{ props.stats.total_entries }}</div>
                            <div class="text-sm text-muted-foreground">Total Sessions</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ props.stats.total_hours }}h</div>
                            <div class="text-sm text-muted-foreground">Total Hours</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ props.stats.total_attachments }}</div>
                            <div class="text-sm text-muted-foreground">Documents Uploaded</div>
                        </div>
                    </div>
                </div>

                <!-- Supervision Log Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12">
                                    <Checkbox :model-value="isAllSelected" @update:model-value="toggleSelectAll" />
                                </TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Supervisor</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Format</TableHead>
                                <TableHead>Themes Discussed</TableHead>
                                <TableHead class="text-right">Duration</TableHead>
                                <TableHead class="text-center">Files</TableHead>
                                <TableHead class="w-12"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="props.supervisions.length === 0">
                                <TableCell colspan="9" class="text-center py-8 text-muted-foreground">
                                    No supervision log entries yet. Start tracking your supervision sessions.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="sup in props.supervisions" :key="sup.id"
                                :class="{ 'bg-muted/50': selectedSupervisions.includes(sup.id) }">
                                <TableCell>
                                    <Checkbox :model-value="selectedSupervisions.includes(sup.id)"
                                        @update:model-value="(checked) => toggleSelection(sup.id, checked)" />
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ formatDate(sup.supervision_date) }}
                                </TableCell>
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ sup.supervisor_name }}</div>
                                        <div v-if="sup.supervisor_accreditation" class="text-xs text-muted-foreground">
                                            {{ sup.supervisor_accreditation }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ sup.supervision_type }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{{ sup.session_format }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="max-w-xs truncate" :title="sup.themes_discussed">
                                        {{ sup.themes_discussed }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ sup.duration_hours }}h
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge v-if="sup.attachments_count > 0" variant="default">
                                        {{ sup.attachments_count }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground text-sm">-</span>
                                </TableCell>
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="handleEdit(sup.id)">
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="handleDelete(sup.id)" class="text-destructive">
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
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
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import GrowthTrackerLayout from '@/layouts/growth-tracker/Layout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types';
import SupervisionController from '@/actions/App/Http/Controllers/Tenant/Coach/SupervisionController';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Plus, Trash2, X, MoreVertical, Pencil } from 'lucide-vue-next';
import { alertConfirm } from '@/plugins/alert';

interface Supervision {
    id: number;
    supervision_date: string;
    duration_minutes: number;
    duration_hours: number;
    supervisor_name: string;
    supervisor_contact: string | null;
    supervisor_accreditation: string | null;
    supervision_type: string;
    session_format: string;
    themes_discussed: string;
    reflections: string | null;
    action_points: string | null;
    ethical_considerations: string | null;
    impact_on_practice: string | null;
    attachments_count: number;
    created_at: string;
}

interface SupervisionStats {
    total_entries: number;
    total_hours: number;
    total_attachments: number;
}

interface SupervisionFilters {
    search?: string;
    supervision_type?: string;
}

interface Props {
    supervisions: Supervision[];
    stats: SupervisionStats;
    filters: SupervisionFilters;
}

const props = defineProps<Props>();

// Selection state
const selectedSupervisions = ref<number[]>([]);

// Filter state
const searchQuery = ref<string>(props.filters.search || '');
const supervisionType = ref<string>(props.filters.supervision_type || 'all');

// Computed
const isAllSelected = computed(() => {
    return props.supervisions.length > 0 && selectedSupervisions.value.length === props.supervisions.length;
});

const hasActiveFilters = computed(() => {
    return !!(props.filters.search || (props.filters.supervision_type && props.filters.supervision_type !== 'all'));
});

// Methods
const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        selectedSupervisions.value = props.supervisions.map(s => s.id);
    } else {
        selectedSupervisions.value = [];
    }
};

const toggleSelection = (id: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedSupervisions.value];

    if (isChecked) {
        if (!currentSelection.includes(id)) {
            currentSelection.push(id);
        }
    } else {
        const index = currentSelection.indexOf(id);
        if (index > -1) {
            currentSelection.splice(index, 1);
        }
    }

    selectedSupervisions.value = currentSelection;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (searchQuery.value) {
        params.search = searchQuery.value;
    }

    if (supervisionType.value && supervisionType.value !== 'all') {
        params.supervision_type = supervisionType.value;
    }

    router.get(SupervisionController.index().url, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Debounced search function
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const clearFilters = () => {
    searchQuery.value = '';
    supervisionType.value = 'all';
    router.get(SupervisionController.index().url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleEdit = (id: number) => {
    router.visit(SupervisionController.edit(id).url);
};

const handleDelete = async (id: number) => {
    const confirmed = await alertConfirm({
        title: 'Delete Entry',
        description: 'Are you sure you want to delete this supervision log entry? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(SupervisionController.destroy(id).url, {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Error deleting entry:', errors);
            }
        });
    }
};

const handleDeleteSelected = async () => {
    const confirmed = await alertConfirm({
        title: 'Delete Selected Entries',
        description: `Are you sure you want to delete ${selectedSupervisions.value.length} selected ${selectedSupervisions.value.length === 1 ? 'entry' : 'entries'}? This action cannot be undone.`,
        confirmText: `Delete (${selectedSupervisions.value.length})`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.post(SupervisionController.index().url + '/delete', {
            supervisions: selectedSupervisions.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedSupervisions.value = [];
            },
            onError: (errors) => {
                console.error('Error deleting entries:', errors);
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Growth Tracker',
        href: SupervisionController.index().url
    },
    {
        title: 'Supervision Log',
        href: SupervisionController.index().url
    },
];
</script>