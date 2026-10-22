<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { CalendarDays, Heart, Plus } from '@lucide/vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import EventCard from '@/Components/UI/EventCard.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import type { WeddingEvent } from '@/types'

const props = defineProps<{
    userName: string
    events: WeddingEvent[]
}>()

defineOptions({
    layout: AdminLayout,
})
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-8">
        <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div class="space-y-2">
                <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.16em] text-emerald-300">
                    <CalendarDays :size="16" aria-hidden="true" />
                    Ruang kerja Anda
                </p>
                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">Dashboard acara</h1>
                <p class="max-w-xl text-slate-400">Kelola acara dan pantau foto yang dibagikan para tamu.</p>
                <p class="text-sm text-slate-500">Halo, {{ props.userName }}</p>
            </div>
            <BaseButton href="/events/create">
                <Plus :size="18" aria-hidden="true" />
                Acara baru
            </BaseButton>
        </section>

        <section v-if="props.events.length" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <EventCard v-for="event in props.events" :key="event.id" :event="event" />
        </section>

        <section v-else class="rounded-2xl border border-dashed border-slate-700 bg-slate-900 px-6 py-16 text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-400/10 text-emerald-300">
                <Heart :size="24" aria-hidden="true" />
            </div>
            <h2 class="mt-5 text-lg font-semibold text-white">Belum ada acara</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-400">
                Acara yang Anda kelola akan tampil di sini bersama statistik foto dan sinkronisasi.
            </p>
            <BaseButton href="/events/create" class="mt-6">Buat acara pertama</BaseButton>
        </section>
    </div>
</template>
