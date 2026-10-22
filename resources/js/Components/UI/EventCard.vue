<script setup lang="ts">
import { Camera, CloudCheck } from '@lucide/vue'
import { Link } from '@inertiajs/vue3'
import StatCard from '@/Components/UI/StatCard.vue'
import type { WeddingEvent } from '@/types'

defineProps<{
    event: WeddingEvent
}>()
</script>

<template>
    <article class="flex min-h-64 flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:border-slate-700 hover:shadow-xl">
        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold leading-snug text-white">
                        <Link :href="`/events/${event.id}`" class="hover:text-emerald-300">{{ event.title }}</Link>
                    </h2>
                    <p class="text-sm text-slate-400">{{ event.bride_name }} &amp; {{ event.groom_name }}</p>
                </div>
                <span
                    class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="event.status === 'active' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-800 text-slate-400'"
                >
                    {{ event.status === 'active' ? 'Aktif' : 'Diarsipkan' }}
                </span>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">QR acara</p>
                <code class="mt-1 block break-all text-sm font-medium text-slate-200">/e/{{ event.slug }}</code>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-800 pt-5">
            <StatCard label="Total foto" :value="event.photos_count" :icon="Camera" />
            <StatCard label="Tersinkron ke Drive" :value="event.synced_photos_count" tone="success" :icon="CloudCheck" />
        </div>
    </article>
</template>
