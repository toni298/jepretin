export interface WeddingEvent {
    id: string
    title: string
    slug: string
    upload_token?: string | null
    bride_name: string
    groom_name: string
    event_date?: string | null
    location?: string | null
    cover_photo_path?: string | null
    gdrive_folder_id?: string | null
    status: 'active' | 'archived'
    photos_count: number
    synced_photos_count: number
}

export interface CursorEventPage {
    data: WeddingEvent[]
    next_cursor: string | null
    next_page_url: string | null
    prev_cursor: string | null
    prev_page_url: string | null
}

export type EventFormData = Pick<
    WeddingEvent,
    'title' | 'bride_name' | 'groom_name' | 'event_date' | 'location' | 'cover_photo_path' | 'gdrive_folder_id' | 'status'
>
