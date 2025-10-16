<template>

    <Head title="Training & Development" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <GrowthTrackerLayout>
            <div class="space-y-6">
                <PageHeader title="Training & Development" description="Track your professional development activities"
                    :badge="`${props.developments.length} ${props.developments.length === 1 ? 'entry' : 'entries'}`">
                    <template #actions>
                        <!-- Delete Selected Button -->
                        <Button v-if="selectedDevelopments.length > 0" variant="destructive"
                            @click="handleDeleteSelected">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete ({{ selectedDevelopments.length }})
                        </Button>

                        <Link :href="ProfessionalDevelopmentController.create().url">
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
                        <Input v-model="searchQuery" placeholder="Search by course title or provider..."
                            @input="debouncedSearch" class="max-w-sm" />
                    </div>
                    <div class="flex gap-2">
                        <Select v-model="trainingType" @update:model-value="applyFilters">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="Formal Training">Formal Training</SelectItem>
                                <SelectItem value="Webinar / workshop">Webinar / workshop</SelectItem>
                                <SelectItem value="Books/podcasts">Books/podcasts</SelectItem>
                                <SelectItem value="Self-study">Self-study</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button v-if="hasActiveFilters" variant="outline" @click="clearFilters">
                            <X class="h-4 w-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="bg-muted/50 rounded-lg p-4" v-if="props.developments.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold">{{ props.stats.total_entries }}</div>
                            <div class="text-sm text-muted-foreground">Total Entries</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ formatHours(props.stats.total_theory_hours) }}h</div>
                            <div class="text-sm text-muted-foreground">Theory Hours</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ formatHours(props.stats.total_practical_hours) }}h
                            </div>
                            <div class="text-sm text-muted-foreground">Practical Hours</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ formatHours(props.stats.total_hours) }}h</div>
                            <div class="text-sm text-muted-foreground">Combined Total</div>
                        </div>
                    </div>
                </div>

                <!-- Training & Development Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12">
                                    <Checkbox :model-value="isAllSelected" @update:model-value="toggleSelectAll" />
                                </TableHead>
                                <TableHead>Training Period</TableHead>
                                <TableHead>Training Type</TableHead>
                                <TableHead>Course Title</TableHead>
                                <TableHead>Provider</TableHead>
                                <TableHead class="text-center">Accredited</TableHead>
                                <TableHead class="text-right">Theory</TableHead>
                                <TableHead class="text-right">Practical</TableHead>
                                <TableHead class="text-right">Total Hours</TableHead>
                                <TableHead class="w-12"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="props.developments.length === 0">
                                <TableCell colspan="10" class="text-center py-8 text-muted-foreground">
                                    No training & development entries yet. Start tracking your professional development.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="dev in props.developments" :key="dev.id"
                                :class="{ 'bg-muted/50': selectedDevelopments.includes(dev.id) }">
                                <TableCell>
                                    <Checkbox :model-value="selectedDevelopments.includes(dev.id)"
                                        @update:model-value="(checked) => toggleSelection(dev.id, checked)" />
                                </TableCell>
                                <TableCell class="font-medium">
                                    <div class="text-sm">
                                        {{ (dev.date_from) }} to {{ (dev.date_to) }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ dev.training_type }}</Badge>
                                </TableCell>
                                <TableCell>
                                    {{ dev.course_title }}
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ dev.training_provider }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge v-if="dev.accredited" variant="default">Yes</Badge>
                                    <span v-else class="text-muted-foreground text-sm">No</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ dev.total_hours_theory || '-' }}h
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ dev.total_hours_practical || '-' }}h
                                </TableCell>
                                <TableCell class="text-right font-medium">
                                    {{ formatHours(dev.total_hours) }}h
                                </TableCell>
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="handleEdit(dev.id)">
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="handleDelete(dev.id)" class="text-destructive">
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
import ProfessionalDevelopmentController from '@/actions/App/Http/Controllers/Tenant/Coach/ProfessionalDevelopmentController';
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

interface ProfessionalDevelopment {
    id: number;
    date_from: string;
    date_to: string;
    training_type: string;
    training_provider: string;
    course_title: string;
    accredited: boolean;
    total_hours_theory: number | null;
    total_hours_practical: number | null;
    total_hours: number;
    created_at: string;
}

interface DevelopmentStats {
    total_entries: number;
    total_theory_hours: number;
    total_practical_hours: number;
    total_hours: number;
}

interface DevelopmentFilters {
    search?: string;
    training_type?: string;
}

interface Props {
    developments: ProfessionalDevelopment[];
    stats: DevelopmentStats;
    filters: DevelopmentFilters;
}

const props = defineProps<Props>();

// Selection state
const selectedDevelopments = ref<number[]>([]);

// Filter state
const searchQuery = ref<string>(props.filters.search || '');
const trainingType = ref<string>(props.filters.training_type || 'all');

// Computed
const isAllSelected = computed(() => {
    return props.developments.length > 0 && selectedDevelopments.value.length === props.developments.length;
});

const hasActiveFilters = computed(() => {
    return !!(props.filters.search || (props.filters.training_type && props.filters.training_type !== 'all'));
});

// Methods
const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    if (isChecked) {
        selectedDevelopments.value = props.developments.map(d => d.id);
    } else {
        selectedDevelopments.value = [];
    }
};

const toggleSelection = (id: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true;
    const currentSelection = [...selectedDevelopments.value];

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

    selectedDevelopments.value = currentSelection;
};

const formatHours = (hours: number | null) => {
    if (!hours) return '0';
    return Math.round(hours * 10) / 10;
};

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (searchQuery.value) {
        params.search = searchQuery.value;
    }

    if (trainingType.value && trainingType.value !== 'all') {
        params.training_type = trainingType.value;
    }

    router.get(ProfessionalDevelopmentController.index().url, params, {
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
    trainingType.value = 'all';
    router.get(ProfessionalDevelopmentController.index().url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleEdit = (id: number) => {
    router.visit(ProfessionalDevelopmentController.edit(id).url);
};

const handleDelete = async (id: number) => {
    const confirmed = await alertConfirm({
        title: 'Delete Entry',
        description: 'Are you sure you want to delete this training & development entry? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'destructive'
    });

    if (confirmed) {
        router.delete(ProfessionalDevelopmentController.destroy(id).url, {
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
        description: `Are you sure you want to delete ${selectedDevelopments.value.length} selected ${selectedDevelopments.value.length === 1 ? 'entry' : 'entries'}? This action cannot be undone.`,
        confirmText: `Delete (${selectedDevelopments.value.length})`,
        variant: 'destructive'
    });

    if (confirmed) {
        router.post(ProfessionalDevelopmentController.index().url + '/delete', {
            developments: selectedDevelopments.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedDevelopments.value = [];
            },
            onError: (errors) => {
                console.error('Error deleting entries:', errors);
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [

    {
        title: 'Training & Development',
        href: ProfessionalDevelopmentController.index().url
    },
];
</script>