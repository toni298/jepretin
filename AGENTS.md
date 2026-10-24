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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
