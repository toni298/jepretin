<script setup lang="ts">
import { ref } from 'vue'

defineProps<{ loginUrl: string }>()

const mobileMenu = ref<HTMLDetailsElement | null>(null)

function closeMenu(): void {
    mobileMenu.value?.removeAttribute('open')
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-white/[0.07] bg-[#100f10]/90 backdrop-blur-xl">
        <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10">
            <a href="#beranda" class="flex shrink-0 items-center gap-2.5" aria-label="Jepretin, ke beranda">
                <img :src="'/images/logo.webp'" alt="" class="size-10 rounded-xl object-cover">
                <span class="text-lg font-semibold tracking-normal">Jepretin<span class="text-[#ef9990]">.</span></span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-8 text-sm text-white/70 md:flex">
                <a class="transition hover:text-white" href="#beranda">Beranda</a>
                <a class="transition hover:text-white" href="#fitur">Fitur</a>
                <a class="transition hover:text-white" href="#cara-kerja">Cara Kerja</a>
                <a class="transition hover:text-white" href="#vendor">Untuk Vendor</a>
                <a class="transition hover:text-white" href="#faq">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                <a :href="loginUrl" class="hidden items-center gap-2 rounded-full bg-gradient-to-r from-[#e76672] to-[#f5a38e] px-5 py-2.5 text-sm font-semibold text-[#1a1112] shadow-[0_8px_30px_rgba(231,102,114,.18)] transition hover:brightness-110 sm:inline-flex">
                    Mulai Sekarang
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
                <details ref="mobileMenu" class="relative md:hidden">
                    <summary aria-label="Buka menu" class="flex size-10 list-none cursor-pointer items-center justify-center rounded-full border border-white/15 text-white [&::-webkit-details-marker]:hidden">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-5"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" /></svg>
                    </summary>
                    <nav aria-label="Navigasi seluler" class="absolute right-0 top-14 grid w-56 gap-1 rounded-2xl border border-white/10 bg-[#191718] p-3 shadow-2xl">
                        <a class="rounded-lg px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white" href="#beranda" @click="closeMenu">Beranda</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white" href="#fitur" @click="closeMenu">Fitur</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white" href="#cara-kerja" @click="closeMenu">Cara Kerja</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white" href="#vendor" @click="closeMenu">Untuk Vendor</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white" href="#faq" @click="closeMenu">FAQ</a>
                        <a :href="loginUrl" class="mt-2 rounded-lg bg-[#ed817e] px-3 py-2.5 text-center text-sm font-semibold text-[#1a1112]">Mulai Sekarang</a>
                    </nav>
                </details>
            </div>
        </div>
    </header>
</template>