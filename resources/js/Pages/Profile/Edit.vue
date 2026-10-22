<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Check } from '@lucide/vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import BaseInput from '@/Components/UI/BaseInput.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps<{
    user: {
        name: string
        email: string
    }
}>()

defineOptions({ layout: AdminLayout })

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

function submit(): void {
    form.put('/profile', {
        onSuccess: () => form.reset('current_password', 'new_password', 'new_password_confirmation'),
    })
}
</script>

<template>
    <Head title="Edit profil" />

    <section class="mx-auto w-full max-w-2xl space-y-6">
        <header class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-300">Akun Anda</p>
            <h1 class="text-3xl font-semibold tracking-tight text-white">Edit profil</h1>
            <p class="text-sm text-slate-400">Perbarui nama, alamat email, atau kata sandi akun Anda.</p>
        </header>

        <form class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg shadow-black/10 sm:p-8" @submit.prevent="submit">
            <BaseInput
                id="name"
                v-model="form.name"
                name="name"
                label="Nama"
                autocomplete="name"
                required
                :error="form.errors.name ?? ''"
            />
            <BaseInput
                id="email"
                v-model="form.email"
                name="email"
                label="Email"
                type="email"
                autocomplete="email"
                required
                :error="form.errors.email ?? ''"
            />

            <div class="space-y-5 border-t border-slate-800 pt-6">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-white">Ganti kata sandi</h2>
                    <p class="text-sm text-slate-400">Isi ketiga kolom ini hanya jika ingin mengganti kata sandi.</p>
                </div>
                <BaseInput
                    id="current_password"
                    v-model="form.current_password"
                    name="current_password"
                    label="Kata sandi saat ini"
                    type="password"
                    autocomplete="current-password"
                    :error="form.errors.current_password ?? ''"
                />
                <BaseInput
                    id="new_password"
                    v-model="form.new_password"
                    name="new_password"
                    label="Kata sandi baru"
                    type="password"
                    autocomplete="new-password"
                    :error="form.errors.new_password ?? ''"
                />
                <BaseInput
                    id="new_password_confirmation"
                    v-model="form.new_password_confirmation"
                    name="new_password_confirmation"
                    label="Konfirmasi kata sandi baru"
                    type="password"
                    autocomplete="new-password"
                    :error="form.errors.new_password_confirmation ?? ''"
                />
            </div>

            <p v-if="form.recentlySuccessful" class="flex items-center gap-2 text-sm text-emerald-300" role="status">
                <Check :size="16" aria-hidden="true" />
                Profil berhasil diperbarui.
            </p>

            <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5">
                <BaseButton type="submit" :loading="form.processing" :disabled="form.processing">
                    Simpan perubahan
                </BaseButton>
                <BaseButton href="/dashboard" variant="secondary">
                    <ArrowLeft :size="16" aria-hidden="true" />
                    Kembali
                </BaseButton>
            </div>
        </form>
    </section>
</template>