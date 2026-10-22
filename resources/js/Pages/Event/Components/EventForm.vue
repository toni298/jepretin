<script setup lang="ts">
import { onBeforeUnmount, ref, watch, nextTick } from "vue";
import { useEventForm } from "../Composables/useEventForm.js";
import { ExternalLink, Lightbulb, X } from "@lucide/vue";
import BaseButton from "@/Components/UI/BaseButton.vue";
import BaseInput from "@/Components/UI/BaseInput.vue";
import BaseSelect from "@/Components/UI/BaseSelect.vue";
import DatePicker from "@/Components/UI/DatePicker.vue";
import FileUpload from "@/Components/UI/FileUpload.vue";
import type { WeddingEvent } from "@/types";

const props = defineProps<{ event?: WeddingEvent }>();
const { form, selectedFile, uploadError, progress, uploading, submit } =
    useEventForm(() => props.event);
const showDriveGuide = ref(false);
const guideButton = ref<HTMLButtonElement | null>(null);
const closeGuideButton = ref<HTMLButtonElement | null>(null);
let previousBodyOverflow = "";

const statusOptions = [
    { label: "Aktif", value: "active" },
    { label: "Diarsipkan", value: "archived" },
];

function handleGuideKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape") showDriveGuide.value = false;
}

function closeDriveGuide(): void {
    showDriveGuide.value = false;
    void nextTick(() => guideButton.value?.focus());
}

watch(showDriveGuide, async (isOpen) => {
    if (isOpen) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        window.addEventListener("keydown", handleGuideKeydown);
        await nextTick();
        closeGuideButton.value?.focus();
        return;
    }

    document.body.style.overflow = previousBodyOverflow;
    window.removeEventListener("keydown", handleGuideKeydown);
});

onBeforeUnmount(() => {
    document.body.style.overflow = previousBodyOverflow;
    window.removeEventListener("keydown", handleGuideKeydown);
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-3 sm:grid-cols-2">
            <BaseInput
                id="title"
                v-model="form.title"
                name="title"
                label="Nama acara"
                required
                :error="form.errors.title"
            />
            <DatePicker
                id="event_date"
                v-model="form.event_date"
                name="event_date"
                label="Tanggal acara"
                required
                :error="form.errors.event_date"
            />
            <BaseInput
                id="bride_name"
                v-model="form.bride_name"
                name="bride_name"
                label="Nama mempelai wanita"
                required
                :error="form.errors.bride_name"
            />
            <BaseInput
                id="groom_name"
                v-model="form.groom_name"
                name="groom_name"
                label="Nama mempelai pria"
                required
                :error="form.errors.groom_name"
            />
        </div>

        <div class="flex flex-col gap-3">
            <label
                for="location"
                class="block text-sm font-medium text-slate-200"
                >Lokasi acara</label
            >
            <textarea
                id="location"
                v-model="form.location"
                name="location"
                rows="3"
                required
                class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
            />
            <p v-if="form.errors.location" class="text-sm text-rose-300">
                {{ form.errors.location }}
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <BaseSelect
                id="status"
                v-model="form.status"
                name="status"
                label="Status acara"
                :options="statusOptions"
                :error="form.errors.status"
            />
            <div class="flex flex-col gap-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label
                        for="gdrive_folder_id"
                        class="block text-sm font-medium text-slate-200"
                    >
                        ID folder Google Drive
                        <span class="text-slate-400">(Opsional)</span>
                    </label>
                </div>
                <input
                    id="gdrive_folder_id"
                    v-model="form.gdrive_folder_id"
                    name="gdrive_folder_id"
                    type="text"
                    placeholder="Tempel ID folder Google Drive"
                    :aria-invalid="Boolean(form.errors.gdrive_folder_id)"
                    :aria-describedby="
                        form.errors.gdrive_folder_id
                            ? 'gdrive_folder_id-error'
                            : undefined
                    "
                    class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-white outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                />

                <button
                    ref="guideButton"
                    type="button"
                    aria-haspopup="dialog"
                    aria-controls="drive-folder-guide"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-300 transition hover:text-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-400/60 focus:ring-offset-2 focus:ring-offset-slate-900"
                    @click="showDriveGuide = true"
                >
                    <Lightbulb :size="15" aria-hidden="true" />
                    Cara mendapatkan ID?
                </button>
                <p
                    v-if="form.errors.gdrive_folder_id"
                    id="gdrive_folder_id-error"
                    class="text-sm text-rose-300"
                >
                    {{ form.errors.gdrive_folder_id }}
                </p>
            </div>
        </div>

        <FileUpload
            id="cover_photo"
            v-model="selectedFile"
            label="Foto cover (opsional)"
            :progress="progress"
            :error="uploadError || form.errors.cover_photo_path"
        />
        <p
            v-if="event?.cover_photo_path && !selectedFile"
            class="-mt-4 text-xs text-slate-500"
        >
            Foto cover saat ini akan tetap digunakan jika Anda tidak memilih
            file baru.
        </p>

        <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5">
            <BaseButton
                type="submit"
                :loading="form.processing || uploading"
                :disabled="form.processing || uploading"
            >
                {{ event ? "Simpan perubahan" : "Buat acara" }}
            </BaseButton>
            <BaseButton href="/events" variant="secondary">Batal</BaseButton>
        </div>
    </form>

    <Teleport to="body">
        <div
            v-if="showDriveGuide"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/80 p-4 backdrop-blur-sm"
            role="presentation"
            @click.self="closeDriveGuide"
        >
            <section
                id="drive-folder-guide"
                role="dialog"
                aria-modal="true"
                aria-labelledby="drive-folder-guide-title"
                class="my-auto w-full max-w-xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl shadow-slate-950/50"
            >
                <header
                    class="flex items-start justify-between gap-4 border-b border-slate-800 px-5 py-4 sm:px-6"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-400/10 text-indigo-300"
                        >
                            <Lightbulb :size="20" aria-hidden="true" />
                        </span>
                        <div class="space-y-1">
                            <h2
                                id="drive-folder-guide-title"
                                class="text-lg font-semibold text-white"
                            >
                                Cara mendapatkan ID folder
                            </h2>
                            <p class="text-sm text-slate-400">
                                Ikuti langkah mudah berikut di Google Drive.
                            </p>
                        </div>
                    </div>
                    <button
                        ref="closeGuideButton"
                        type="button"
                        aria-label="Tutup panduan"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400/60"
                        @click="closeDriveGuide"
                    >
                        <X :size="20" aria-hidden="true" />
                    </button>
                </header>

                <ol class="grid gap-5 px-5 py-6 sm:px-6">
                    <li class="flex gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-400/10 text-sm font-semibold text-indigo-300"
                            >1</span
                        >
                        <div class="space-y-1 text-sm leading-6 text-slate-300">
                            <p class="font-medium text-white">
                                Buka Google Drive
                            </p>
                            <p>
                                Kunjungi
                                <a
                                    href="https://drive.google.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-medium text-indigo-300 underline decoration-indigo-400/40 underline-offset-4 hover:text-indigo-200"
                                >
                                    drive.google.com
                                    <ExternalLink
                                        :size="13"
                                        aria-hidden="true"
                                    />
                                    <span class="sr-only"
                                        >(terbuka di tab baru)</span
                                    >
                                </a>
                                dan masuk ke akun Google Anda.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-400/10 text-sm font-semibold text-indigo-300"
                            >2</span
                        >
                        <div class="space-y-1 text-sm leading-6 text-slate-300">
                            <p class="font-medium text-white">
                                Buat folder baru
                            </p>
                            <p>
                                Klik tombol
                                <strong class="font-semibold text-white"
                                    >+ Baru</strong
                                >
                                di pojok kiri atas, pilih
                                <strong class="font-semibold text-white"
                                    >Folder baru</strong
                                >, lalu beri nama folder Anda.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-400/10 text-sm font-semibold text-indigo-300"
                            >3</span
                        >
                        <div class="space-y-1 text-sm leading-6 text-slate-300">
                            <p class="font-medium text-white">
                                Buka folder tersebut
                            </p>
                            <p>
                                Klik dua kali folder yang baru dibuat untuk
                                membukanya.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-400/10 text-sm font-semibold text-indigo-300"
                            >4</span
                        >
                        <div class="space-y-1 text-sm leading-6 text-slate-300">
                            <p class="font-medium text-white">
                                Salin ID dari alamat link
                            </p>
                            <p>
                                Salin kode panjang pada alamat browser, yaitu
                                bagian setelah teks
                                <code
                                    class="rounded bg-slate-800 px-1.5 py-0.5 font-mono text-xs text-indigo-200"
                                    >folders/</code
                                >.
                            </p>
                            <p
                                class="break-all rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 font-mono text-xs text-slate-300"
                            >
                                drive.google.com/drive/folders/<span
                                    class="font-semibold text-indigo-300"
                                    >ID_FOLDER_ANDA</span
                                >
                            </p>
                            <p>
                                Tempel kode tersebut pada kolom ID folder Google
                                Drive.
                            </p>
                        </div>
                    </li>
                </ol>

                <footer
                    class="flex justify-end border-t border-slate-800 px-5 py-4 sm:px-6"
                >
                    <BaseButton @click="closeDriveGuide"
                        >Saya Mengerti</BaseButton
                    >
                </footer>
            </section>
        </div>
    </Teleport>
</template>
