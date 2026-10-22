<template>
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f4f0e8] px-4 py-10 text-stone-800 sm:px-6">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -left-24 -top-24 size-72 rounded-full bg-amber-200/30 blur-3xl" />
            <div class="absolute -bottom-32 -right-20 size-96 rounded-full bg-rose-200/25 blur-3xl" />
        </div>
        <slot />
    </main>
</template>
