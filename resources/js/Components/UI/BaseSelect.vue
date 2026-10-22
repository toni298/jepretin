<script setup lang="ts">
defineProps<{
    id: string
    name: string
    label: string
    modelValue: string
    options: { label: string; value: string }[]
    error?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <div class="flex flex-col gap-3">
        <label :for="id" class="block text-sm font-medium text-slate-200">{{ label }}</label>
        <select
            :id="id"
            :name="name"
            :value="modelValue"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? `${id}-error` : undefined"
            class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
            @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-300">{{ error }}</p>
    </div>
</template>
