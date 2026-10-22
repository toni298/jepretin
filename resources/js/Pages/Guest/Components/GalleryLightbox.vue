<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { ArrowDownToLine, ArrowLeft, ArrowRight, Minus, Plus, X } from '@lucide/vue'
import { useGalleryZoom } from '@/Pages/Guest/Composables/useGalleryZoom'

interface GalleryPhoto {
    id: string
    url: string
    downloadUrl: string
    fileName: string
    createdAt: string | null
}

const props = defineProps<{
    photo: GalleryPhoto
    index: number
    total: number
}>()

const emit = defineEmits<{
    close: []
    previous: []
    next: []
}>()

const viewport = ref<HTMLDivElement | null>(null)
const image = ref<HTMLImageElement | null>(null)
const {
    scale,
    offsetX,
    offsetY,
    isPanning,
    resetTransform,
    constrainOffsets,
    zoomIn,
    zoomOut,
    handlePointerDown,
    handlePointerMove,
    handlePointerEnd,
    handleWheel,
} = useGalleryZoom(viewport, image)

watch(() => props.photo.id, resetTransform)
watch(() => props.photo.url, resetTransform)
watch(() => props.index, resetTransform)

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') emit('close')
    if (event.key === 'ArrowLeft') emit('previous')
    if (event.key === 'ArrowRight') emit('next')
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/95 p-3 text-white sm:p-6"
        role="dialog"
        aria-modal="true"
        :aria-label="`Pratinjau foto ${photo.fileName}`"
        @click.self="emit('close')"
    >
        <div class="absolute inset-x-3 top-3 z-10 flex items-center justify-between gap-3 sm:inset-x-6 sm:top-6">
            <p class="min-w-0 truncate text-sm text-white/75">{{ photo.fileName }} · {{ index + 1 }}/{{ total }}</p>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 disabled:opacity-40" aria-label="Perkecil foto" :disabled="scale <= 1" @click="zoomOut">
                    <Minus :size="19" aria-hidden="true" />
                </button>
                <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 disabled:opacity-40" aria-label="Perbesar foto" :disabled="scale >= 4" @click="zoomIn">
                    <Plus :size="19" aria-hidden="true" />
                </button>
                <a :href="photo.downloadUrl" :download="photo.fileName" class="rounded-full bg-emerald-400 p-2.5 text-slate-950 transition hover:bg-emerald-300" aria-label="Unduh foto">
                    <ArrowDownToLine :size="19" aria-hidden="true" />
                </a>
                <button type="button" class="rounded-full bg-white/10 p-2.5 transition hover:bg-white/20" aria-label="Tutup foto" @click="emit('close')">
                    <X :size="20" aria-hidden="true" />
                </button>
            </div>
        </div>

        <button type="button" class="absolute left-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 sm:left-6" aria-label="Foto sebelumnya" @click="emit('previous')">
            <ArrowLeft :size="22" aria-hidden="true" />
        </button>

        <div
            ref="viewport"
            class="absolute inset-0 flex touch-none select-none items-center justify-center overflow-hidden pb-12 pt-16"
            :class="isPanning ? 'cursor-grabbing' : 'cursor-grab'"
            @pointerdown="handlePointerDown"
            @pointermove="handlePointerMove"
            @pointerup="handlePointerEnd"
            @pointercancel="handlePointerEnd"
            @lostpointercapture="handlePointerEnd"
            @wheel="handleWheel"
        >
            <img
                ref="image"
                :src="photo.url"
                :alt="photo.fileName"
                draggable="false"
                class="max-h-full max-w-full object-contain"
                :style="{
                    transform: `translate3d(${offsetX}px, ${offsetY}px, 0) scale(${scale})`,
                    transformOrigin: 'center',
                    willChange: scale > 1 || isPanning ? 'transform' : 'auto',
                }"
                @load="constrainOffsets"
            >
        </div>

        <button type="button" class="absolute right-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-2.5 transition hover:bg-white/20 sm:right-6" aria-label="Foto berikutnya" @click="emit('next')">
            <ArrowRight :size="22" aria-hidden="true" />
        </button>
    </div>
</template>
