<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { ArrowDownToLine, ArrowLeft, ArrowRight, Camera, Images, Minus, Plus, X } from '@lucide/vue'
import PageMetadata from '@/Components/UI/PageMetadata.vue'
import InvitationLayout from '@/Layouts/InvitationLayout.vue'

interface GalleryPhoto {
    id: string
    url: string
    downloadUrl: string
    fileName: string
    createdAt: string | null
}

interface GalleryPhotosResponse {
    data: GalleryPhoto[]
    next_cursor: string | null
    has_more: boolean
}

const props = defineProps<{
    event: {
        slug: string
        title: string
        brideName: string
        groomName: string
        eventDate: string | null
        location: string | null
        coverPhotoUrl: string | null
        uploadUrl: string
    }
    seo: {
        title: string
        description: string
        image: string
        url: string
    }
    photos: GalleryPhoto[]
    nextCursor: string | null
    hasMore: boolean
    photosEndpoint: string
}>()

defineOptions({ layout: InvitationLayout })

const photos = ref([...props.photos])
const nextCursor = ref(props.nextCursor)
const hasMore = ref(props.hasMore)
const isLoadingMore = ref(false)
const loadError = ref('')
const activePhotoIndex = ref<number | null>(null)
const zoom = ref(1)

const selectedPhoto = computed(() => (
    activePhotoIndex.value === null ? null : photos.value[activePhotoIndex.value] ?? null
))
const eventDateLabel = computed(() => {
    if (!props.event.eventDate) return null

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${props.event.eventDate}T12:00:00`))
})

function openPhoto(index: number): void {
    activePhotoIndex.value = index
    zoom.value = 1
}

function closePhoto(): void {
    activePhotoIndex.value = null
    zoom.value = 1
}

function showPreviousPhoto(): void {
    if (activePhotoIndex.value === null || photos.value.length === 0) return

    activePhotoIndex.value = (activePhotoIndex.value - 1 + photos.value.length) % photos.value.length
    zoom.value = 1
}

function showNextPhoto(): void {
    if (activePhotoIndex.value === null || photos.value.length === 0) return

    activePhotoIndex.value = (activePhotoIndex.value + 1) % photos.value.length
    zoom.value = 1
}

function handleKeydown(event: KeyboardEvent): void {
    if (activePhotoIndex.value === null) return

    if (event.key === 'Escape') closePhoto()
    if (event.key === 'ArrowLeft') showPreviousPhoto()
    if (event.key === 'ArrowRight') showNextPhoto()
}

function zoomIn(): void {
    zoom.value = Math.min(zoom.value + 0.5, 3)
}

function zoomOut(): void {
    zoom.value = Math.max(zoom.value - 0.5, 1)
}

async function loadMore(): Promise<void> {
    if (!nextCursor.value || isLoadingMore.value) return

    isLoadingMore.value = true
    loadError.value = ''

    try {
        const url = new URL(props.photosEndpoint, window.location.origin)
        url.searchParams.set('cursor', nextCursor.value)

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!response.ok) {
            throw new Error('Foto berikutnya gagal dimuat. Silakan coba lagi.')
        }

        const result = await response.json() as GalleryPhotosResponse
        const existingIds = new Set(photos.value.map((photo) => photo.id))
        photos.value.push(...result.data.filter((photo) => !existingIds.has(photo.id)))
        nextCursor.value = result.next_cursor
        hasMore.value = result.has_more
    } catch (error) {
        loadError.value = error instanceof Error
            ? error.message
            : 'Foto berikutnya gagal dimuat. Silakan coba lagi.'
    } finally {
        isLoadingMore.value = false
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
    <div class="contents">
        <PageMetadata v-bind="seo" />

        <div class="min-h-screen w-full max-w-7xl space-y-8">
            <header class="relative isolate overflow-hidden rounded-[2rem] border border-[#d8c9a6] bg-[#f9f8f6] px-6 py-10 text-center text-stone-800 shadow-[0_28px_90px_-32px_rgba(63,48,28,0.25)] sm:px-12 sm:py-14">
            <div v-if="event.coverPhotoUrl" aria-hidden="true" class="absolute inset-0 -z-10">
                <img :src="event.coverPhotoUrl" alt="" loading="lazy" class="size-full object-cover">
                <div class="absolute inset-0 bg-slate-950/65" />
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/20 to-slate-950/50" />
            </div>

            <div class="relative mx-auto max-w-3xl space-y-5" :class="event.coverPhotoUrl ? 'text-white' : ''">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#8d7953]" :class="event.coverPhotoUrl ? 'text-amber-100' : ''">
                    <Images :size="16" aria-hidden="true" />
                    Galeri Foto
                </p>
                <h1 class="font-serif text-3xl italic leading-tight sm:text-5xl">
                    {{ event.brideName }} <span class="text-[#a18b61]" :class="event.coverPhotoUrl ? 'text-amber-200' : ''">&amp;</span> {{ event.groomName }}
                </h1>
                <p class="text-sm" :class="event.coverPhotoUrl ? 'text-white/80' : 'text-stone-500'">{{ event.title }}</p>
                <p v-if="eventDateLabel || event.location" class="text-sm" :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">
                    <span v-if="eventDateLabel">{{ eventDateLabel }}</span>
                    <span v-if="eventDateLabel && event.location" aria-hidden="true"> · </span>
                    <span v-if="event.location">{{ event.location }}</span>
                </p>
                <div class="flex flex-wrap justify-center gap-3 pt-2">
                    <a
                        :href="event.uploadUrl"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/30"
                    >
                        <Camera :size="17" aria-hidden="true" />
                        Bagikan foto
                    </a>
                </div>
            </div>
            </header>

            <section aria-labelledby="gallery-heading" class="space-y-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#8d7953]">Momen bersama</p>
                    <h2 id="gallery-heading" class="mt-1 text-2xl font-semibold text-stone-800">Foto acara</h2>
                </div>
                <p class="text-sm text-stone-500">{{ photos.length }} foto dimuat</p>
            </div>

            <div v-if="photos.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                <button
                    v-for="(photo, index) in photos"
                    :key="photo.id"
                    type="button"
                    class="group relative aspect-square overflow-hidden rounded-2xl bg-stone-200 text-left focus:outline-none focus:ring-4 focus:ring-emerald-500/50"
                    :aria-label="`Buka foto ${photo.fileName}`"
                    @click="openPhoto(index)"
                >
                    <img
                        :src="photo.url"
                        :alt="photo.fileName"
                        loading="lazy"
                        decoding="async"
                        class="size-full object-cover transition duration-500 group-hover:scale-105"
                    >
                    <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/45 via-transparent to-transparent opacity-0 transition group-hover:opacity-100" />
                    <span class="pointer-events-none absolute inset-x-3 bottom-3 truncate text-xs font-medium text-white opacity-0 transition group-hover:opacity-100">
                        {{ photo.fileName }}
                    </span>
                </button>
            </div>

            <div v-else class="rounded-3xl border border-dashed border-[#cfc3aa] bg-white/70 px-6 py-16 text-center">
                <Images :size="34" class="mx-auto text-[#a18b61]" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-semibold text-stone-800">Belum ada foto</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">
                    Jadilah yang pertama membagikan momen dari acara ini.
                </p>
                <a :href="event.uploadUrl" class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
                    <Camera :size="17" aria-hidden="true" />
                    Bagikan foto
                </a>
            </div>

            <div v-if="loadError" class="flex flex-col items-center gap-3 text-center" role="alert">
                <p class="text-sm text-rose-700">{{ loadError }}</p>
                <button type="button" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" @click="loadMore">
                    Coba lagi
                </button>
            </div>

            <div v-if="hasMore" class="flex justify-center pt-2">
                <button
                    type="button"
                    :disabled="isLoadingMore"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#b5a277]/60 bg-white/80 px-6 py-3 text-sm font-semibold text-stone-700 transition hover:bg-white disabled:cursor-wait disabled:opacity-60"
                    @click="loadMore"
                >
                    {{ isLoadingMore ? 'Memuat foto…' : 'Muat lebih banyak' }}
                </button>
            </div>
            </section>

            <div
            v-if="selectedPhoto"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/95 p-3 text-white sm:p-6"
            role="dialog"
            aria-modal="true"
            :aria-label="`Pratinjau foto ${selectedPhoto.fileName}`"
            @click.self="closePhoto"
        >
            <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-3 sm:inset-x-6 sm:top-6">
                <p class="min-w-0 truncate text-sm text-white/75">{{ selectedPhoto.fileName }}</p>
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 disabled:opacity-40" aria-label="Perkecil foto" :disabled="zoom <= 1" @click="zoomOut">
                        <Minus :size="19" aria-hidden="true" />
                    </button>
                    <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 disabled:opacity-40" aria-label="Perbesar foto" :disabled="zoom >= 3" @click="zoomIn">
                        <Plus :size="19" aria-hidden="true" />
                    </button>
                    <a :href="selectedPhoto.downloadUrl" :download="selectedPhoto.fileName" class="rounded-full bg-emerald-400 p-2.5 text-slate-950 transition hover:bg-emerald-300" aria-label="Unduh foto">
                        <ArrowDownToLine :size="19" aria-hidden="true" />
                    </a>
                    <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20" aria-label="Tutup foto" @click="closePhoto">
                        <X :size="20" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <button type="button" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 sm:left-6" aria-label="Foto sebelumnya" @click="showPreviousPhoto">
                <ArrowLeft :size="22" aria-hidden="true" />
            </button>
            <div class="flex size-full items-center justify-center overflow-auto pb-12 pt-16">
                <img
                    :src="selectedPhoto.url"
                    :alt="selectedPhoto.fileName"
                    loading="lazy"
                    class="max-h-full max-w-full object-contain transition-transform duration-200"
                    :style="{ transform: `scale(${zoom})` }"
                >
            </div>
            <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 sm:right-6" aria-label="Foto berikutnya" @click="showNextPhoto">
                <ArrowRight :size="22" aria-hidden="true" />
            </button>
            </div>
        </div>
    </div>
</template>
