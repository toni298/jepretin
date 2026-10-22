<script setup lang="ts">
defineProps<{ loginUrl: string }>()
</script>

<template>
    <section id="vendor" class="scroll-mt-24 px-5 py-20 sm:px-8 lg:px-10 lg:py-28">
        <div v-motion-reveal-up class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[.9fr_1.1fr] lg:gap-16">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#ef938c]">Untuk Vendor &amp; EO</p>
                <h2 class="mt-4 max-w-xl text-3xl font-semibold leading-tight sm:text-4xl lg:text-[46px]">Berikan Nilai Tambah <span class="text-[#f0a69a]">untuk Setiap Event</span></h2>
                <p class="mt-5 max-w-lg text-[15px] leading-7 text-white/60">Tingkatkan pengalaman klien dengan koleksi foto tamu yang modern dan praktis. Cocok untuk vendor fotografi, wedding organizer, dan event organizer.</p>
                <ul class="mt-7 grid gap-3.5 text-sm text-white/80">
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Mudah diintegrasikan dalam paket layanan</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Meningkatkan kepuasan klien</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Branding custom untuk setiap event</li>
                    <li class="flex items-center gap-3"><span class="text-[#ef8a83]">✓</span>Kelola banyak event sekaligus</li>
                </ul>
                <a :href="loginUrl" class="mt-8 inline-flex min-h-11 items-center gap-3 rounded-full bg-gradient-to-r from-[#e76672] to-[#f3a28c] px-5 text-sm font-semibold text-[#1a1112] transition hover:-translate-y-0.5 hover:brightness-110">Daftar untuk Vendor <span aria-hidden="true">→</span></a>
            </div>
            <div class="relative overflow-hidden rounded-[22px] border border-white/10">
                <img :src="'/banner/banner-2.webp'" alt="QR Code Jepretin di meja resepsi pernikahan" class="aspect-[1.4/1] w-full object-cover object-[10%_center]">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                <p class="absolute bottom-5 left-5 rounded-full border border-white/15 bg-black/45 px-4 py-2 text-xs text-white/85 backdrop-blur-md">Satu QR untuk semua momen</p>
            </div>
        </div>
    </section>
</template>