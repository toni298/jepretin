<script setup lang="ts">
withDefaults(defineProps<{
    id: string
    name: string
    modelValue: string
    label: string
    type?: 'text' | 'email' | 'password' | 'number' | 'date'
    autocomplete?: string
    placeholder?: string
    error?: string
    required?: boolean
    autofocus?: boolean
}>(), {
    type: 'text',
    autocomplete: undefined,
    placeholder: '',
    error: '',
    required: false,
    autofocus: false,
})

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <div class="flex flex-col gap-3">
        <label :for="id" class="block text-sm font-medium text-slate-200">{{ label }}</label>
        <input
            :id="id"
            :name="name"
            :type="type"
            :value="modelValue"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :required="required"
            :autofocus="autofocus"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? `${id}-error` : undefined"
            class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        >
        <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-300">
            {{ error }}
        </p>
    </div>
</template>
