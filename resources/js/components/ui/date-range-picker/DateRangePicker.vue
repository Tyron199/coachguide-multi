<script setup lang="ts">
import type { DateValue } from "@internationalized/date"
import type { DateRange } from "reka-ui"
import type { Grid } from "reka-ui/date"
import type { Ref } from "vue"
import {
    CalendarDate,
    isEqualMonth,
    getLocalTimeZone,
    today,
} from "@internationalized/date"
import {
    Calendar as CalendarIcon,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next"
import { RangeCalendarRoot, useDateFormatter } from "reka-ui"
import { createMonth, toDate } from "reka-ui/date"
import { ref, watch, computed } from "vue"
import { cn } from "@/lib/utils"
import { Button, buttonVariants } from "@/components/ui/button"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover"
import {
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell,
} from "@/components/ui/range-calendar"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

interface DateRangePreset {
    label: string
    value: string
    getRange: () => DateRange
}

interface Props {
    modelValue?: DateRange
    presets?: DateRangePreset[]
    placeholder?: string
    class?: string
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: "Pick a date range",
    presets: () => [
        {
            label: "This Month",
            value: "this-month",
            getRange: () => {
                const now = today(getLocalTimeZone())
                const start = now.set({ day: 1 })
                const end = start.add({ months: 1 }).subtract({ days: 1 })
                return { start, end }
            }
        },
        {
            label: "Last Month",
            value: "last-month",
            getRange: () => {
                const now = today(getLocalTimeZone())
                const start = now.subtract({ months: 1 }).set({ day: 1 })
                const end = now.set({ day: 1 }).subtract({ days: 1 })
                return { start, end }
            }
        },
        {
            label: "Last 3 Months",
            value: "last-3-months",
            getRange: () => {
                const now = today(getLocalTimeZone())
                const start = now.subtract({ months: 2 }).set({ day: 1 })
                const end = now
                return { start, end }
            }
        },
        {
            label: "Last 6 Months",
            value: "last-6-months",
            getRange: () => {
                const now = today(getLocalTimeZone())
                const start = now.subtract({ months: 5 }).set({ day: 1 })
                const end = now
                return { start, end }
            }
        },
        {
            label: "Last 12 Months",
            value: "last-12-months",
            getRange: () => {
                const now = today(getLocalTimeZone())
                const start = now.subtract({ months: 11 }).set({ day: 1 })
                const end = now
                return { start, end }
            }
        }
    ]
})

const emit = defineEmits<{
    'update:modelValue': [value: DateRange]
}>()

// Initialize with default range (last 30 days) if no value provided
const defaultRange = computed(() => {
    const now = today(getLocalTimeZone())
    return {
        start: now.subtract({ days: 30 }),
        end: now
    }
})

const value = ref(props.modelValue || defaultRange.value) as Ref<DateRange>

const locale = ref("en-US")
const formatter = useDateFormatter(locale.value)

const placeholder = ref(value.value.start) as Ref<DateValue>
const secondMonthPlaceholder = ref(value.value.end) as Ref<DateValue>

const firstMonth = ref(
    createMonth({
        dateObj: placeholder.value,
        locale: locale.value,
        fixedWeeks: true,
        weekStartsOn: 0,
    }),
) as Ref<Grid<DateValue>>

const secondMonth = ref(
    createMonth({
        dateObj: secondMonthPlaceholder.value,
        locale: locale.value,
        fixedWeeks: true,
        weekStartsOn: 0,
    }),
) as Ref<Grid<DateValue>>

function updateMonth(reference: "first" | "second", months: number) {
    if (reference === "first") {
        placeholder.value = placeholder.value.add({ months })
    } else {
        secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
            months,
        })
    }
}

function selectPreset(presetValue: string) {
    const preset = props.presets.find(p => p.value === presetValue)
    if (preset) {
        const range = preset.getRange()
        value.value = range
        placeholder.value = range.start
        secondMonthPlaceholder.value = range.end
    }
}

// Watch for changes and emit to parent
watch(value, (newValue) => {
    emit('update:modelValue', newValue)
}, { deep: true })

watch(placeholder, (_placeholder) => {
    firstMonth.value = createMonth({
        dateObj: _placeholder,
        weekStartsOn: 0,
        fixedWeeks: false,
        locale: locale.value,
    })
    if (isEqualMonth(secondMonthPlaceholder.value, _placeholder)) {
        secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
            months: 1,
        })
    }
})

watch(secondMonthPlaceholder, (_secondMonthPlaceholder) => {
    secondMonth.value = createMonth({
        dateObj: _secondMonthPlaceholder,
        weekStartsOn: 0,
        fixedWeeks: false,
        locale: locale.value,
    })
    if (isEqualMonth(_secondMonthPlaceholder, placeholder.value))
        placeholder.value = placeholder.value.subtract({ months: 1 })
})

// Format the display text
const displayText = computed(() => {
    if (value.value.start && value.value.end) {
        return `${formatter.custom(toDate(value.value.start), { dateStyle: "medium" })} - ${formatter.custom(toDate(value.value.end), { dateStyle: "medium" })}`
    }
    if (value.value.start) {
        return formatter.custom(toDate(value.value.start), { dateStyle: "medium" })
    }
    return props.placeholder
})
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn(
                'w-[300px] justify-start text-left font-normal',
                !value.start && 'text-muted-foreground',
                props.class
            )">
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ displayText }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <div class="flex flex-col gap-2 p-2">
                <!-- Presets Dropdown -->
                <Select @update:model-value="selectPreset">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a preset" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="preset in presets" :key="preset.value" :value="preset.value">
                            {{ preset.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Range Calendar -->
                <RangeCalendarRoot v-slot="{ weekDays }" v-model="value" v-model:placeholder="placeholder" class="p-3">
                    <div class="flex flex-col gap-y-4 mt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
                        <!-- First Month -->
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <button :class="cn(
                                    buttonVariants({ variant: 'outline' }),
                                    'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
                                )" @click="updateMonth('first', -1)">
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <div :class="cn('text-sm font-medium')">
                                    {{ formatter.fullMonthAndYear(toDate(firstMonth.value)) }}
                                </div>
                                <button :class="cn(
                                    buttonVariants({ variant: 'outline' }),
                                    'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
                                )" @click="updateMonth('first', 1)">
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                            <RangeCalendarGrid>
                                <RangeCalendarGridHead>
                                    <RangeCalendarGridRow>
                                        <RangeCalendarHeadCell v-for="day in weekDays" :key="day" class="w-full">
                                            {{ day }}
                                        </RangeCalendarHeadCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridHead>
                                <RangeCalendarGridBody>
                                    <RangeCalendarGridRow v-for="(weekDates, index) in firstMonth.rows"
                                        :key="`weekDate-${index}`" class="mt-2 w-full">
                                        <RangeCalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()"
                                            :date="weekDate">
                                            <RangeCalendarCellTrigger :day="weekDate" :month="firstMonth.value" />
                                        </RangeCalendarCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridBody>
                            </RangeCalendarGrid>
                        </div>

                        <!-- Second Month -->
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <button :class="cn(
                                    buttonVariants({ variant: 'outline' }),
                                    'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
                                )" @click="updateMonth('second', -1)">
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <div :class="cn('text-sm font-medium')">
                                    {{ formatter.fullMonthAndYear(toDate(secondMonth.value)) }}
                                </div>
                                <button :class="cn(
                                    buttonVariants({ variant: 'outline' }),
                                    'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100',
                                )" @click="updateMonth('second', 1)">
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                            <RangeCalendarGrid>
                                <RangeCalendarGridHead>
                                    <RangeCalendarGridRow>
                                        <RangeCalendarHeadCell v-for="day in weekDays" :key="day" class="w-full">
                                            {{ day }}
                                        </RangeCalendarHeadCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridHead>
                                <RangeCalendarGridBody>
                                    <RangeCalendarGridRow v-for="(weekDates, index) in secondMonth.rows"
                                        :key="`weekDate-${index}`" class="mt-2 w-full">
                                        <RangeCalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()"
                                            :date="weekDate">
                                            <RangeCalendarCellTrigger :day="weekDate" :month="secondMonth.value" />
                                        </RangeCalendarCell>
                                    </RangeCalendarGridRow>
                                </RangeCalendarGridBody>
                            </RangeCalendarGrid>
                        </div>
                    </div>
                </RangeCalendarRoot>
            </div>
        </PopoverContent>
    </Popover>
</template>
