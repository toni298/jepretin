<script setup lang="ts">
withDefaults(defineProps<{
    size?: 'small' | 'large'
}>(), {
    size: 'small',
})

const logoUrl = '/images/logo.webp'
</script>

<template>
    <img
        :src="logoUrl"
        alt="Jepretin"
        class="rounded-xl border border-white/10 object-contain"
        :class="size === 'large' ? 'h-16 max-w-48' : 'h-10 max-w-40'"
    >
</template>
