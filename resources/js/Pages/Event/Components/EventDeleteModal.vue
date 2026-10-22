<script setup lang="ts">
import BaseButton from '@/Components/UI/BaseButton.vue'
import type { WeddingEvent } from '@/types'

const props = defineProps<{
    event: WeddingEvent | null
    processing?: boolean
}>()
const emit = defineEmits<{
    close: []
    confirm: []
}>()

function archive(): void {
    if (props.event && !props.processing) emit('confirm')
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="event"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
            role="presentation"
            @click.self="!processing && emit('close')"
        >
            <section
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="archive-event-title"
                aria-describedby="archive-event-description"
                class="w-full max-w-md space-y-5 rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl"
            >
                <div class="space-y-2">
                    <h2 id="archive-event-title" class="text-lg font-semibold text-white">Arsipkan acara?</h2>
                    <p id="archive-event-description" class="text-sm leading-6 text-slate-300">
                        Acara “{{ event.title }}” tidak akan dihapus. Tautan tamu dinonaktifkan dan data foto tetap tersimpan.
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <BaseButton variant="secondary" :disabled="processing" @click="emit('close')">
                        Batal
                    </BaseButton>
                    <BaseButton :loading="processing" :disabled="processing" @click="archive">
                        Arsipkan
                    </BaseButton>
                </div>
            </section>
        </div>
    </Teleport>
</template>
