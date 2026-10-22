# AGENTS.md — Project Rules & AI Code Generation Guidelines (Jepretin)

> **This file is the single source of truth for all AI-generated code in this project.**
> Every rule here is non-negotiable. Read everything before writing a single line of code.
> If a task is unclear, ask for clarification — never assume.

---

## Stack

| Layer         | Technology                                                   |
| ------------- | ------------------------------------------------------------ |
| Backend       | Laravel 11 (latest stable)                                   |
| Frontend      | Vue 3 + Inertia.js (Full SSR, no REST API)                   |
| Styling       | TailwindCSS v4 (`@tailwindcss/vite`)                         |
| State         | Pinia                                                        |
| Authorization | Spatie Laravel Permission                                    |
| QR Code       | `simplesoftwareio/simple-qrcode`                             |
| Icons         | `lucide-vue-next`                                            |
| Date Picker   | `@vuepic/vue-datepicker`                                     |
| Scroll        | `vue-virtual-scroller`                                       |
| Database      | Neon DB (Serverless PostgreSQL)                              |
| Hosting       | Vercel (Serverless Deployment)                               |
| Storage       | Cloudflare R2 / Supabase Storage (Buffer) + Google Drive API |

---

## Business Concept (Jepretin — Event Photo Crowdsourcing)

**Jepretin** adalah platform _crowdsourced wedding & event photography_ berbasis QR Code. Admin (Event Organizer atau Mempelai) dapat mendaftarkan acara pernikahan, menghasilkan QR Code unik, dan mencetaknya di venue acara. Tamu undangan yang memindai QR Code dapat mengunggah foto-foto momen unik secara instan dari smartphone tanpa harus mendaftar.

- **Admin/EO**: Mengelola acara (`events`), memantau foto masuk, mengunduh cetakan QR Code, dan mengonfigurasi folder Google Drive tujuan.
- **Tamu (Guest)**: Membuka landing page acara via QR Code (`/e/{slug}`), mengunggah foto langsung ke Buffer Storage via _Presigned URL_, dan melihat ringkasan galeri.
- **Background Sync Engine**: Mengunggah foto dari Buffer Storage (Cloudflare R2) ke folder Google Drive milik mempelai secara asinkron (_batch cron job_) untuk menghindari _rate limit_ Google Drive dan batasan _payload size_ Vercel.

---

## ⚡ Serverless & Direct Upload Rules (Vercel & R2 Constraints)

- **Vercel Payload Limit Bypass**: Vercel Serverless Function memiliki batasan _request body_ **4.5 MB**. **DILARANG SEKERAS-KERASNYA** mengunggah file foto mentah melalui endpoint Laravel di Vercel.
- **Presigned URL Architecture**:
    1. Vue meminta token / Presigned URL singkat dari Laravel (`/api/events/{slug}/upload-url`).
    2. Vue mengunggah file foto **langsung** dari browser tamu ke Cloudflare R2 / Supabase Storage.
    3. Setelah upload selesai, Vue mengirimkan metadata (storage path & filename) kembali ke Laravel untuk dicatat di Neon DB (`photos`).
- **Google Drive Rate Limits**: Jangan memanggil Google Drive API secara langsung (_synchronous_) saat tamu menekan tombol upload. Selalu gunakan mekanisme _background sync_ (asinkron).
- **Client-side Compression**: Selalu terapkan kompresi gambar di sisi klien (Vue) sebelum mengunggah untuk menghemat bandwidth tamu dan mempercepat proses upload.

---

## ♻️ Mandatory Reusable Component Rule (Consistency First)

- **Never duplicate UI markup or layout patterns across pages.**
- **Always extract repeated UI elements, buttons, inputs, file uploaders, modals, event cards, and photo grids into reusable components** in `resources/js/Components/UI/`.
- Ensure all components are fully typed, accept props with default values, emit standard events, and follow design system tokens (Tailwind classes, emerald/rose wedding color palette, rounded-xl corners, smooth transitions) for 100% UI consistency across any page.

---

## Hierarchy & Data Access

```text
Super Admin / Admin (EO)
└── Event (Pernikahan / Event)
    └── Photos (Foto Tamu)
        └── Sync Queue (Google Drive Buffer)
```

### Access Roles

Only these application roles are valid (defined in `app/Support/Rbac.php`):

- `super admin` — internal developer/support account; bypasses every Gate via `Gate::before`
- `admin` (EO / Mempelai) — access to create and manage owned events, download QR codes, and view photos

> Authorization must always use **Policies / Gates / Permissions**, never manual role checks inside controllers or components.

---

## ⚠️ Critical Pre-Generation Checklist

Before writing **any** code, verify every item:

- [ ] Read this entire file from top to bottom
- [ ] Folder structure matches "Project Architecture" section
- [ ] **Reusable components reused** — check `Components/UI/` before creating anything new
- [ ] Direct Upload pattern strictly followed for image uploads (no raw file upload to Vercel/Laravel)
- [ ] Every query on event data includes proper ownership scope (`user_id` / `event_id`)
- [ ] No hardcoded IDs, credentials, or env values
- [ ] No axios/fetch for page data — Inertia only (except Presigned URL requests & direct R2 uploads)
- [ ] Frontend Vue file does not exceed **300 lines**
- [ ] No `v-html` on user-generated content
- [ ] No `DB::table()` — Eloquent only
- [ ] No inline validation — Form Request classes only
- [ ] No role checks — Policies/Gates only
- [ ] No raw SQL string concatenation — Eloquent bindings only
- [ ] No `$request->all()` — always `$request->validated()`

---

## Project Architecture

### Backend

```text
app/
├── Http/
│   ├── Controllers/ # Thin — max 300 lines, no business logic
│   ├── Middleware/ # Auth, security headers, rate limit
│   ├── Requests/ # All form validation — never inline
│   └── Resources/ # Resource transformers
├── Models/ # Eloquent models (User, Event, Photo, SyncLog)
├── Services/ # Business logic (StorageService, GoogleDriveService, QrCodeService)
├── Actions/ # Single-responsibility action classes (e.g., GenerateUploadUrlAction, SyncPhotoToDriveAction)
└── Exceptions/ # Custom exception handlers
```

### Frontend (`resources/js/`)

```text
resources/js/
├── Pages/ # Inertia pages (Admin/Events, Guest/Landing, Guest/Upload)
├── Components/
│   └── UI/ # Shared primitives (BaseInput, BaseSelect, FileUpload, BaseButton, BaseModal, DataTable, StatCard)
├── Layouts/ # Layout wrappers (AdminLayout, GuestLayout)
├── Composables/ # Reusable Vue logic (e.g., useDirectUpload, useQrCode)
├── Config/ # Static app config — navigation lives here
├── Stores/ # Pinia global state (e.g., useEventStore)
└── Utils/ # Pure helper functions (formatDate, truncateFilename)
```

### Module Structure (modules with > 5 components)

````
Pages/Event/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Components/
│   ├── EventTable.vue
│   ├── EventForm.vue
│   ├── EventFilters.vue
│   ├── EventDeleteModal.vue
│   └── EventStatusBadge.vue
└── Composables/
    ├── useEventForm.js
    └── useEventFilters.js

### File Naming

| Type        | Convention      | Example                |
| ----------- | --------------- | ---------------------- |
| Pages       | PascalCase.vue  | `EventIndex.vue`       |
| Components  | PascalCase.vue  | `FileUpload.vue`       |
| Composables | camelCase.js/ts | `useDirectUpload.ts`   |
| Stores      | camelCase.js/ts | `useEventStore.ts`     |
| Utils       | camelCase.js/ts | `formatDate.ts`        |

---

## 📏 Frontend File Size Rule — STRICT

**Every Vue file (Pages and Components) must not exceed 300 lines.**

### Split strategy & Reusability

| File exceeds 300 lines because of...      | Extract into...                                                    |
| ----------------------------------------- | ------------------------------------------------------------------ |
| Complex upload logic / progress handling  | `Composables/useDirectUpload.ts`                                   |
| Photo grid & masonry layout               | Child components (`GuestPhotoGrid.vue`)                            |
| Repeated UI patterns                      | `Components/UI/` shared component (mandatory for cross-page reuse) |
| QR Code preview & print modal             | `QrCodeModal.vue` with its own composable                          |

---

## Vue 3 Code Style Rules

### `<script setup lang="ts">` block order — always follow this sequence

```vue
<script setup lang="ts">
// 1. Imports
import { ref, computed, watch, onMounted } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import BaseButton from '@/Components/UI/BaseButton.vue'

// 2. Props & emits
const props = defineProps({ ... })
const emit  = defineEmits([...])

// 3. Composables / stores
const page  = usePage()

// 4. Reactive state
const uploadProgress = ref(0)
const loading = ref(false)

// 5. Computed
const isUploaded = computed(() => ...)

// 6. Methods / handlers
const handleUpload = () => { ... }

// 7. Watchers
watch(...)

// 8. Lifecycle hooks
onMounted(() => { ... })
</script>
````

## Shared UI Component Library (MANDATORY)

Always check and use existing components in `resources/js/Components/UI/` before creating new ones.

| Component        | Usage & Reusability Goal                                            |
| ---------------- | ------------------------------------------------------------------- |
| `BaseInput.vue`  | Standard text/email/number inputs with label and error messages     |
| `BaseSelect.vue` | Dropdown select inputs                                              |
| `FileUpload.vue` | File upload with client-side preview & drag-and-drop                |
| `BaseButton.vue` | Primary, secondary, danger, and outline buttons with loading states |
| `BaseModal.vue`  | Reusable animated modal wrapper                                     |
| `DataTable.vue`  | All paginated data tables                                           |
| `DatePicker.vue` | Date/time pickers (wraps `@vuepic/vue-datepicker`)                  |
| `StatCard.vue`   | Event statistics and metric displays                                |

---

### Repositories — all queries live here

```php
class EventRepository
{
    public function paginateWithFilters(array $filters): CursorPaginator
    {
        return Event::query()
            // Selalu eager load count foto dan status sync untuk zero N+1 tolerance
            ->withCount([
                'photos',
                'photos as synced_photos_count' => function ($query) {
                    $query->where('sync_status', 'synced');
                }
            ])
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('bride_name', 'like', "%{$s}%")
                  ->orWhere('groom_name', 'like', "%{$s}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->latest()
            ->cursorPaginate(10);
    }
}
```

### Form Requests — all validation, never inline

```php
class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('events.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'location' => ['required', 'string'],
            'cover_photo_path' => ['nullable', 'string'], // Path dari Supabase Storage
            'gdrive_folder_id' => ['nullable', 'string', 'max:255'], // ID Folder Google Drive mempelai
            'status' => ['required', 'in:active,archived'],
        ];
    }
}
```

### Always eager load — zero N+1 tolerance

```php
Bahan::with(['cabang', 'outlet', 'kategori', 'satuan'])->cursorPaginate(10);
```

## Summary: What AI Must Always Do

- Read this entire file from top to bottom.
- Never exceed 300 lines per Vue file — plan the split before writing.
- Always use and build Reusable Components in `resources/js/Components/UI/` to ensure visual and behavioral consistency.
- Enforce **Direct Upload architecture** (Vue $\rightarrow$ Presigned URL $\rightarrow$ R2/Supabase) to bypass Vercel's 4.5MB payload limit.
- Never axios/fetch for page data — Inertia router only.
- Never `$request->all()` — always `$request->validated()`.
- Never `DB::table()` — Eloquent only.
- Never inline validation — Form Request classes only.
- Never role checks in controllers/components — Policies and Gates only.
- Always use Indonesian locale: date `dd/MM/yyyy`, time `WIB`.
