<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset'
    variant?: 'primary' | 'secondary'
    loading?: boolean
    disabled?: boolean
    href?: string
}>(), {
    type: 'button',
    variant: 'primary',
    loading: false,
    disabled: false,
    href: undefined,
})

const baseClasses = 'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60'
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        :class="[
            baseClasses,
            variant === 'primary'
                ? 'bg-emerald-500 text-slate-950 hover:bg-emerald-400 focus:ring-emerald-400/20'
                : 'border border-slate-700 bg-slate-800 text-slate-200 hover:border-emerald-400/50 hover:bg-slate-700 focus:ring-emerald-400/15',
        ]"
    >
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="[
            baseClasses,
            variant === 'primary'
                ? 'bg-emerald-500 text-slate-950 hover:bg-emerald-400 focus:ring-emerald-400/20'
                : 'border border-slate-700 bg-slate-800 text-slate-200 hover:border-emerald-400/50 hover:bg-slate-700 focus:ring-emerald-400/15',
        ]"
    >
        <span
            v-if="loading"
            class="size-4 animate-spin rounded-full border-2 border-current/30 border-t-current"
            aria-hidden="true"
        />
        <slot />
    </button>
</template>
