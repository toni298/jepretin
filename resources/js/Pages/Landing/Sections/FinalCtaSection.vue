<script setup lang="ts">
defineProps<{ loginUrl: string }>()
</script>

<template>
    <section class="relative isolate overflow-hidden px-5 py-20 sm:px-8 lg:px-10 lg:py-24">
        <div v-motion-reveal-up class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
            <img :src="'/banner/banner-1.webp'" alt="" class="absolute inset-0 -z-20 size-full object-cover object-[56%_center] opacity-35">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#100f10] via-[#100f10]/90 to-[#100f10]/65"></div>
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#ef938c]">Mulai Sekarang</p>
                <h2 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl lg:text-[46px]">Abadikan Lebih Banyak <span class="text-[#f0a69a]">Momen Indah Bersama</span></h2>
                <p class="mt-4 max-w-xl text-sm leading-6 text-white/65 sm:text-base">Buat event kamu sekarang dan kumpulkan cerita dari orang-orang terdekat.</p>
                <a :href="loginUrl" class="mt-7 inline-flex min-h-12 items-center gap-3 rounded-full bg-gradient-to-r from-[#e76672] to-[#f3a28c] px-6 text-sm font-semibold text-[#1a1112] shadow-[0_12px_35px_rgba(231,102,114,.2)] transition hover:-translate-y-0.5 hover:brightness-110">Buat Event Sekarang <span aria-hidden="true">→</span></a>
            </div>
            <div class="hidden items-center gap-3 rounded-2xl border border-white/10 bg-[#171516]/85 p-4 shadow-2xl backdrop-blur-lg sm:flex">
                <img :src="'/images/logo.webp'" alt="" class="size-12 rounded-xl">
                <span class="text-xs leading-5"><strong class="block text-sm font-semibold">Cerita yang utuh</strong><span class="text-white/50">dimulai dari setiap sudut</span></span>
            </div>
        </div>
    </section>
</template>