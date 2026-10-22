<script setup lang="ts">
defineProps<{ loginUrl: string }>()
</script>

<template>
    <section id="galeri" class="scroll-mt-24 border-y border-white/[0.05] bg-[#151314] px-5 py-20 sm:px-8 lg:px-10 lg:py-28">
        <div v-motion-reveal-up class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.08fr_.92fr] lg:gap-16">
            <div class="relative order-2 lg:order-1">
                <img :src="'/banner/banner-3.webp'" alt="Tampilan galeri digital Jepretin dengan foto, fitur berbagi, dan QR Code" class="aspect-[1.4/1] w-full rounded-[22px] border border-white/10 object-cover shadow-[0_24px_80px_rgba(0,0,0,.38)]">
                <div class="absolute bottom-4 left-4 flex items-center gap-3 rounded-xl border border-white/10 bg-[#171516]/90 px-4 py-3 shadow-2xl backdrop-blur-md sm:bottom-6 sm:left-6">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-[#ed817e] text-[#221516]"><svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-5"><path d="M3 4h14v12H3zM5.5 13l3-3 2.2 2 2-2 2.8 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                    <span class="text-xs leading-5"><strong class="block text-sm font-semibold text-white">324</strong><span class="text-white/55">Foto terkumpul</span></span>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#ef938c]">Galeri Digital</p>
                <h2 class="mt-4 max-w-xl text-3xl font-semibold leading-tight sm:text-4xl lg:text-[46px]">Semua Momen <span class="text-[#f0a69a]">dalam Satu Tempat</span></h2>
                <p class="mt-5 max-w-lg text-[15px] leading-7 text-white/60">Nikmati pengalaman melihat kembali hari bahagiamu dari berbagai sudut. Semua foto tersusun rapi, mudah diakses, dan bisa diunduh kapan saja.</p>
                <ul class="mt-7 grid gap-3.5 text-sm text-white/80">
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Galeri foto yang terorganisir</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Filter berdasarkan tamu atau fotografer</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Unduh koleksi foto resolusi asli</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Bagikan momen ke keluarga dan teman</li>
                </ul>
                <a :href="loginUrl" class="mt-8 inline-flex min-h-11 items-center gap-3 rounded-full bg-gradient-to-r from-[#e76672] to-[#f3a28c] px-5 text-sm font-semibold text-[#1a1112] transition hover:-translate-y-0.5 hover:brightness-110">Lihat Tampilan Galeri <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </section>
</template>