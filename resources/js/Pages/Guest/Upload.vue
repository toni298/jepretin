<script setup lang="ts">
import GuestUploadCard from '@/Pages/Guest/Components/GuestUploadCard.vue'
import PageMetadata from '@/Components/UI/PageMetadata.vue'
import InvitationLayout from '@/Layouts/InvitationLayout.vue'

defineProps<{
    event: {
        slug: string
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
}>()

defineOptions({ layout: InvitationLayout })
</script>

<template>
    <div class="contents">
        <PageMetadata v-bind="seo" />
        <GuestUploadCard :event="event" />
    </div>
</template>
