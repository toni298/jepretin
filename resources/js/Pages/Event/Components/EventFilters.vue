<script setup lang="ts">
defineProps<{
    query: string
    status: string
}>()

const emit = defineEmits<{
    'update:query': [value: string]
    'update:status': [value: string]
}>()

const statusOptions = [
    { label: 'Semua status', value: '' },
    { label: 'Aktif', value: 'active' },
    { label: 'Diarsipkan', value: 'archived' },
]
</script>

<template>
    <div class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4 sm:grid-cols-[1fr_220px]">
        <div class="space-y-2">
            <label for="event-search" class="text-sm font-medium text-slate-200">Cari acara</label>
            <input
                id="event-search"
                :value="query"
                type="search"
                placeholder="Nama acara atau mempelai"
                class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                @input="emit('update:query', ($event.target as HTMLInputElement).value)"
            >
        </div>
        <div class="space-y-2">
            <label for="event-status-filter" class="text-sm font-medium text-slate-200">Status acara</label>
            <select
                id="event-status-filter"
                :value="status"
                class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                @change="emit('update:status', ($event.target as HTMLSelectElement).value)"
            >
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
        </div>
    </div>
</template>
