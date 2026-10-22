<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import BaseButton from '@/Components/UI/BaseButton.vue'
import BaseInput from '@/Components/UI/BaseInput.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineOptions({
    layout: GuestLayout,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Masuk" />

    <div class="space-y-3 text-center">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Selamat datang kembali</h1>
        <p class="text-sm text-slate-400">Masuk untuk mengelola acara dan foto Anda.</p>
    </div>

    <form class="mt-8 space-y-5" @submit.prevent="submit">
        <BaseInput
            id="email"
            v-model="form.email"
            label="Email"
            name="email"
            type="email"
            autocomplete="email"
            placeholder="nama@email.com"
            :error="form.errors.email ?? ''"
            required
            autofocus
        />

        <BaseInput
            id="password"
            v-model="form.password"
            label="Password"
            name="password"
            type="password"
            autocomplete="current-password"
            placeholder="Masukkan password"
            :error="form.errors.password ?? ''"
            required
        />

        <label class="flex items-center gap-3 text-sm text-slate-300">
            <input
                v-model="form.remember"
                type="checkbox"
                name="remember"
                class="size-4 rounded border-slate-600 bg-slate-950 text-emerald-400 focus:ring-emerald-400"
            >
            Ingat saya
        </label>

        <BaseButton type="submit" class="w-full" :loading="form.processing">
            {{ form.processing ? 'Memproses...' : 'Masuk ke dashboard' }}
        </BaseButton>
    </form>
</template>
