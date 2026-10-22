<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { CalendarDays, ChevronDown, CircleUserRound, LayoutDashboard, LogOut, Settings2 } from '@lucide/vue'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import BrandLogo from '@/Components/UI/BrandLogo.vue'

type AuthUser = {
    name: string
    email: string
    avatar_url?: string | null
    profile_photo_url?: string | null
}

const page = usePage<{ auth?: { user?: AuthUser | null } }>()
const user = computed(() => page.props.auth?.user ?? null)
const currentPath = computed(() => page.url.split('?')[0])
const avatarUrl = computed(() => user.value?.avatar_url || user.value?.profile_photo_url || null)
const initials = computed(() => {
    const words = (user.value?.name || user.value?.email || '').trim().split(/\s+/).filter(Boolean)

    return words.slice(0, 2).map((word) => word.charAt(0).toUpperCase()).join('') || null
})

const userMenu = ref<HTMLElement | null>(null)
const isDropdownOpen = ref(false)
const hasAvatarError = ref(false)

watch(avatarUrl, () => {
    hasAvatarError.value = false
})

function closeDropdown(): void {
    isDropdownOpen.value = false
}

function isActive(path: string, exact = false): boolean {
    return exact
        ? currentPath.value === path
        : currentPath.value === path || currentPath.value.startsWith(`${path}/`)
}

function handleOutsideClick(event: PointerEvent): void {
    if (event.target instanceof Node && !userMenu.value?.contains(event.target)) {
        closeDropdown()
    }
}

onMounted(() => document.addEventListener('pointerdown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsideClick))
</script>

<template>
    <div class="dark min-h-screen bg-slate-950 text-slate-100">
        <header class="border-b border-slate-800 bg-slate-900">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <Link href="/dashboard" aria-label="Jepretin, dashboard">
                    <BrandLogo />
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Link
                        href="/dashboard"
                        aria-label="Dashboard"
                        :aria-current="isActive('/dashboard', true) ? 'page' : undefined"
                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-slate-800 hover:text-white"
                        :class="isActive('/dashboard', true) ? 'bg-slate-800 text-white' : 'text-slate-300'"
                    >
                        <LayoutDashboard :size="16" aria-hidden="true" />
                        <span class="hidden sm:inline">Dashboard</span>
                    </Link>
                    <Link
                        href="/events"
                        :aria-current="isActive('/events') ? 'page' : undefined"
                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-slate-800 hover:text-white"
                        :class="isActive('/events') ? 'bg-slate-800 text-white' : 'text-slate-300'"
                    >
                        <CalendarDays :size="16" aria-hidden="true" />
                        <span>Acara</span>
                    </Link>

                    <div v-if="user" ref="userMenu" class="relative">
                        <button
                            type="button"
                            class="group inline-flex min-h-11 items-center gap-2 rounded-full border border-slate-700 bg-slate-900/80 py-1 pl-1 pr-2.5 text-sm text-slate-100 transition hover:border-rose-300/50 hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/70"
                            :aria-expanded="isDropdownOpen"
                            aria-haspopup="menu"
                            aria-label="Buka menu akun"
                            @click="isDropdownOpen = !isDropdownOpen"
                            @keydown.esc.stop="closeDropdown"
                        >
                            <span class="flex size-9 items-center justify-center overflow-hidden rounded-full border border-rose-200/30 bg-gradient-to-br from-rose-300 to-pink-500 font-semibold text-slate-950">
                                <img
                                    v-if="avatarUrl && !hasAvatarError"
                                    :src="avatarUrl"
                                    :alt="`Foto profil ${user.name}`"
                                    class="size-full object-cover"
                                    @error="hasAvatarError = true"
                                >
                                <span v-else-if="initials" aria-hidden="true">{{ initials }}</span>
                                <CircleUserRound v-else :size="19" aria-hidden="true" />
                            </span>
                            <span class="hidden max-w-32 truncate font-medium sm:block">{{ user.name }}</span>
                            <ChevronDown :size="15" aria-hidden="true" class="text-slate-400 transition-transform" :class="{ 'rotate-180': isDropdownOpen }" />
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                        >
                            <div
                                v-if="isDropdownOpen"
                                class="absolute right-0 top-[calc(100%+0.65rem)] z-50 w-72 overflow-hidden rounded-2xl border border-slate-700/90 bg-slate-900 shadow-2xl shadow-black/40"
                                role="menu"
                                aria-label="Menu akun"
                            >
                                <div class="border-b border-slate-700/80 px-4 py-4">
                                    <p class="truncate text-sm font-semibold text-white">{{ user.name }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-400">{{ user.email }}</p>
                                </div>
                                <div class="p-2">
                                    <Link
                                        href="/profile/edit"
                                        role="menuitem"
                                        :aria-current="isActive('/profile') ? 'page' : undefined"
                                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-slate-800 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/70"
                                        :class="isActive('/profile') ? 'bg-slate-800 text-white' : 'text-slate-300'"
                                        @click="closeDropdown"
                                    >
                                        <Settings2 :size="17" aria-hidden="true" class="text-slate-400" />
                                        Edit Profil
                                    </Link>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        role="menuitem"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-rose-300 transition hover:bg-rose-400/10 hover:text-rose-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/70"
                                        @click="closeDropdown"
                                    >
                                        <LogOut :size="17" aria-hidden="true" />
                                        Keluar
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <Link
                        v-else
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-400 to-pink-300 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:brightness-110"
                    >
                        Masuk
                    </Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-7xl px-5 py-10 sm:px-8">
            <slot />
        </main>
    </div>
</template>
