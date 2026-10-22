<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { CalendarDays, Plus } from '@lucide/vue'
import { useEventFilters } from './Composables/useEventFilters.js'
import EventDeleteModal from './Components/EventDeleteModal.vue'
import EventFilters from './Components/EventFilters.vue'
import EventTable from './Components/EventTable.vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import type { CursorEventPage, WeddingEvent } from '@/types'

const props = defineProps<{
    events: CursorEventPage
    filters: { search: string; status: string }
    summary: { total: number; active: number }
}>()

defineOptions({ layout: AdminLayout })

const { query, status } = useEventFilters(() => props.filters)
const eventToArchive = ref<WeddingEvent | null>(null)
const archiving = ref(false)

function archiveEvent(): void {
    if (!eventToArchive.value || archiving.value) return
    archiving.value = true

    router.delete(`/events/${eventToArchive.value.id}`, {
        onSuccess: () => {
            eventToArchive.value = null
        },
        onFinish: () => {
            archiving.value = false
        },
    })
}

function showEvent(event: WeddingEvent): void {
    router.visit(`/events/${event.id}`)
}

function editEvent(event: WeddingEvent): void {
    router.visit(`/events/${event.id}/edit`)
}
</script>

<template>
    <Head title="Acara pernikahan" />

    <div class="space-y-8">
        <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div class="space-y-2">
                <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.16em] text-emerald-300">
                    <CalendarDays :size="16" aria-hidden="true" />
                    Pengelolaan
                </p>
                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">Acara pernikahan</h1>
                <p class="text-slate-400">Kelola detail acara, QR tamu, dan status sinkronisasi foto.</p>
            </div>
            <BaseButton href="/events/create">
                <Plus :size="18" aria-hidden="true" />
                Acara baru
            </BaseButton>
        </section>

        <section class="grid gap-5 rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:grid-cols-2">
            <StatCard label="Total acara" :value="summary.total" :icon="CalendarDays" />
            <StatCard label="Acara aktif" :value="summary.active" :icon="CalendarDays" tone="success" />
        </section>

        <section class="space-y-4">
            <EventFilters v-model:query="query" v-model:status="status" />
            <EventTable
                :events="events.data"
                :pagination="events"
                @show="showEvent"
                @edit="editEvent"
                @delete="eventToArchive = $event"
            />
        </section>

        <EventDeleteModal
            :event="eventToArchive"
            :processing="archiving"
            @close="eventToArchive = null"
            @confirm="archiveEvent"
        />
    </div>
</template>
