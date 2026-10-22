<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ArrowUpRight, CalendarDays, Camera, CloudCheck, Pencil } from '@lucide/vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import ReceptionQrCard from './Components/ReceptionQrCard.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import type { WeddingEvent } from '@/types'

defineProps<{
    event: WeddingEvent
    guestUrl: string
    guestUploadUrl: string
    qrCodeUrl: string | null
    coverPhotoUrl: string | null
}>()

defineOptions({ layout: AdminLayout })

function formatDate(date: string | null | undefined): string {
    return date
        ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(new Date(`${date.slice(0, 10)}T00:00:00`))
        : 'Belum diatur'
}
</script>

<template>
    <Head :title="event.title" />

    <div class="space-y-8">
        <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
            <div class="space-y-3">
                <Link href="/events" class="text-sm font-medium text-emerald-300 hover:text-emerald-200">
                    ← Kembali ke daftar acara
                </Link>
                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">{{ event.title }}</h1>
                <p class="text-slate-400">{{ event.bride_name }} &amp; {{ event.groom_name }}</p>
            </div>
            <BaseButton :href="`/events/${event.id}/edit`" variant="secondary">
                <Pencil :size="16" aria-hidden="true" />
                Edit acara
            </BaseButton>
        </section>

        <section class="grid gap-5 rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:grid-cols-3">
            <StatCard label="Tanggal acara" :value="formatDate(event.event_date)" :icon="CalendarDays" />
            <StatCard label="Total foto" :value="event.photos_count" :icon="Camera" />
            <StatCard label="Tersinkron ke Drive" :value="event.synced_photos_count" :icon="CloudCheck" tone="success" />
        </section>

        <section class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-lg font-semibold text-white">Tautan publik</h2>
                <p class="break-all rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 font-mono text-sm text-emerald-300">
                    {{ guestUploadUrl }}
                </p>
                <p class="text-sm text-slate-400">{{ event.location || 'Lokasi belum diatur' }}</p>
                <p class="text-sm text-slate-400">
                    Status:
                    <span :class="event.status === 'active' ? 'text-emerald-300' : 'text-slate-300'">
                        {{ event.status === 'active' ? 'Aktif' : 'Diarsipkan' }}
                    </span>
                </p>
                <p class="text-sm text-slate-400">
                    Folder Google Drive:
                    <code class="text-slate-200">{{ event.gdrive_folder_id || 'Belum dikonfigurasi' }}</code>
                </p>
                <a
                    v-if="event.status === 'active'"
                    :href="guestUrl"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex items-center gap-2 text-sm font-medium text-emerald-300 hover:text-emerald-200"
                >
                    Buka halaman tamu <ArrowUpRight :size="16" aria-hidden="true" />
                </a>
            </div>

            <aside class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-lg font-semibold text-white">QR Code</h2>
                <img
                    v-if="qrCodeUrl"
                    :src="qrCodeUrl"
                    :alt="`QR Code ${event.title}`"
                    class="mx-auto aspect-square w-full max-w-[240px] rounded-xl bg-white p-3"
                >
                <p v-else class="rounded-xl border border-slate-800 bg-slate-950 p-5 text-sm text-slate-400">
                    QR tidak aktif karena acara telah diarsipkan.
                </p>
                <ReceptionQrCard
                    v-if="qrCodeUrl"
                    :event="event"
                    :cover-photo-url="coverPhotoUrl"
                    :qr-code-url="qrCodeUrl"
                />
                <img
                    v-if="coverPhotoUrl"
                    :src="coverPhotoUrl"
                    :alt="`Foto cover ${event.title}`"
                    class="mt-5 aspect-video w-full rounded-xl object-cover"
                >
            </aside>
        </section>
    </div>
</template>
