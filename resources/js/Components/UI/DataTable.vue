<script setup lang="ts" generic="Row">
defineProps<{
    columns: { key: string; label: string }[]
    rows: Row[]
    clickableRows?: boolean
}>()

const emit = defineEmits<{
    'row-click': [row: Row]
}>()
</script>

<template>
    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-slate-800 bg-slate-950/70 text-xs uppercase tracking-wide text-slate-400">
                <tr>
                    <th v-for="column in columns" :key="column.key" class="px-5 py-4 font-semibold">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                <tr
                    v-for="(row, index) in rows"
                    :key="index"
                    :tabindex="clickableRows ? 0 : undefined"
                    :class="clickableRows ? 'cursor-pointer transition hover:bg-slate-800/60 focus:bg-slate-800/60 focus:outline-none' : undefined"
                    @click="clickableRows && emit('row-click', row)"
                    @keydown.enter.prevent="clickableRows && emit('row-click', row)"
                    @keydown.space.prevent="clickableRows && emit('row-click', row)"
                >
                    <slot name="row" :row="row" />
                </tr>
                <tr v-if="rows.length === 0">
                    <td :colspan="columns.length" class="px-5 py-12 text-center text-slate-400">
                        <slot name="empty">Belum ada data.</slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
