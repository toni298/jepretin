import { onBeforeUnmount, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export function useEventFilters(getFilters) {
    const initialFilters = getFilters()
    const query = ref(initialFilters.search)
    const status = ref(initialFilters.status)
    let syncing = false
    let debounceTimer

    watch([query, status], () => {
        if (syncing) return

        clearTimeout(debounceTimer)
        debounceTimer = setTimeout(() => {
            router.get('/events', {
                search: query.value.trim() || undefined,
                status: status.value || undefined,
            }, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            })
        }, 300)
    }, { flush: 'sync' })

    watch(
        () => [getFilters().search, getFilters().status],
        ([search, currentStatus]) => {
            syncing = true
            query.value = search
            status.value = currentStatus
            syncing = false
        },
        { flush: 'sync' },
    )

    onBeforeUnmount(() => clearTimeout(debounceTimer))

    return { query, status }
}
