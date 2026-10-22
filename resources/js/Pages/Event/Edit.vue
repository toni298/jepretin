<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import EventForm from './Components/EventForm.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import type { WeddingEvent } from '@/types'

defineProps<{ event: WeddingEvent }>()

defineOptions({ layout: AdminLayout })
</script>

<template>
    <Head :title="`Edit ${event.title}`" />

    <section class="mx-auto w-full max-w-3xl space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold tracking-tight text-white">Edit acara</h1>
            <p class="text-sm text-slate-400">Perbarui informasi acara tanpa mengubah tautan QR yang sudah dibagikan.</p>
        </header>
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg shadow-black/10 sm:p-8">
            <EventForm :event="event" />
        </div>
    </section>
</template>
