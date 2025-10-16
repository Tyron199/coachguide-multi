<template>
    <div class="space-y-6">
        <!-- Framework Name -->
        <div class="space-y-2">
            <Label for="name" class="text-sm font-medium">
                Framework Name *
            </Label>
            <Input id="name" v-model="frameworkName" placeholder="e.g., My Custom GROW Model"
                :class="{ 'border-destructive': errors.name }" maxlength="255" />
            <div v-if="errors.name" class="text-sm text-destructive">
                {{ errors.name }}
            </div>
            <div class="text-xs text-muted-foreground">
                {{ modelValue.name.length }}/255 characters
            </div>
        </div>

        <!-- Framework Description -->
        <div class="space-y-2">
            <Label for="description" class="text-sm font-medium">
                Description *
            </Label>
            <Textarea id="description" v-model="frameworkDescription"
                placeholder="Describe what your framework helps coaches achieve and when to use it..."
                :class="{ 'border-destructive': errors.description }" rows="4" maxlength="1000" />
            <div v-if="errors.description" class="text-sm text-destructive">
                {{ errors.description }}
            </div>
            <div class="text-xs text-muted-foreground">
                {{ modelValue.description.length }}/1000 characters
            </div>
        </div>

        <!-- Category Selection -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">
                Category *
            </Label>
            <RadioGroup v-model="frameworkCategory" class="flex gap-6">
                <div class="flex items-center space-x-2">
                    <RadioGroupItem value="models" id="models" />
                    <Label for="models" class="font-normal cursor-pointer">
                        <div>
                            <div class="font-medium">Models</div>
                            <div class="text-xs text-muted-foreground">
                                Theoretical frameworks and structured approaches
                            </div>
                        </div>
                    </Label>
                </div>
                <div class="flex items-center space-x-2">
                    <RadioGroupItem value="tools" id="tools" />
                    <Label for="tools" class="font-normal cursor-pointer">
                        <div>
                            <div class="font-medium">Tools</div>
                            <div class="text-xs text-muted-foreground">
                                Practical techniques and exercises
                            </div>
                        </div>
                    </Label>
                </div>
            </RadioGroup>
            <div v-if="errors.category" class="text-sm text-destructive">
                {{ errors.category }}
            </div>
        </div>

        <!-- Subcategory -->
        <div class="space-y-2">
            <Label for="subcategory" class="text-sm font-medium">
                Subcategory
            </Label>
            <div class="flex gap-2">
                <Select v-model="selectedSubcategory" @update:model-value="handleSubcategoryChange">
                    <SelectTrigger class="flex-1">
                        <SelectValue placeholder="Choose existing or create new..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="custom">Create New Subcategory</SelectItem>
                        <SelectSeparator v-if="existingSubcategories.length > 0" />
                        <SelectItem v-for="subcategory in existingSubcategories" :key="subcategory"
                            :value="subcategory">
                            {{ subcategory }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Custom Subcategory Input -->
            <Input v-if="showCustomSubcategory" v-model="frameworkSubcategory" placeholder="Enter new subcategory..."
                maxlength="100" />

            <div v-if="errors.subcategory" class="text-sm text-destructive">
                {{ errors.subcategory }}
            </div>
            <div class="text-xs text-muted-foreground">
                Optional: Helps organize frameworks by specific focus area
            </div>
        </div>

        <!-- Best For Tags -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">
                Best For (Coaching Types)
            </Label>

            <!-- Tag Input -->
            <div class="flex gap-2">
                <Input v-model="newTag" placeholder="Add coaching type..." @keydown.enter.prevent="addTag"
                    maxlength="100" />
                <Button type="button" variant="outline" @click="addTag" :disabled="!newTag.trim()">
                    <Plus class="h-4 w-4" />
                </Button>
            </div>

            <!-- Existing Tags Dropdown -->
            <div v-if="existingBestForOptions.length > 0" class="space-y-2">
                <Select @update:model-value="addExistingTag">
                    <SelectTrigger>
                        <SelectValue placeholder="Or choose from existing types..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="option in availableExistingOptions" :key="option" :value="option">
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Selected Tags -->
            <div v-if="modelValue.best_for.length > 0" class="flex flex-wrap gap-2">
                <Badge v-for="tag in modelValue.best_for" :key="tag" variant="secondary"
                    class="flex items-center gap-1">
                    {{ tag }}
                    <button @click="removeTag(tag)" class="ml-1 hover:text-destructive">
                        <X class="h-3 w-3" />
                    </button>
                </Badge>
            </div>

            <div v-if="errors.best_for" class="text-sm text-destructive">
                {{ errors.best_for }}
            </div>
            <div class="text-xs text-muted-foreground">
                Press Enter or comma to add tags. Maximum 10 tags.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Plus, X } from 'lucide-vue-next';

interface Props {
    modelValue: {
        name: string;
        description: string;
        category: string;
        subcategory: string;
        best_for: string[];
    };
    existingSubcategories: string[];
    existingBestForOptions: string[];
    errors: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: Props['modelValue']];
}>();

// Local state
const selectedSubcategory = ref('');
const showCustomSubcategory = ref(false);
const newTag = ref('');

// Computed properties for two-way binding
const frameworkName = computed({
    get: () => props.modelValue.name,
    set: (value: string) => {
        emit('update:modelValue', { ...props.modelValue, name: value });
    }
});

const frameworkDescription = computed({
    get: () => props.modelValue.description,
    set: (value: string) => {
        emit('update:modelValue', { ...props.modelValue, description: value });
    }
});

const frameworkCategory = computed({
    get: () => props.modelValue.category,
    set: (value: string) => {
        emit('update:modelValue', { ...props.modelValue, category: value });
    }
});

const frameworkSubcategory = computed({
    get: () => props.modelValue.subcategory,
    set: (value: string) => {
        emit('update:modelValue', { ...props.modelValue, subcategory: value });
    }
});

// Computed
const availableExistingOptions = computed(() => {
    return props.existingBestForOptions.filter(option =>
        !props.modelValue.best_for.includes(option)
    );
});

// Methods
function handleSubcategoryChange(value: string): void {
    if (value === 'custom') {
        showCustomSubcategory.value = true;
        frameworkSubcategory.value = '';
    } else {
        showCustomSubcategory.value = false;
        frameworkSubcategory.value = value;
    }
}

function addTag(): void {
    const tag = newTag.value.trim();
    if (tag &&
        !props.modelValue.best_for.includes(tag) &&
        props.modelValue.best_for.length < 10) {
        const updatedBestFor = [...props.modelValue.best_for, tag];
        emit('update:modelValue', { ...props.modelValue, best_for: updatedBestFor });
        newTag.value = '';
    }
}

function addExistingTag(tag: string): void {
    if (tag &&
        !props.modelValue.best_for.includes(tag) &&
        props.modelValue.best_for.length < 10) {
        const updatedBestFor = [...props.modelValue.best_for, tag];
        emit('update:modelValue', { ...props.modelValue, best_for: updatedBestFor });
    }
}

function removeTag(tag: string): void {
    const updatedBestFor = props.modelValue.best_for.filter(t => t !== tag);
    emit('update:modelValue', { ...props.modelValue, best_for: updatedBestFor });
}

// Initialize subcategory selection on mount
onMounted(() => {
    // If there's an existing subcategory, set the dropdown to that value
    if (props.modelValue.subcategory) {
        // Check if the subcategory exists in the existing options
        if (props.existingSubcategories.includes(props.modelValue.subcategory)) {
            selectedSubcategory.value = props.modelValue.subcategory;
            showCustomSubcategory.value = false;
        } else {
            // It's a custom subcategory
            selectedSubcategory.value = 'custom';
            showCustomSubcategory.value = true;
        }
    }
});
</script>
