<template>
    <div class="space-y-6">
        <!-- Instructions -->
        <div class="bg-muted/50 rounded-lg p-4">
            <h3 class="font-medium mb-2">Building Your Framework Questions</h3>
            <p class="text-sm text-muted-foreground">
                Add questions that will guide coaches through your framework. Each question becomes a text area
                where coaches can record insights and responses during coaching sessions.
            </p>
        </div>

        <!-- Field List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium">Framework Questions</h3>
                <Button @click="addField" :disabled="fields.length >= 20">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Question
                </Button>
            </div>

            <!-- Empty State -->
            <div v-if="fields.length === 0" class="text-center py-8 border-2 border-dashed border-muted rounded-lg">
                <FileQuestion class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-medium mb-2">No Questions Yet</h3>
                <p class="text-muted-foreground mb-4">Add your first question to get started.</p>
                <Button @click="addField">
                    <Plus class="mr-2 h-4 w-4" />
                    Add First Question
                </Button>
            </div>

            <!-- Field Items -->
            <draggable v-else v-model="fields" handle=".drag-handle" item-key="id" class="space-y-3">
                <template #item="{ element: field, index }">
                    <Card class="relative">
                        <CardContent class="p-4">
                            <div class="flex items-start gap-4">
                                <!-- Drag Handle -->
                                <div class="drag-handle cursor-move flex-shrink-0 mt-2">
                                    <GripVertical class="h-4 w-4 text-muted-foreground" />
                                </div>

                                <!-- Field Content -->
                                <div class="flex-1 space-y-4">
                                    <!-- Field Header -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Badge variant="outline" class="text-xs">
                                                Question {{ index + 1 }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Button variant="ghost" size="sm" @click="editField(field)">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="deleteField(field.id)"
                                                class="text-destructive hover:text-destructive">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- Field Preview -->
                                    <div class="space-y-2">
                                        <div class="font-medium">
                                            {{ field.title || 'Untitled Question' }}
                                        </div>
                                        <div v-if="field.description" class="text-sm text-muted-foreground">
                                            {{ field.description }}
                                        </div>

                                        <!-- Validation Errors -->
                                        <div v-if="getFieldErrors(field.id).length > 0" class="space-y-1">
                                            <div v-for="error in getFieldErrors(field.id)" :key="error"
                                                class="text-xs text-destructive flex items-center gap-1">
                                                <AlertCircle class="h-3 w-3" />
                                                {{ error }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </template>
            </draggable>
        </div>

        <!-- Field Limit Warning -->
        <div v-if="fields.length >= 18" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-amber-800">
                <AlertTriangle class="h-4 w-4" />
                <span class="text-sm font-medium">
                    Approaching field limit ({{ fields.length }}/20)
                </span>
            </div>
        </div>

        <!-- Global Field Errors -->
        <div v-if="errors.fields" class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
            <div class="flex items-center gap-2 text-destructive">
                <AlertCircle class="h-4 w-4" />
                <span class="text-sm font-medium">{{ errors.fields }}</span>
            </div>
        </div>
    </div>

    <!-- Field Edit Modal -->
    <Dialog :open="editModalOpen" @update:open="editModalOpen = $event">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>
                    {{ editingField ? 'Edit Question' : 'Add Question' }}
                </DialogTitle>
                <DialogDescription>
                    Configure the question that coaches will see during coaching sessions.
                </DialogDescription>
            </DialogHeader>

            <div v-if="editingField" class="space-y-4">
                <!-- Question Title -->
                <div class="space-y-2">
                    <Label for="edit-title" class="text-sm font-medium">
                        Question/Prompt *
                    </Label>
                    <Input id="edit-title" v-model="editingField.title"
                        placeholder="e.g., What is your current situation?" maxlength="255" />
                    <div class="text-xs text-muted-foreground">
                        {{ editingField.title.length }}/255 characters
                    </div>
                </div>

                <!-- Helper Text -->
                <div class="space-y-2">
                    <Label for="edit-description" class="text-sm font-medium">
                        Helper Text (Optional)
                    </Label>
                    <Textarea id="edit-description" v-model="editingField.description"
                        placeholder="Provide guidance or examples for coaches..." rows="3" maxlength="500" />
                    <div class="text-xs text-muted-foreground">
                        {{ editingField.description.length }}/500 characters
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="editModalOpen = false">
                    Cancel
                </Button>
                <Button @click="saveField" :disabled="!canSaveField">
                    {{ editingField?.id ? 'Update' : 'Add' }} Question
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Plus,
    GripVertical,
    Edit,
    Trash2,
    FileQuestion,
    AlertCircle,
    AlertTriangle
} from 'lucide-vue-next';

interface Field {
    id: string;
    key: string;
    title: string;
    description: string;
}

interface Props {
    fields: Field[];
    errors: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:fields': [fields: Field[]];
}>();

// Local state
const editModalOpen = ref(false);
const editingField = ref<Field | null>(null);

// Computed
const fields = computed({
    get: () => props.fields,
    set: (value) => emit('update:fields', value)
});

const canSaveField = computed(() => {
    return editingField.value &&
        editingField.value.title.trim() !== '';
});

// Methods
function addField(): void {
    const newField: Field = {
        id: crypto.randomUUID(),
        key: crypto.randomUUID(), // Auto-generate UUID for field key
        title: '',
        description: ''
    };

    editingField.value = newField;
    editModalOpen.value = true;
}

function editField(field: Field): void {
    editingField.value = { ...field };
    editModalOpen.value = true;
}

function deleteField(fieldId: string): void {
    const index = fields.value.findIndex(f => f.id === fieldId);
    if (index > -1) {
        fields.value.splice(index, 1);
    }
}

function saveField(): void {
    if (!editingField.value || !canSaveField.value) return;

    const existingIndex = fields.value.findIndex(f => f.id === editingField.value!.id);

    if (existingIndex > -1) {
        // Update existing field
        fields.value[existingIndex] = { ...editingField.value };
    } else {
        // Add new field
        fields.value.push({ ...editingField.value });
    }

    editModalOpen.value = false;
    editingField.value = null;
}


function getFieldErrors(fieldId: string): string[] {
    const errors: string[] = [];
    const field = fields.value.find(f => f.id === fieldId);

    if (!field) return errors;

    if (!field.title.trim()) {
        errors.push('Missing question text');
    }

    return errors;
}
</script>
