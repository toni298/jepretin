<script setup lang="ts">
import { Camera } from '@lucide/vue'

withDefaults(defineProps<{
    brideName: string
    groomName: string
    qrCodeUrl: string
    coverPhotoUrl?: string | null
}>(), {
    coverPhotoUrl: null,
})
</script>

<template>
    <article
        class="relative isolate mx-auto flex min-h-[760px] w-full max-w-xl flex-col items-center overflow-hidden rounded-[2rem] border border-[#d8c9a6] bg-[#F9F8F6] px-7 py-9 text-center shadow-[0_28px_90px_-32px_rgba(63,48,28,0.45)] sm:px-12"
        :class="coverPhotoUrl ? 'text-white' : 'text-stone-800'"
    >
        <div v-if="coverPhotoUrl" aria-hidden="true" class="absolute inset-0 -z-10 overflow-hidden">
            <img
                :src="coverPhotoUrl"
                alt=""
                class="absolute inset-0 size-full scale-110 object-cover blur-md"
            >
            <div class="absolute inset-0 bg-slate-950/70" />
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/25 via-transparent to-slate-950/50" />
        </div>

        <div v-else aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <svg class="absolute -right-4 -top-3 h-48 w-40 text-[#b5a277]/45" viewBox="0 0 160 190" fill="none">
                <path d="M156 4C104 23 72 58 58 112c-8 30-22 52-48 72M119 29c-21 0-39 11-49 29 20 5 38-5 49-29ZM91 58c-20-2-39 7-52 24 19 8 39 1 52-24ZM68 89c-20-5-40 1-56 16 17 11 38 7 56-16ZM48 121c-18-7-39-5-57 7 15 13 36 12 57-7ZM77 67c-1-20 8-39 25-52 7 20 0 39-25 52ZM58 99c-4-20 2-40 17-56 10 18 6 39-17 56ZM37 132c-7-19-5-40 7-58 13 16 12 37-7 58Z" stroke="currentColor" stroke-width="1.2" />
            </svg>
            <svg class="absolute -bottom-5 -left-5 h-44 w-36 rotate-180 text-[#b5a277]/35" viewBox="0 0 160 190" fill="none">
                <path d="M156 4C104 23 72 58 58 112c-8 30-22 52-48 72M119 29c-21 0-39 11-49 29 20 5 38-5 49-29ZM91 58c-20-2-39 7-52 24 19 8 39 1 52-24ZM68 89c-20-5-40 1-56 16 17 11 38 7 56-16ZM48 121c-18-7-39-5-57 7 15 13 36 12 57-7ZM77 67c-1-20 8-39 25-52 7 20 0 39-25 52ZM58 99c-4-20 2-40 17-56 10 18 6 39-17 56ZM37 132c-7-19-5-40 7-58 13 16 12 37-7 58Z" stroke="currentColor" stroke-width="1.2" />
            </svg>
            <div class="absolute inset-4 rounded-[1.5rem] border border-[#b5a277]/35" />
        </div>

        <p
            class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] sm:text-xs sm:tracking-[0.28em]"
            :class="coverPhotoUrl ? 'text-amber-100' : 'text-[#8d7953]'"
        >
            — Selamat Datang di Hari Bahagia Kami —
        </p>

        <div class="mt-8 space-y-2">
            <p class="font-serif text-2xl italic sm:text-3xl">Terima Kasih Atas Kehadiran Anda</p>
            <p class="mx-auto max-w-sm text-sm leading-6" :class="coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">
                Merupakan suatu kehormatan bagi kami dapat berbagi kebahagiaan bersama Anda.
            </p>
        </div>

        <h1 class="mt-7 font-serif text-4xl italic leading-tight sm:text-5xl">
            {{ brideName }} <span :class="coverPhotoUrl ? 'text-amber-200' : 'text-[#a18b61]'">&amp;</span> {{ groomName }}
        </h1>

        <div class="my-6 flex items-center gap-3" aria-hidden="true">
            <span class="h-px w-12" :class="coverPhotoUrl ? 'bg-white/30' : 'bg-[#b5a277]/50'" />
            <span class="size-1.5 rotate-45 border" :class="coverPhotoUrl ? 'border-amber-200' : 'border-[#a18b61]'" />
            <span class="h-px w-12" :class="coverPhotoUrl ? 'bg-white/30' : 'bg-[#b5a277]/50'" />
        </div>

        <section
            class="w-full max-w-md rounded-2xl border px-5 py-4 text-left backdrop-blur-sm"
            :class="coverPhotoUrl ? 'border-white/20 bg-white/10' : 'border-[#dfd5c2] bg-white/65'"
        >
            <p class="text-center text-sm font-semibold tracking-wide">📸 Mari Berbagi Momen!</p>
            <p class="mt-2 text-center text-sm leading-6" :class="coverPhotoUrl ? 'text-white/80' : 'text-stone-600'">
                Pindai QR di bawah untuk mengunggah foto keseruan Anda bersama pengantin langsung ke galeri acara.
            </p>
        </section>

        <section
            class="mt-6 flex flex-col items-center rounded-2xl border p-3"
            :class="coverPhotoUrl ? 'border-white/20 bg-white' : 'border-[#dfd5c2] bg-white shadow-sm'"
        >
            <div class="relative size-48">
                <img :src="qrCodeUrl" alt="QR Code untuk mengunggah foto ke galeri acara" class="size-full">
                <span class="absolute left-1/2 top-1/2 flex size-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-xl bg-white shadow-sm">
                    <Camera :size="22" class="text-stone-700" aria-hidden="true" />
                </span>
            </div>
            <p class="mt-2 text-xs font-medium tracking-wide text-stone-600">
                Pindai QR untuk unggah foto
            </p>
        </section>

        <p class="mt-auto pt-7 font-serif text-sm italic" :class="coverPhotoUrl ? 'text-amber-100/90' : 'text-[#8d7953]'">
            Terima kasih telah melengkapi kebahagiaan hari ini.
        </p>
    </article>
</template>
