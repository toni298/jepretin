import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useSupabaseDirectUpload } from '@/Composables/useSupabaseDirectUpload'

export function useEventForm(getEvent) {
    const event = getEvent()
    const form = useForm({
        title: event?.title ?? '',
        bride_name: event?.bride_name ?? '',
        groom_name: event?.groom_name ?? '',
        event_date: event?.event_date?.slice(0, 10) ?? '',
        location: event?.location ?? '',
        cover_photo_path: event?.cover_photo_path ?? '',
        gdrive_folder_id: event?.gdrive_folder_id ?? '',
        status: event?.status ?? 'active',
    })
    const selectedFile = ref(null)
    const uploadError = ref('')
    const { progress, uploading, upload } = useSupabaseDirectUpload()

    async function submit() {
        uploadError.value = ''

        try {
            if (selectedFile.value) {
                form.cover_photo_path = await upload(selectedFile.value)
            }

            form.transform((data) => ({
                ...data,
                cover_photo_path: data.cover_photo_path || null,
                gdrive_folder_id: data.gdrive_folder_id || null,
            }))

            if (event) {
                form.put(`/events/${event.id}`)
                return
            }

            form.post('/events')
        } catch (error) {
            uploadError.value = error instanceof Error
                ? error.message
                : 'Foto cover gagal diunggah. Silakan coba kembali.'
        }
    }

    return { form, selectedFile, uploadError, progress, uploading, submit }
}
