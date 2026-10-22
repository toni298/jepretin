<script setup lang="ts">
import { computed } from 'vue'
import { CalendarDays, Clock3, MapPin } from '@lucide/vue'

const props = defineProps<{
    event: {
        title: string
        brideName: string
        groomName: string
        eventDate: string | null
        location: string | null
        coverPhotoUrl: string | null
    }
    guestName: string
    qrCodeUrl: string
}>()

const logoUrl = '/images/logo.webp'

const eventDay = computed(() => {
    if (!props.event.eventDate) return 'Tanggal akan diumumkan'

    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
    }).format(new Date(`${props.event.eventDate}T12:00:00`))
})

const eventDateLabel = computed(() => {
    if (!props.event.eventDate) return 'Menunggu konfirmasi'

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${props.event.eventDate}T12:00:00`))
})
</script>

<template>
    <div class="contents">
        <article
            class="relative isolate w-full max-w-md overflow-hidden rounded-[2rem] border border-[#d8c9a6] bg-[#F9F8F6] shadow-[0_28px_90px_-32px_rgba(63,48,28,0.45)]"
            :class="event.coverPhotoUrl ? 'text-white' : 'text-stone-800'"
        >
        <div v-if="event.coverPhotoUrl" aria-hidden="true" class="absolute inset-0 -z-10 overflow-hidden">
            <img
                :src="event.coverPhotoUrl"
                :alt="`Foto ${event.brideName} dan ${event.groomName}`"
                class="absolute inset-0 size-full scale-110 object-cover blur-md"
            >
            <div class="absolute inset-0 bg-slate-950/65" />
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/20 via-transparent to-slate-950/45" />
        </div>

        <div v-else aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <svg class="absolute -right-4 -top-3 h-48 w-40 text-[#b5a277]/45" viewBox="0 0 160 190" fill="none">
                <path d="M156 4C104 23 72 58 58 112c-8 30-22 52-48 72M119 29c-21 0-39 11-49 29 20 5 38-5 49-29ZM91 58c-20-2-39 7-52 24 19 8 39 1 52-24ZM68 89c-20-5-40 1-56 16 17 11 38 7 56-16ZM48 121c-18-7-39-5-57 7 15 13 36 12 57-7ZM77 67c-1-20 8-39 25-52 7 20 0 39-25 52ZM58 99c-4-20 2-40 17-56 10 18 6 39-17 56ZM37 132c-7-19-5-40 7-58 13 16 12 37-7 58Z" stroke="currentColor" stroke-width="1.2" />
                <path d="M143 17c-17 3-31 12-42 27m-18 27C67 74 55 85 46 99m-14 34c-8 10-15 18-22 25" stroke="currentColor" stroke-width=".8" />
            </svg>
            <svg class="absolute -bottom-5 -left-5 h-44 w-36 rotate-180 text-[#b5a277]/35" viewBox="0 0 160 190" fill="none">
                <path d="M156 4C104 23 72 58 58 112c-8 30-22 52-48 72M119 29c-21 0-39 11-49 29 20 5 38-5 49-29ZM91 58c-20-2-39 7-52 24 19 8 39 1 52-24ZM68 89c-20-5-40 1-56 16 17 11 38 7 56-16ZM48 121c-18-7-39-5-57 7 15 13 36 12 57-7ZM77 67c-1-20 8-39 25-52 7 20 0 39-25 52ZM58 99c-4-20 2-40 17-56 10 18 6 39-17 56ZM37 132c-7-19-5-40 7-58 13 16 12 37-7 58Z" stroke="currentColor" stroke-width="1.2" />
            </svg>
            <div class="absolute inset-4 rounded-[1.5rem] border border-[#b5a277]/35" />
        </div>

        <div class="relative flex min-h-[760px] flex-col items-center px-7 pb-8 pt-9 text-center sm:px-10">
            <div class="flex items-center gap-2">
                <span class="h-px w-8" :class="event.coverPhotoUrl ? 'bg-amber-200/60' : 'bg-[#b5a277]/70'" />
                <p
                    class="text-[0.65rem] font-semibold uppercase tracking-[0.28em]"
                    :class="event.coverPhotoUrl ? 'text-amber-100' : 'text-[#8d7953]'"
                >
                    Undangan Pernikahan
                </p>
                <span class="h-px w-8" :class="event.coverPhotoUrl ? 'bg-amber-200/60' : 'bg-[#b5a277]/70'" />
            </div>

            <p
                class="mt-8 text-xs uppercase tracking-[0.14em]"
                :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'"
            >
                Kepada Yth.
            </p>
            <p class="invitation-script mt-1 max-w-full break-words text-3xl" :class="event.coverPhotoUrl ? 'text-amber-100' : 'text-[#8d7953]'">
                {{ guestName }}
            </p>

            <p class="mt-8 text-sm" :class="event.coverPhotoUrl ? 'text-white/80' : 'text-stone-500'">
                Dengan penuh kebahagiaan, kami mengundang Anda untuk hadir di pernikahan
            </p>

            <h1 class="invitation-script mt-5 text-5xl leading-tight sm:text-6xl">
                <span class="block">{{ event.brideName }}</span>
                <span class="my-1 block font-serif text-3xl italic" :class="event.coverPhotoUrl ? 'text-amber-200' : 'text-[#a18b61]'">&amp;</span>
                <span class="block">{{ event.groomName }}</span>
            </h1>

            <p class="mt-4 text-xs font-medium uppercase tracking-[0.16em]" :class="event.coverPhotoUrl ? 'text-white/70' : 'text-stone-500'">
                {{ event.title }}
            </p>

            <div class="my-7 flex items-center gap-3" aria-hidden="true">
                <span class="h-px w-12" :class="event.coverPhotoUrl ? 'bg-white/30' : 'bg-[#b5a277]/50'" />
                <span class="size-1.5 rotate-45 border" :class="event.coverPhotoUrl ? 'border-amber-200' : 'border-[#a18b61]'" />
                <span class="h-px w-12" :class="event.coverPhotoUrl ? 'bg-white/30' : 'bg-[#b5a277]/50'" />
            </div>

            <div class="grid w-full grid-cols-2 gap-3">
                <section
                    class="flex min-h-24 flex-col items-center justify-center gap-2 rounded-2xl border px-3 py-4"
                    :class="event.coverPhotoUrl ? 'border-white/20 bg-white/10' : 'border-[#dfd5c2] bg-white/60'"
                >
                    <CalendarDays :size="17" :class="event.coverPhotoUrl ? 'text-amber-200' : 'text-[#9b8458]'" aria-hidden="true" />
                    <div class="space-y-1">
                        <p class="text-xs font-semibold capitalize">{{ eventDay }}</p>
                        <p class="text-[0.7rem]" :class="event.coverPhotoUrl ? 'text-white/70' : 'text-stone-500'">
                            {{ eventDateLabel }}
                        </p>
                    </div>
                </section>
                <section
                    class="flex min-h-24 flex-col items-center justify-center gap-2 rounded-2xl border px-3 py-4"
                    :class="event.coverPhotoUrl ? 'border-white/20 bg-white/10' : 'border-[#dfd5c2] bg-white/60'"
                >
                    <Clock3 :size="17" :class="event.coverPhotoUrl ? 'text-amber-200' : 'text-[#9b8458]'" aria-hidden="true" />
                    <div class="space-y-1">
                        <p class="text-xs font-semibold">Waktu Acara</p>
                        <p class="text-[0.7rem]" :class="event.coverPhotoUrl ? 'text-white/70' : 'text-stone-500'">
                            Informasi menyusul
                        </p>
                    </div>
                </section>
            </div>

            <section class="mt-5 w-full">
                <div class="flex items-center justify-center gap-2">
                    <MapPin :size="16" :class="event.coverPhotoUrl ? 'text-amber-200' : 'text-[#9b8458]'" aria-hidden="true" />
                    <h2 class="text-xs font-semibold uppercase tracking-[0.14em]">Lokasi Acara</h2>
                </div>
                <p class="mt-2 font-serif text-lg">{{ event.title }}</p>
                <p class="mt-1 whitespace-pre-line text-xs leading-5" :class="event.coverPhotoUrl ? 'text-white/75' : 'text-stone-500'">
                    {{ event.location || 'Informasi lokasi akan diumumkan' }}
                </p>
            </section>

            <section
                class="mt-6 flex flex-col items-center rounded-2xl border p-3"
                :class="event.coverPhotoUrl ? 'border-white/20 bg-white' : 'border-[#dfd5c2] bg-white shadow-sm'"
            >
                <div class="relative size-40">
                    <img :src="qrCodeUrl" :alt="`QR Code undangan ${event.title}`" class="size-full">
                    <span class="absolute left-1/2 top-1/2 flex size-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-lg bg-white p-1 shadow-sm">
                        <img :src="logoUrl" alt="" class="max-h-full max-w-full object-contain">
                    </span>
                </div>
                <p class="mt-2 text-[0.65rem] font-medium tracking-wide text-stone-500">
                    Pindai QR untuk membuka undangan
                </p>
            </section>

            <p class="mt-auto pt-6 font-serif text-sm italic" :class="event.coverPhotoUrl ? 'text-amber-100/90' : 'text-[#8d7953]'">
                Merupakan kebahagiaan bagi kami atas kehadiran Anda
            </p>
        </div>
        </article>
    </div>
</template>

<style scoped>
.invitation-script {
    font-family: 'Alex Brush', cursive;
}
</style>
