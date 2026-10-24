import { computed, onBeforeUnmount, ref } from 'vue'

const MAX_SOURCE_SIZE = 20 * 1024 * 1024
const MAX_COMPRESSED_SIZE = 10 * 1024 * 1024
const MAX_IMAGE_EDGE = 2000
const MAX_PHOTOS = 5
const ALLOWED_TYPES: readonly string[] = ['image/jpeg', 'image/png', 'image/webp']

interface DraftPhoto {
    id: string
    name: string
    type: string
    size: number
    previewUrl: string
    file: File
}

interface PersistedDraftPhoto {
    id: string
    name: string
    type: string
    size: number
    previewUrl: string
    lastModified: number
}

interface UploadSignature {
    path: string
    uploadUrl: string
    headers: Record<string, string>
}

interface UploadResult {
    photo: { id: string; url: string }
    message: string
    uploadedCount: number
}

function isUploadSignature(value: unknown): value is UploadSignature {
    return typeof value === 'object'
        && value !== null
        && 'path' in value && typeof value.path === 'string'
        && 'uploadUrl' in value && typeof value.uploadUrl === 'string'
        && 'headers' in value && typeof value.headers === 'object' && value.headers !== null
}

function isUploadResult(value: unknown): value is UploadResult {
    return typeof value === 'object'
        && value !== null
        && 'message' in value && typeof value.message === 'string'
        && 'photo' in value && typeof value.photo === 'object' && value.photo !== null
        && 'id' in value.photo && typeof value.photo.id === 'string'
        && 'url' in value.photo && typeof value.photo.url === 'string'
        && 'uploadedCount' in value && typeof value.uploadedCount === 'number'
}

function getXsrfToken(): string | undefined {
    const cookie = document.cookie.split('; ').find((entry) => entry.startsWith('XSRF-TOKEN='))

    return cookie ? decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) : undefined
}

function storageKeyFor(uploadToken: string): string {
    return `jepretin:guest:${uploadToken}`
}

async function readResponse(response: Response): Promise<unknown> {
    return response.json().catch(() => null)
}

function responseMessage(payload: unknown, fallback: string): string {
    if (typeof payload !== 'object' || payload === null) return fallback

    if ('message' in payload && typeof payload.message === 'string') return payload.message

    if ('errors' in payload && typeof payload.errors === 'object' && payload.errors !== null) {
        const firstError = Object.values(payload.errors).flat().find((message) => typeof message === 'string')
        if (typeof firstError === 'string') return firstError
    }

    return fallback
}

async function fileToDataUrl(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(String(reader.result))
        reader.onerror = () => reject(new Error('Foto tidak dapat dibaca di browser ini.'))
        reader.readAsDataURL(file)
    })
}

function dataUrlToFile(dataUrl: string, fileName: string, lastModified: number): File {
    const matches = dataUrl.match(/^data:(.*?);base64,(.*)$/)
    if (!matches) {
        throw new Error('Data foto tidak valid untuk sesi tamu.')
    }

    const mime = matches[1] || 'image/jpeg'
    const content = matches[2]
    const binary = atob(content)
    const bytes = new Uint8Array(binary.length)

    for (let index = 0; index < binary.length; index += 1) {
        bytes[index] = binary.charCodeAt(index)
    }

    return new File([bytes], fileName, { type: mime, lastModified })
}

async function compressPhoto(file: File): Promise<File> {
    if (typeof createImageBitmap !== 'function') {
        return file
    }

    const bitmap = await createImageBitmap(file)
    const scale = Math.min(1, MAX_IMAGE_EDGE / Math.max(bitmap.width, bitmap.height))
    const canvas = document.createElement('canvas')
    canvas.width = Math.round(bitmap.width * scale)
    canvas.height = Math.round(bitmap.height * scale)
    const context = canvas.getContext('2d')

    if (!context) {
        bitmap.close()
        return file
    }

    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height)
    bitmap.close()

    const blob = await new Promise<Blob>((resolve, reject) => {
        canvas.toBlob((result) => {
            if (result) resolve(result)
            else reject(new Error('Foto gagal dikompresi. Silakan pilih foto lain.'))
        }, 'image/jpeg', 0.82)
    })

    if (blob.size > MAX_COMPRESSED_SIZE) {
        throw new Error('Ukuran foto masih melebihi 10 MB setelah dikompresi.')
    }

    const filename = file.name.replace(/\.[^.]+$/, '') || 'foto'

    return new File([blob], `${filename}.jpg`, { type: 'image/jpeg', lastModified: Date.now() })
}

function uploadToStorage(
    url: string,
    file: File,
    headers: Record<string, string>,
    onProgress: (percentage: number) => void,
): Promise<void> {
    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest()
        request.open('PUT', url)
        Object.entries(headers).forEach(([name, value]) => request.setRequestHeader(name, value))

        if (!Object.keys(headers).some((name) => name.toLowerCase() === 'content-type')) {
            request.setRequestHeader('Content-Type', file.type)
        }

        request.upload.onprogress = (event) => {
            if (event.lengthComputable) onProgress(Math.round((event.loaded / event.total) * 100))
        }
        request.onload = () => {
            if (request.status >= 200 && request.status < 300) resolve()
            else reject(new Error(`Supabase menolak upload foto (HTTP ${request.status}).`))
        }
        request.onerror = () => reject(new Error('Koneksi ke Supabase gagal. Periksa jaringan dan pengaturan CORS bucket.'))
        request.onabort = () => reject(new Error('Upload foto dibatalkan.'))
        request.send(file)
    })
}

export function useGuestPhotoUpload(
    slug: string,
    uploadToken: string | undefined,
    initialUploadedPhotoCount: number,
    maxPhotos: number,
) {
    const error = ref('')
    const success = ref('')
    const uploading = ref(false)
    const compressing = ref(false)
    const progress = ref(0)
    const draftPhotos = ref<DraftPhoto[]>([])
    const uploadedPhotoCount = ref(Math.min(initialUploadedPhotoCount, maxPhotos))

    const totalPhotosLabel = computed(() => `${draftPhotos.value.length} foto siap diunggah`)
    const isAtUploadLimit = computed(() => uploadedPhotoCount.value >= maxPhotos)
    const canAddPhotos = computed(() => !isAtUploadLimit.value && uploadedPhotoCount.value + draftPhotos.value.length < maxPhotos)

    function persistDrafts(): void {
        if (!uploadToken) return

        const payload: PersistedDraftPhoto[] = draftPhotos.value.map((photo) => ({
            id: photo.id,
            name: photo.name,
            type: photo.type,
            size: photo.size,
            previewUrl: photo.previewUrl,
            lastModified: photo.file.lastModified,
        }))

        localStorage.setItem(storageKeyFor(uploadToken), JSON.stringify(payload))
    }

    async function hydrateDrafts(): Promise<void> {
        if (!uploadToken) return

        const raw = localStorage.getItem(storageKeyFor(uploadToken))
        if (!raw) return

        try {
            const payload = JSON.parse(raw) as PersistedDraftPhoto[]
            if (!Array.isArray(payload)) return

            const restored = await Promise.all(payload.map(async (item) => {
                const file = dataUrlToFile(item.previewUrl, item.name, item.lastModified)
                return {
                    id: item.id,
                    name: item.name,
                    type: item.type,
                    size: item.size,
                    previewUrl: item.previewUrl,
                    file,
                } satisfies DraftPhoto
            }))

            draftPhotos.value = restored
                .filter((photo) => photo.previewUrl.startsWith('data:'))
                .slice(0, Math.max(0, maxPhotos - uploadedPhotoCount.value))
            persistDrafts()
        } catch {
            localStorage.removeItem(storageKeyFor(uploadToken))
        }
    }

    async function addFiles(fileList: FileList | File[] | null): Promise<void> {
        if (!fileList || fileList.length === 0) return
        if (isAtUploadLimit.value) {
            error.value = `Maksimal ${maxPhotos} foto dapat diunggah pada perangkat ini untuk acara tersebut.`
            return
        }

        const remainingSlots = maxPhotos - uploadedPhotoCount.value - draftPhotos.value.length
        if (remainingSlots <= 0) {
            error.value = `Maksimal ${maxPhotos} foto dapat diunggah pada perangkat ini untuk acara tersebut.`
            return
        }

        const nextFiles = Array.from(fileList)
        const validFiles = nextFiles.filter((file) => ALLOWED_TYPES.includes(file.type) && file.size <= MAX_SOURCE_SIZE)

        if (validFiles.length === 0) {
            error.value = 'Pilih foto berformat JPG, PNG, atau WebP dengan ukuran maksimal 20 MB.'
            return
        }

        error.value = ''
        success.value = ''

        const prepared = await Promise.all(validFiles.slice(0, remainingSlots).map(async (file) => {
            const previewUrl = await fileToDataUrl(file)

            return {
                id: crypto.randomUUID(),
                name: file.name,
                type: file.type,
                size: file.size,
                previewUrl,
                file,
            } satisfies DraftPhoto
        }))

        draftPhotos.value = [...draftPhotos.value, ...prepared]
        persistDrafts()

        if (validFiles.length > remainingSlots) {
            error.value = `Maksimal ${maxPhotos} foto dapat diunggah pada perangkat ini. Hanya ${remainingSlots} foto yang ditambahkan.`
        }
    }

    function removeDraft(id: string): void {
        draftPhotos.value = draftPhotos.value.filter((photo) => photo.id !== id)
        persistDrafts()
    }

    function clearDrafts(): void {
        draftPhotos.value = []
        if (uploadToken) localStorage.removeItem(storageKeyFor(uploadToken))
    }

    async function uploadAll(): Promise<void> {
        if (draftPhotos.value.length === 0 || uploading.value) return
        if (isAtUploadLimit.value || uploadedPhotoCount.value + draftPhotos.value.length > maxPhotos) {
            error.value = `Maksimal ${maxPhotos} foto dapat diunggah pada perangkat ini untuk acara tersebut.`
            return
        }

        error.value = ''
        success.value = ''
        uploading.value = true
        progress.value = 0

        try {
            const token = getXsrfToken()
            const headers: Record<string, string> = {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            }
            if (token) headers['X-XSRF-TOKEN'] = token

            const encodedSlug = encodeURIComponent(slug)
            const processedFiles = await Promise.all(draftPhotos.value.map(async (photo) => ({
                ...photo,
                file: await compressPhoto(photo.file),
            })))

            const uploadResults = [] as Array<{ file: File; path: string; filename: string; contentType: string }>

            for (const [index, photo] of processedFiles.entries()) {
                compressing.value = true
                const signatureResponse = await fetch(`/e/${encodedSlug}/upload-url`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers,
                    body: JSON.stringify({ content_type: photo.file.type }),
                })
                const signaturePayload = await readResponse(signatureResponse)
                if (!signatureResponse.ok) {
                    throw new Error(responseMessage(signaturePayload, `Foto ${index + 1} gagal mendapat izin upload.`))
                }
                if (!isUploadSignature(signaturePayload)) {
                    throw new Error('Server tidak mengembalikan izin upload yang valid.')
                }

                await uploadToStorage(signaturePayload.uploadUrl, photo.file, signaturePayload.headers, (percent) => {
                    progress.value = Math.round(((index + percent / 100) / processedFiles.length) * 100)
                })

                uploadResults.push({
                    file: photo.file,
                    path: signaturePayload.path,
                    filename: photo.name.slice(0, 255),
                    contentType: photo.file.type,
                })
                compressing.value = false
            }

            for (const [index, upload] of uploadResults.entries()) {
                const storeResponse = await fetch(`/e/${encodedSlug}/photos`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers,
                    body: JSON.stringify({
                        path: upload.path,
                        filename: upload.filename,
                        content_type: upload.contentType,
                    }),
                })
                const storePayload = await readResponse(storeResponse)
                if (!storeResponse.ok) {
                    throw new Error(responseMessage(storePayload, `Foto ${index + 1} sudah diunggah tetapi gagal dicatat.`))
                }
                if (!isUploadResult(storePayload)) {
                    throw new Error('Server tidak memberikan konfirmasi untuk semua foto yang diunggah.')
                }
                uploadedPhotoCount.value = storePayload.uploadedCount
            }

            success.value = `${uploadResults.length} foto berhasil diunggah ke galeri acara.`
            clearDrafts()
            progress.value = 100
        } catch (uploadError) {
            error.value = uploadError instanceof Error
                ? uploadError.message
                : 'Semua foto gagal diunggah. Silakan coba kembali.'
        } finally {
            compressing.value = false
            uploading.value = false
        }
    }

    onBeforeUnmount(() => {
        persistDrafts()
    })

    void hydrateDrafts()

    return {
        draftPhotos,
        uploadedPhotoCount,
        maxPhotos,
        isAtUploadLimit,
        canAddPhotos,
        totalPhotosLabel,
        error,
        success,
        uploading,
        compressing,
        progress,
        addFiles,
        removeDraft,
        clearDrafts,
        uploadAll,
    }
}
