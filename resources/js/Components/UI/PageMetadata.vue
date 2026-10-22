<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
    title: string
    description: string
    image: string
    url: string
}>()

function httpsUrl(value: string, base?: string): string {
    try {
        const url = base ? new URL(value, base) : new URL(value)

        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return base ?? value
        }

        url.protocol = 'https:'

        return url.href
    } catch {
        return base ?? value
    }
}

const pageUrl = computed(() => httpsUrl(props.url))
const imageUrl = computed(() => httpsUrl(props.image, pageUrl.value))
</script>

<template>
    <Head :title="title">
        <meta head-key="description" name="description" :content="description">
        <meta head-key="og:type" property="og:type" content="website">
        <meta head-key="og:title" property="og:title" :content="title">
        <meta head-key="og:description" property="og:description" :content="description">
        <meta head-key="og:image" property="og:image" :content="imageUrl">
        <meta head-key="og:url" property="og:url" :content="pageUrl">
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image">
        <meta head-key="twitter:title" name="twitter:title" :content="title">
        <meta head-key="twitter:description" name="twitter:description" :content="description">
        <meta head-key="twitter:image" name="twitter:image" :content="imageUrl">
    </Head>
</template>