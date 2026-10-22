<script setup lang="ts">
import BrandLogo from '@/Components/UI/BrandLogo.vue'
</script>

<template>
    <main class="dark flex min-h-screen items-center justify-center bg-slate-900 px-5 py-12 text-slate-100">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-slate-800/80 p-8 shadow-2xl shadow-black/30 sm:p-10">
            <header class="mb-8 space-y-3 text-center">
                <BrandLogo size="large" class="mx-auto" />
            </header>

            <slot />

            <footer class="mt-8 text-center text-xs leading-5 text-slate-500">
                Jepretin · Simpan setiap momen istimewa bersama.
            </footer>
        </section>
    </main>
</template>
