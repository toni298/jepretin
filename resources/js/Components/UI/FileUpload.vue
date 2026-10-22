<script setup lang="ts">
withDefaults(defineProps<{
    id: string
    label: string
    modelValue: File | null
    error?: string
    progress?: number
    accept?: string
}>(), {
    error: '',
    progress: 0,
    accept: 'image/jpeg,image/png,image/webp',
})

const emit = defineEmits<{
    'update:modelValue': [file: File | null]
}>()
</script>

<template>
    <div class="flex flex-col gap-3">
        <label :for="id" class="block text-sm font-medium text-slate-200">{{ label }}</label>
        <input
            :id="id"
            type="file"
            :accept="accept"
            :aria-invalid="Boolean(error)"
            :aria-describedby="error ? `${id}-error` : undefined"
            class="block w-full cursor-pointer rounded-xl border border-slate-700 bg-slate-950/70 text-sm text-slate-300 file:mr-4 file:border-0 file:bg-slate-800 file:px-4 file:py-3 file:font-medium file:text-emerald-300 hover:file:bg-slate-700"
            @change="emit('update:modelValue', ($event.target as HTMLInputElement).files?.item(0) ?? null)"
        >
        <p v-if="modelValue" class="text-xs text-slate-400">{{ modelValue.name }}</p>
        <p v-if="progress > 0 && progress < 100" class="text-xs text-emerald-300">
            Mengunggah {{ progress }}%
        </p>
        <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-300">{{ error }}</p>
    </div>
</template>
