<template>
    <section id="faq" class="scroll-mt-24 border-y border-white/[0.05] bg-[#151314] px-5 py-20 sm:px-8 lg:px-10 lg:py-24">
        <div v-motion-reveal-up class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[.7fr_1.3fr] lg:gap-20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#ef938c]">FAQ</p>
                <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Yang sering <span class="text-[#f0a69a]">ditanyakan</span></h2>
                <p class="mt-4 max-w-sm text-sm leading-6 text-white/55">Masih ingin tahu lebih banyak tentang Jepretin? Temukan jawabannya di sini.</p>
            </div>
            <div class="divide-y divide-white/10">
                <details class="group py-5" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-base font-medium text-white [&::-webkit-details-marker]:hidden">Apakah tamu perlu menginstal aplikasi?<span class="text-xl text-[#ed8984] transition group-open:rotate-45">+</span></summary>
                    <p class="mt-3 max-w-2xl pr-8 text-sm leading-6 text-white/55">Tidak. Tamu cukup memindai QR Code dengan kamera ponsel, lalu mengunggah foto melalui browser.</p>
                </details>
                <details class="group py-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-base font-medium text-white [&::-webkit-details-marker]:hidden">Kapan foto yang diunggah bisa dilihat?<span class="text-xl text-[#ed8984] transition group-open:rotate-45">+</span></summary>
                    <p class="mt-3 max-w-2xl pr-8 text-sm leading-6 text-white/55">Foto akan muncul di galeri event setelah berhasil diunggah, sehingga momen bisa dikumpulkan selama acara berlangsung.</p>
                </details>
                <details class="group py-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-base font-medium text-white [&::-webkit-details-marker]:hidden">Siapa yang dapat mengakses galeri event?<span class="text-xl text-[#ed8984] transition group-open:rotate-45">+</span></summary>
                    <p class="mt-3 max-w-2xl pr-8 text-sm leading-6 text-white/55">Akses galeri mengikuti pengaturan event. Bagikan tautan hanya kepada orang-orang yang ingin kamu ajak melihat koleksi foto.</p>
                </details>
            </div>
        </div>
    </section>
</template>