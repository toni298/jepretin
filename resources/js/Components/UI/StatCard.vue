<script setup lang="ts">
import type { Component } from 'vue'

withDefaults(defineProps<{
    label: string
    value: number | string
    tone?: 'default' | 'success'
    icon?: Component
}>(), {
    tone: 'default',
    icon: undefined,
})
</script>

<template>
    <div class="flex items-center gap-3">
        <span
            v-if="icon"
            class="grid size-9 shrink-0 place-items-center rounded-xl"
            :class="tone === 'success' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-800 text-slate-300'"
        >
            <component :is="icon" :size="17" aria-hidden="true" />
        </span>
        <div>
            <p class="text-xs text-slate-400">{{ label }}</p>
            <p
                class="mt-1 text-xl font-semibold"
                :class="tone === 'success' ? 'text-emerald-300' : 'text-white'"
            >
                {{ value }}
            </p>
        </div>
    </div>
</template>
