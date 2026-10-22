<script setup lang="ts">
import type { WeddingEvent } from '@/types'

defineProps<{ status: WeddingEvent['status'] }>()
</script>

<template>
    <span
        class="rounded-full px-3 py-1 text-xs font-semibold"
        :class="status === 'active' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-800 text-slate-400'"
    >
        {{ status === 'active' ? 'Aktif' : 'Diarsipkan' }}
    </span>
</template>
