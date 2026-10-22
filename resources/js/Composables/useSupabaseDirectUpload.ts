import { ref } from 'vue'

const MAX_SOURCE_SIZE = 20 * 1024 * 1024
const MAX_COMPRESSED_SIZE = 10 * 1024 * 1024
const MAX_IMAGE_EDGE = 2000

interface UploadSignature {
    path: string
    uploadUrl: string
    headers: Record<string, string>
}

function isUploadSignature(value: unknown): value is UploadSignature {
    return typeof value === 'object'
        && value !== null
        && 'path' in value
        && typeof value.path === 'string'
        && 'uploadUrl' in value
        && typeof value.uploadUrl === 'string'
        && 'headers' in value
        && typeof value.headers === 'object'
        && value.headers !== null
}

async function compressImage(file: File): Promise<File> {
    const bitmap = await createImageBitmap(file)
    const scale = Math.min(1, MAX_IMAGE_EDGE / Math.max(bitmap.width, bitmap.height))
    const canvas = document.createElement('canvas')
    canvas.width = Math.round(bitmap.width * scale)
    canvas.height = Math.round(bitmap.height * scale)
    const context = canvas.getContext('2d')

    if (!context) {
        bitmap.close()
        throw new Error('Browser tidak dapat memproses gambar cover.')
    }

    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height)
    bitmap.close()

    const blob = await new Promise<Blob>((resolve, reject) => {
        canvas.toBlob((result) => {
            if (!result) {
                reject(new Error('Gambar cover gagal dikompresi.'))
                return
            }
            if (result.type !== 'image/webp') {
                reject(new Error('Browser tidak mendukung kompresi WebP untuk cover.'))
                return
            }
            resolve(result)
        }, 'image/webp', 0.82)
    })

    if (blob.size > MAX_COMPRESSED_SIZE) {
        throw new Error('Foto cover masih melebihi batas 10 MB setelah dikompresi.')
    }

    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.webp', {
        type: 'image/webp',
        lastModified: Date.now(),
    })
}

function xsrfToken(): string | undefined {
    const cookie = document.cookie.split('; ').find((entry) => entry.startsWith('XSRF-TOKEN='))
    return cookie ? decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) : undefined
}

function uploadToStorage(
    url: string,
    file: File,
    headers: Record<string, string>,
    onProgress: (value: number) => void,
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
            if (request.status >= 200 && request.status < 300) {
                resolve()
                return
            }
            reject(new Error(`Supabase menolak upload cover (HTTP ${request.status}).`))
        }
        request.onerror = () => reject(new Error('Koneksi ke Supabase gagal saat upload cover.'))
        request.onabort = () => reject(new Error('Upload cover dibatalkan.'))
        request.send(file)
    })
}

export function useSupabaseDirectUpload() {
    const progress = ref(0)
    const uploading = ref(false)

    async function upload(file: File): Promise<string> {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            throw new Error('Pilih file JPG, PNG, atau WebP.')
        }
        if (file.size > MAX_SOURCE_SIZE) {
            throw new Error('Ukuran foto cover maksimal 20 MB sebelum kompresi.')
        }

        progress.value = 0
        uploading.value = true
        try {
            const compressedFile = await compressImage(file)
            const token = xsrfToken()
            const headers: Record<string, string> = {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            }
            if (token) headers['X-XSRF-TOKEN'] = token

            const response = await fetch('/events/cover-upload-signature', {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: JSON.stringify({ content_type: compressedFile.type }),
            })
            const payload: unknown = await response.json().catch(() => null)

            if (!response.ok) {
                const message = typeof payload === 'object' && payload !== null && 'message' in payload
                    && typeof payload.message === 'string'
                    ? payload.message
                    : `Gagal meminta izin upload (HTTP ${response.status}).`
                throw new Error(message)
            }
            if (!isUploadSignature(payload)) {
                throw new Error('Server tidak mengembalikan URL upload yang valid.')
            }

            await uploadToStorage(payload.uploadUrl, compressedFile, payload.headers, (value) => {
                progress.value = value
            })

            return payload.path
        } finally {
            uploading.value = false
        }
    }

    return { progress, uploading, upload }
}
