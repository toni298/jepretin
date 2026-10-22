<script setup lang="ts">
import { ref } from 'vue'
import { toPng } from 'html-to-image'
import { Head } from '@inertiajs/vue3'
import { Download, LoaderCircle } from '@lucide/vue'

const props = defineProps<{
    event: {
        slug: string
        bride_name: string
        groom_name: string
    }
    coverPhotoUrl: string | null
    qrCodeUrl: string | null
}>()

const cardElement = ref<HTMLElement | null>(null)
const isDownloading = ref(false)
const downloadError = ref('')

async function downloadCard(): Promise<void> {
    if (!cardElement.value || !props.qrCodeUrl || isDownloading.value) {
        return
    }

    isDownloading.value = true
    downloadError.value = ''

    try {
        await document.fonts.load('76px "Alex Brush"')
        await document.fonts.ready

        const image = await toPng(cardElement.value, {
            cacheBust: true,
            pixelRatio: 2,
            width: 600,
            height: 800,
        })
        const link = document.createElement('a')
        const safeSlug = props.event.slug.replace(/[^a-z0-9-]/gi, '-')

        link.download = `QR-Resepsi-${safeSlug}.png`
        link.href = image
        document.body.append(link)
        link.click()
        link.remove()
    } catch {
        downloadError.value = 'Kartu QR belum berhasil dibuat. Silakan coba lagi.'
    } finally {
        isDownloading.value = false
    }
}
</script>

<template>
    <div class="space-y-2">
        <Head>
            <link head-key="alex-brush-preconnect" rel="preconnect" href="https://fonts.googleapis.com">
            <link head-key="alex-brush-preconnect-cors" rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
            <link head-key="alex-brush-font" rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alex+Brush&display=swap">
        </Head>

        <button
            type="button"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-400/20 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="!qrCodeUrl || isDownloading"
            :aria-busy="isDownloading"
            @click="downloadCard"
        >
            <LoaderCircle v-if="isDownloading" :size="17" aria-hidden="true" class="animate-spin" />
            <Download v-else :size="17" aria-hidden="true" />
            {{ isDownloading ? 'Menyiapkan kartu...' : 'Unduh Kartu QR Resepsi' }}
        </button>
        <p v-if="downloadError" class="text-center text-xs text-rose-300" role="alert">{{ downloadError }}</p>

        <div class="pointer-events-none fixed left-[-10000px] top-0 -z-10" aria-hidden="true">
            <div
                v-if="qrCodeUrl"
                ref="cardElement"
                class="relative isolate flex h-[800px] w-[600px] flex-col items-center overflow-hidden bg-[#21171b] px-10 pb-6 pt-7 text-center text-white"
            >
                <img
                    :src="coverPhotoUrl || '/banner/banner-1.webp'"
                    alt=""
                    crossorigin="anonymous"
                    class="absolute inset-0 -z-20 size-full scale-105 object-cover blur-[2px]"
                >
                <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#160f12]/65 via-[#160f12]/55 to-[#160f12]/80"></div>

                <p class="text-[14px] font-medium uppercase tracking-[0.28em] text-rose-100">The Wedding Celebration</p>
                <h2 class="mt-3 flex w-full flex-col items-center text-[76px] leading-[0.92] text-white" style="font-family: 'Alex Brush', cursive">
                    <span class="max-w-full break-words">{{ event.groom_name }}</span>
                    <span class="my-1 font-sans text-[20px] font-light italic leading-none text-rose-200">&amp;</span>
                    <span class="max-w-full break-words">{{ event.bride_name }}</span>
                </h2>

                <div class="mt-4 w-full rounded-[26px] border border-white/30 bg-white/15 px-7 py-5 shadow-2xl backdrop-blur-xl">
                    <div class="mx-auto flex size-[250px] items-center justify-center rounded-2xl bg-white p-3 shadow-lg">
                        <img :src="qrCodeUrl" alt="" class="size-full object-contain">
                    </div>
                    <h3 class="mt-4 text-[24px] font-semibold text-white">Scan untuk Unggah Foto!</h3>
                    <p class="mx-auto mt-2 max-w-[420px] text-[14px] leading-5 text-white/90">
                        Abadikan &amp; bagikan momen candid terbaikmu dari semua sudut secara real-time langsung ke galeri pernikahan.
                    </p>
                </div>

                <p class="mt-auto pt-4 text-[11px] font-medium uppercase tracking-[0.28em] text-rose-100/85">Powered by Jepretin</p>
            </div>
        </div>
    </div>
</template>