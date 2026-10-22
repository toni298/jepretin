<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Archive, Pencil } from '@lucide/vue'
import DataTable from '@/Components/UI/DataTable.vue'
import type { CursorEventPage, WeddingEvent } from '@/types'
import EventStatusBadge from './EventStatusBadge.vue'

type EventTableAction = 'edit' | 'delete'

const props = withDefaults(defineProps<{
    events: WeddingEvent[]
    pagination?: Pick<CursorEventPage, 'prev_page_url' | 'next_page_url'> | null
    actions?: EventTableAction[]
    emptyMessage?: string
}>(), {
    pagination: null,
    actions: () => ['edit', 'delete'],
    emptyMessage: 'Tidak ada acara yang cocok dengan filter.',
})

const emit = defineEmits<{
    show: [event: WeddingEvent]
    edit: [event: WeddingEvent]
    delete: [event: WeddingEvent]
}>()

const columns = [
    { key: 'title', label: 'Acara' },
    { key: 'event_date', label: 'Tanggal' },
    { key: 'slug', label: 'Link tamu' },
    { key: 'photos', label: 'Foto' },
    { key: 'status', label: 'Status' },
    ...(props.actions.length ? [{ key: 'actions', label: 'Aksi' }] : []),
]

function formatDate(date: string | null | undefined): string {
    return date
        ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(`${date.slice(0, 10)}T00:00:00`))
        : 'Belum diatur'
}
</script>

<template>
    <DataTable
        :columns="columns"
        :rows="events"
        clickable-rows
        @row-click="emit('show', $event)"
    >
        <template #row="{ row }">
            <td class="px-5 py-4">
                <Link
                    :href="`/events/${row.id}`"
                    class="text-left font-semibold text-white hover:text-emerald-300"
                    @click.stop
                >
                    {{ row.title }}
                </Link>
                <p class="mt-1 text-xs text-slate-400">{{ row.bride_name }} &amp; {{ row.groom_name }}</p>
            </td>
            <td class="px-5 py-4 text-slate-300">{{ formatDate(row.event_date) }}</td>
            <td class="px-5 py-4 font-mono text-xs text-slate-300">/e/{{ row.slug }}</td>
            <td class="px-5 py-4 text-slate-300">
                {{ row.photos_count }} total · {{ row.synced_photos_count }} tersinkron
            </td>
            <td class="px-5 py-4">
                <EventStatusBadge :status="row.status" />
            </td>
            <td v-if="actions.length" class="px-5 py-4">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <button
                        v-if="actions.includes('edit')"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-300 transition hover:text-emerald-200"
                        @click.stop="emit('edit', row)"
                    >
                        <Pencil :size="15" aria-hidden="true" />
                        Edit
                    </button>
                    <button
                        v-if="actions.includes('delete') && row.status === 'active'"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-300 transition hover:text-rose-200"
                        @click.stop="emit('delete', row)"
                    >
                        <Archive :size="15" aria-hidden="true" />
                        Arsipkan
                    </button>
                    <span
                        v-if="actions.includes('delete') && row.status !== 'active' && actions.length === 1"
                        class="text-xs text-slate-500"
                    >—</span>
                </div>
            </td>
        </template>
        <template #empty>
            {{ emptyMessage }}
        </template>
    </DataTable>

    <nav
        v-if="pagination?.prev_page_url || pagination?.next_page_url"
        aria-label="Navigasi halaman acara"
        class="flex items-center justify-between gap-4"
    >
        <Link
            v-if="pagination?.prev_page_url"
            :href="pagination.prev_page_url"
            preserve-scroll
            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-emerald-400/50 hover:text-white"
        >
            Acara lebih baru
        </Link>
        <span v-else />
        <Link
            v-if="pagination?.next_page_url"
            :href="pagination.next_page_url"
            preserve-scroll
            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-emerald-400/50 hover:text-white"
        >
            Acara berikutnya
        </Link>
    </nav>
</template>
