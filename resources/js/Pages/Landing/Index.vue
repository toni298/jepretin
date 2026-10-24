<script setup lang="ts">
import PageMetadata from '@/Components/UI/PageMetadata.vue'
import BrideSection from './Sections/BrideSection.vue'
import FeatureGrid from './Sections/FeatureGrid.vue'
import FinalCtaSection from './Sections/FinalCtaSection.vue'
import GallerySection from './Sections/GallerySection.vue'
import HeroSection from './Sections/HeroSection.vue'
import HowItWorksSection from './Sections/HowItWorksSection.vue'
import LandingNav from './Sections/LandingNav.vue'
import VendorSection from './Sections/VendorSection.vue'
import FaqSection from './Sections/FaqSection.vue'

defineProps<{
    loginUrl: string
    seo: {
        title: string
        description: string
        image: string
        url: string
    }
}>()

const whatsappUrl = 'https://wa.me/628112797899?text=Halo%20Admin%20Jepretin%2C%20saya%20tertarik%20untuk%20membuat%20event.%20Mohon%20informasi%20lebih%20lanjut.'
</script>

<template>
    <div class="overflow-x-hidden bg-[#100f10] font-sans text-white antialiased selection:bg-[#e98380] selection:text-[#160f10]">
        <PageMetadata v-bind="seo" />
        <LandingNav :login-url="loginUrl" />
        <main>
            <HeroSection :whatsapp-url="whatsappUrl" />
            <FeatureGrid />
            <BrideSection :login-url="loginUrl" />
            <HowItWorksSection />
            <GallerySection :login-url="loginUrl" />
            <VendorSection :login-url="loginUrl" />
            <FaqSection />
            <FinalCtaSection :whatsapp-url="whatsappUrl" />
        </main>
        <footer class="border-t border-white/[0.07] px-5 py-7 sm:px-8 lg:px-10">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                <a href="#beranda" class="flex items-center gap-2 text-sm font-semibold text-white/75">
                    <img :src="'/images/logo.webp'" alt="" class="size-7 rounded-lg">
                    Jepretin
                </a>
                <p>Setiap momen berharga, dari semua sudut.</p>
                <p>&copy; {{ new Date().getFullYear() }} Jepretin</p>
            </div>
        </footer>
    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}

@keyframes petal-drift {
    0% { transform: translate3d(0, -12px, 0) rotate(0deg); opacity: 0; }
    12% { opacity: .72; }
    88% { opacity: .6; }
    100% { transform: translate3d(46px, 320px, 0) rotate(210deg); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .hero-petal { animation: none !important; }
}
</style>