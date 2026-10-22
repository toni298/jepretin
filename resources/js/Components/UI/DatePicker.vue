<script setup lang="ts">
import { computed } from 'vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import { endOfMonth, endOfToday, startOfMonth, startOfToday, subDays, subMonths } from 'date-fns'
import { id } from 'date-fns/locale'

type DatePickerValue = string | string[] | null

const props = withDefaults(defineProps<{
    id: string
    name: string
    label: string
    modelValue: DatePickerValue
    mode?: 'single' | 'range'
    error?: string
    placeholder?: string
    required?: boolean
    disabled?: boolean
}>(), {
    mode: 'single',
    error: '',
    placeholder: undefined,
    required: false,
    disabled: false,
})

const emit = defineEmits<{
    'update:modelValue': [value: DatePickerValue]
}>()

const isRange = computed(() => props.mode === 'range')
const pickerValue = computed<DatePickerValue>({
    get: () => props.modelValue === '' ? null : props.modelValue,
    set: (value) => emit('update:modelValue', value ?? ''),
})
const placeholder = computed(() => props.placeholder ?? (isRange.value ? 'Pilih rentang tanggal' : 'Pilih tanggal'))
const inputAttributes = computed(() => ({
    id: props.id,
    name: props.name,
    required: props.required,
    autocomplete: 'off',
    clearable: !props.required,
    alwaysClearable: !props.required,
    'aria-invalid': props.error ? 'true' : 'false',
    'aria-describedby': props.error ? `${props.id}-error` : undefined,
}))
const inputFormats = { input: 'dd MMMM yyyy' }
const rangePresets = computed(() => {
    if (!isRange.value) return []

    const today = startOfToday()
    const yesterday = endOfToday()
    const previousMonth = subMonths(today, 1)

    return [
        { label: 'Hari ini', value: [today, yesterday] },
        { label: '7 hari terakhir', value: [subDays(today, 6), yesterday] },
        { label: '30 hari terakhir', value: [subDays(today, 29), yesterday] },
        { label: 'Bulan ini', value: [startOfMonth(today), endOfMonth(today)] },
        { label: 'Bulan lalu', value: [startOfMonth(previousMonth), endOfMonth(previousMonth)] },
    ]
})
</script>

<template>
    <div class="flex flex-col gap-3">
        <label :for="id" class="block text-sm font-medium text-slate-200">{{ label }}</label>
        <VueDatePicker
            v-model="pickerValue"
            :range="isRange ? { partialRange: false, showLastInRange: true } : false"
            :multi-calendars="isRange ? 2 : false"
            :auto-apply="!isRange"
            :action-row="{
                showSelect: isRange,
                showCancel: isRange,
                showNow: false,
                showPreview: true,
                selectBtnLabel: 'Terapkan',
                cancelBtnLabel: 'Batal',
            }"
            :preset-dates="rangePresets"
            :formats="inputFormats"
            model-type="yyyy-MM-dd"
            :locale="id"
            :week-start="1"
            :dark="true"
            :placeholder="placeholder"
            :disabled="disabled"
            :input-attrs="inputAttributes"
            :aria-labels="{ input: label, menu: label }"
            :enable-time-picker="false"
            :teleport="true"
            :ui="{
                input: error
                    ? 'jepretin-date-input jepretin-date-input-error'
                    : 'jepretin-date-input',
                menu: 'jepretin-date-menu',
                calendar: 'jepretin-date-calendar',
            }"
        />
        <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-300">
            {{ error }}
        </p>
    </div>
</template>
