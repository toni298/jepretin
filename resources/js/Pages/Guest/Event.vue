<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageMetadata from '@/Components/UI/PageMetadata.vue'
import GuestInvitationCard from '@/Pages/Guest/Components/GuestInvitationCard.vue'
import InvitationLayout from '@/Layouts/InvitationLayout.vue'

defineProps<{
    event: {
        title: string
        brideName: string
        groomName: string
        eventDate: string | null
        location: string | null
        coverPhotoUrl: string | null
    }
    seo: {
        title: string
        description: string
        image: string
        url: string
    }
    guestName: string
    qrCodeUrl: string
}>()

defineOptions({ layout: InvitationLayout })
</script>

<template>
    <div class="contents">
        <PageMetadata v-bind="seo" />
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
            <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&display=swap" rel="stylesheet">
        </Head>

        <GuestInvitationCard :event="event" :guest-name="guestName" :qr-code-url="qrCodeUrl" />
    </div>
</template>
