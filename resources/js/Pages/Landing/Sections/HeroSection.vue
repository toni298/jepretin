<script setup lang="ts">
defineProps<{ loginUrl: string }>()
</script>

<template>
    <section id="beranda" class="relative isolate scroll-mt-20 overflow-hidden">
        <img :src="'/banner/banner-1.webp'" alt="Pasangan pengantin dan galeri foto tamu di ponsel" class="absolute inset-0 -z-20 size-full object-cover object-[68%_center] opacity-45 lg:object-center lg:opacity-100">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#100f10]/90 via-[#100f10]/85 to-[#100f10] lg:bg-gradient-to-r lg:from-[#100f10] lg:via-[#100f10]/95 lg:via-55% lg:to-[#100f10]/15"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <span class="hero-petal absolute left-[14%] top-10 h-3 w-5 animate-[petal-drift_10s_ease-in-out_infinite] rounded-[80%_20%_80%_20%] bg-[#f4a3a0]/75 blur-[1px]"></span>
            <span class="hero-petal absolute left-[42%] top-0 h-2.5 w-4 animate-[petal-drift_13s_ease-in-out_-5s_infinite] rounded-[80%_20%_80%_20%] bg-[#f7c4b0]/70 blur-[1px]"></span>
            <span class="hero-petal absolute left-[72%] top-4 h-3 w-5 animate-[petal-drift_12s_ease-in-out_-8s_infinite] rounded-[80%_20%_80%_20%] bg-[#e97d83]/70 blur-[1px]"></span>
        </div>

        <div class="mx-auto flex min-h-[680px] max-w-7xl items-center px-5 pb-16 pt-20 sm:px-8 lg:min-h-[620px] lg:px-10 lg:pb-24 lg:pt-24">
            <div v-motion-reveal-up class="max-w-[590px]">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#f2a19b]/20 bg-black/25 px-3.5 py-2 text-[11px] font-medium uppercase tracking-[0.18em] text-[#f4aaa0] backdrop-blur-sm">
                    <span class="size-1.5 rounded-full bg-[#f28d87] shadow-[0_0_12px_#f28d87]"></span>
                    Cerita dari setiap sudut
                </p>
                <h1 class="max-w-[560px] text-[42px] font-semibold leading-[1.08] tracking-normal sm:text-5xl lg:text-[62px]">
                    Abadikan Setiap Momen
                    <span class="mt-1 block bg-gradient-to-r from-[#f5b5a4] via-[#ef8d8b] to-[#e86c7c] bg-clip-text text-transparent">Dari Semua Sudut</span>
                </h1>
                <p class="mt-6 max-w-[490px] text-base leading-7 text-white/65 sm:text-[17px]">
                    Kumpulkan foto candid dari tamu undangan melalui QR Code. Semua momen tersimpan otomatis, real-time, tanpa perlu menunggu hasil editing.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a :href="loginUrl" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-full bg-gradient-to-r from-[#e76672] to-[#f3a28c] px-6 text-sm font-semibold text-[#1a1112] shadow-[0_12px_35px_rgba(231,102,114,.2)] transition hover:-translate-y-0.5 hover:brightness-110">
                        Buat Event Sekarang
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </a>
                    <a href="#cara-kerja" class="inline-flex min-h-12 items-center justify-center gap-2.5 rounded-full border border-white/20 bg-black/20 px-6 text-sm font-medium text-white transition hover:border-white/40 hover:bg-white/5">
                        <span class="flex size-5 items-center justify-center rounded-full border border-white/60"><svg aria-hidden="true" viewBox="0 0 12 12" fill="currentColor" class="ml-0.5 size-2"><path d="M3 1.7 10 6l-7 4.3V1.7Z" /></svg></span>
                        Lihat Demo
                    </a>
                </div>
                <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-xs text-white/55">
                    <span class="inline-flex items-center gap-2"><svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4 text-[#f39b8e]"><path d="M10 2.5 12 7l4.8.5-3.6 3.2 1 4.8-4.2-2.5-4.2 2.5 1-4.8-3.6-3.2L8 7l2-4.5Z" fill="currentColor" /></svg>Tanpa install aplikasi</span>
                    <span class="inline-flex items-center gap-2"><svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4 text-[#f39b8e]"><path d="m11.3 1.8-7 9h4.9l-.6 7.4 7.1-10h-5l.6-6.4Z" fill="currentColor" /></svg>Upload real-time</span>
                    <span class="inline-flex items-center gap-2"><svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4 text-[#f39b8e]"><path d="M10 2.3 16 4.5v4.8c0 3.7-2.4 6.7-6 8.4-3.6-1.7-6-4.7-6-8.4V4.5l6-2.2Z" stroke="currentColor" stroke-width="1.6" /><path d="m7.3 9.8 1.8 1.8 3.7-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg>Aman &amp; privat terjaga</span>
                </div>
            </div>
        </div>
    </section>
</template>