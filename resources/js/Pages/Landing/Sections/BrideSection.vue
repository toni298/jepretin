<script setup lang="ts">
defineProps<{ loginUrl: string }>()
</script>

<template>
    <section id="pengantin" class="scroll-mt-24 border-y border-white/[0.05] bg-[#151314] px-5 py-20 sm:px-8 lg:px-10 lg:py-28">
        <div v-motion-reveal-up class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[.86fr_1.14fr] lg:gap-16">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#ef938c]">Untuk Pengantin</p>
                <h2 class="mt-4 max-w-xl text-3xl font-semibold leading-tight sm:text-4xl lg:text-[46px]">Lebih Banyak Cerita <span class="text-[#f0a69a]">dari Hari Bahagiamu</span></h2>
                <p class="mt-5 max-w-lg text-[15px] leading-7 text-white/60">Tidak hanya dari fotografer utama, sekarang kamu bisa mendapatkan foto candid langsung dari tamu undangan. Setiap senyum, pelukan, dan momen kecil tersimpan menjadi cerita yang lengkap.</p>
                <ul class="mt-7 grid gap-3.5 text-sm text-white/80">
                    <li class="flex items-center gap-3"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#e97779] text-[#1d1213]">✓</span>Koleksi foto spontan dan autentik</li>
                    <li class="flex items-center gap-3"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#e97779] text-[#1d1213]">✓</span>Semua momen dalam satu galeri</li>
                    <li class="flex items-center gap-3"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#e97779] text-[#1d1213]">✓</span>Bisa diunduh kapan saja</li>
                    <li class="flex items-center gap-3"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#e97779] text-[#1d1213]">✓</span>Cocok untuk pernikahan, tunangan, dan event lainnya</li>
                </ul>
                <a :href="loginUrl" class="mt-8 inline-flex items-center gap-2 rounded-full border border-white/15 px-5 py-3 text-sm font-medium text-white transition hover:border-[#ef938c]/60 hover:text-[#f3aaa0]">Mulai kumpulkan cerita <span aria-hidden="true">→</span></a>
            </div>
            <div class="relative">
                <img :src="'/banner/banner-2.webp'" alt="Galeri foto pernikahan dari berbagai sudut dan notifikasi foto baru" class="aspect-[1.36/1] w-full rounded-[22px] border border-white/10 object-cover shadow-[0_24px_80px_rgba(0,0,0,.38)]">
                <div class="absolute bottom-4 right-4 flex items-center gap-3 rounded-xl border border-white/10 bg-[#171516]/90 px-4 py-3 shadow-2xl backdrop-blur-md sm:bottom-6 sm:right-6">
                    <span class="flex -space-x-2"><span class="size-7 rounded-lg border-2 border-[#171516] bg-[#cf8d72]"></span><span class="size-7 rounded-lg border-2 border-[#171516] bg-[#7f5548]"></span><span class="size-7 rounded-lg border-2 border-[#171516] bg-[#e8b5a2]"></span></span>
                    <span class="text-xs leading-5"><strong class="block font-semibold text-white">100+ foto baru</strong><span class="text-white/55">hari ini</span></span>
                </div>
            </div>
        </div>
    </section>
</template>