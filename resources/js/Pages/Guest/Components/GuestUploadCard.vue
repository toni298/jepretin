<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { CalendarDays, Camera, CheckCircle2, ImagePlus, MapPin, Trash2, Upload, X } from '@lucide/vue'
import { useGuestPhotoUpload } from '@/Pages/Guest/Composables/useGuestPhotoUpload'

const props = defineProps<{
    event: {
        slug: string
        uploadToken?: string | null
        title: string
        brideName: string
        groomName: string
        eventDate: string | null
        location: string | null
        coverPhotoUrl: string | null
        galleryUrl: string
    }
}>()

const { draftPhotos, totalPhotosLabel, error, success, uploading, compressing, progress, addFiles, removeDraft, uploadAll } =
    useGuestPhotoUpload(props.event.slug, props.event.uploadToken ?? undefined)

const cameraInput = ref<HTMLInputElement | null>(null)
const galleryInput = ref<HTMLInputElement | null>(null)
const isMobile = ref(false)

const eventDate = computed(() => {
    if (!props.event.eventDate) return 'Tanggal akan diumumkan'

    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${props.event.eventDate}T12:00:00`))
})

onMounted(() => {
    isMobile.value = /android|iphone|ipod|mobile|windows phone/i.test(navigator.userAgent)
        || (/Macintosh/i.test(navigator.userAgent) && navigator.maxTouchPoints > 1)
})

async function onFileChange(event: Event): Promise<void> {
    const input = event.target
    if (!(input instanceof HTMLInputElement)) return

    await addFiles(input.files)
    input.value = ''
}
</script>

<template>
    <article class="relative isolate w-full max-w-lg overflow-hidden rounded-[2rem] border border-[#d8c9a6] bg-[#F9F8F6] shadow-[0_28px_90px_-32px_rgba(63,48,28,0.45)]">
        <div v-if="event.coverPhotoUrl" aria-hidden="true" class="absolute inset-0 -z-10 overflow-hidden">
            <img :src="event.coverPhotoUrl" alt="" class="absolute inset-0 size-full scale-110 object-cover blur-md">
            <div class="absolute inset-0 bg-slate-950/75" />
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/20 via-transparent to-slate-950/40" />
        </div>

        <div v-else aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <svg class="absolute -right-4 -top-3 h-48 w-40 text-[#b5a277]/45" viewBox="0 0 160 190" fill="none">
                <path d="M156 4C104 23 72 58 58 112c-8 30-22 52-48 72M119 29c-21 0-39 11-49 29 20 5 38-5 49-29ZM91 58c-20-2-39 7-52 24 19 8 39 1 52-24ZM68 89c-20-5-40 1-56 16 17 11 38 7 56-16ZM48 121c-18-7-39-5-57 7 15 13 36 12 57-7ZM77 67c-1-20 8-39 25-52 7 20 0 39-25 52ZM58 99c-4-20 2-40 17-56 10 18 6 39-17 56ZM37 132c-7-19-5-40 7-58 13 16 12 37-7 58Z" stroke="currentColor" stroke-width="1.2" />
            </svg>
            <div class="absolute inset-4 rounded-[1.5rem] border border-[#b5a277]/35" />
        </div>

        <div class="relative space-y-6 px-6 py-8 text-center sm:px-10 sm:py-10" :class="event.coverPhotoUrl ? 'text-white' : 'text-stone-800'">
            <header class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.22em]" :class="event.coverPhotoUrl ? 'text-amber-100' : 'text-[#8d7953]'">
                    Galeri Momen Bahagia
                </p>
                <h1 class="font-serif text-3xl italic leading-tight sm:text-4xl">
                    {{ event.brideName }} <span :class="event.coverPhotoUrl ? 'text-amber-200' : 'text-[#a18b61]'">&amp;</span> {{ event.groomName }}
                </h1>
                <p class="text-sm" :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">{{ event.title }}</p>
            </header>

            <div class="flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs" :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">
                <span class="inline-flex items-center gap-1.5">
                    <CalendarDays :size="15" aria-hidden="true" />
                    {{ eventDate }}
                </span>
                <span v-if="event.location" class="inline-flex items-center gap-1.5">
                    <MapPin :size="15" aria-hidden="true" />
                    {{ event.location }}
                </span>
            </div>

            <p class="mx-auto max-w-sm text-sm leading-6" :class="event.coverPhotoUrl ? 'text-white/80' : 'text-stone-600'">
                Abadikan momen seru Anda bersama pengantin dan bagikan fotonya ke galeri acara.
            </p>

            <input
                ref="cameraInput"
                class="hidden"
                type="file"
                accept="image/*"
                capture="environment"
                multiple
                aria-label="Ambil foto dengan kamera"
                @change="onFileChange"
            >
            <input
                ref="galleryInput"
                class="hidden"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                multiple
                aria-label="Pilih foto dari galeri"
                @change="onFileChange"
            >

            <div v-if="draftPhotos.length === 0" class="grid gap-3 sm:grid-cols-2">
                <button
                    v-if="isMobile"
                    type="button"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/30"
                    @click="cameraInput?.click()"
                >
                    <Camera :size="18" aria-hidden="true" />
                    Foto Lagi
                </button>
                <button
                    type="button"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#b5a277]/50 bg-white/70 px-5 py-3 text-sm font-semibold text-stone-800 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/20"
                    @click="galleryInput?.click()"
                >
                    <ImagePlus :size="18" aria-hidden="true" />
                    {{ isMobile ? 'Pilih dari Galeri' : 'Tambahkan Foto' }}
                </button>
            </div>

            <section v-else class="space-y-4 rounded-2xl border border-[#dfd5c2] bg-white/80 p-3 text-left">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-stone-700">{{ totalPhotosLabel }}</p>
                    <button
                        v-if="!uploading"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-stone-100 px-2.5 py-1.5 text-xs font-medium text-stone-700"
                        @click="galleryInput?.click()"
                    >
                        <ImagePlus :size="14" aria-hidden="true" />
                        Tambah foto
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div v-for="photo in draftPhotos" :key="photo.id" class="group relative overflow-hidden rounded-xl border border-stone-200 bg-stone-100">
                        <img :src="photo.previewUrl" :alt="photo.name" class="aspect-square w-full object-cover">
                        <button
                            v-if="!uploading"
                            type="button"
                            aria-label="Hapus foto dari daftar"
                            class="absolute right-2 top-2 rounded-full bg-slate-950/75 p-1.5 text-white transition hover:bg-slate-950"
                            @click="removeDraft(photo.id)"
                        >
                            <X :size="14" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50"
                        @click="cameraInput?.click()"
                    >
                        <Camera :size="16" aria-hidden="true" />
                        Foto Lagi
                    </button>
                    <button
                        type="button"
                        :disabled="uploading"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="uploadAll()"
                    >
                        <Upload :size="16" aria-hidden="true" />
                        {{ uploading ? 'Mengunggah…' : 'Upload' }}
                    </button>
                </div>
            </section>

            <div v-if="uploading || compressing" class="space-y-2" role="status" aria-live="polite">
                <div class="h-2 overflow-hidden rounded-full bg-stone-200">
                    <div
                        class="h-full rounded-full bg-emerald-500 transition-[width]"
                        :style="{ width: `${compressing ? 8 : Math.max(progress, 8)}%` }"
                    />
                </div>
                <p class="text-xs" :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">
                    {{ compressing ? 'Menyiapkan foto…' : `Mengunggah ${progress}%…` }}
                </p>
            </div>

            <p v-if="error" class="rounded-xl border border-rose-300/50 bg-rose-100/90 px-4 py-3 text-left text-sm text-rose-800" role="alert">
                {{ error }}
            </p>
            <p v-if="success" class="flex items-start gap-2 rounded-xl border border-emerald-300/50 bg-emerald-100/90 px-4 py-3 text-left text-sm text-emerald-900" role="status">
                <CheckCircle2 :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                {{ success }}
            </p>

            <p class="text-xs leading-5" :class="event.coverPhotoUrl ? 'text-white/60' : 'text-stone-400'">
                Foto akan dikompresi sebelum diunggah. Maksimal 20 MB per foto. Gunakan sesi tamu lokal agar daftar foto tetap terpisah.
            </p>
            <a
                :href="event.galleryUrl"
                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#b5a277]/50 px-5 py-2.5 text-sm font-semibold transition hover:bg-white/70 focus:outline-none focus:ring-4 focus:ring-amber-500/20"
                :class="event.coverPhotoUrl ? 'text-white hover:bg-white/10' : 'text-stone-700'"
            >
                Lihat galeri foto acara
            </a>
        </div>
    </article>
</template>
