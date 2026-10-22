<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:5000'],
            'cover_photo_path' => [
                'nullable',
                'string',
                'max:512',
                $this->ownedCoverPathRule(),
            ],
            'gdrive_folder_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'archived'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Nama acara wajib diisi.',
            'bride_name.required' => 'Nama mempelai wanita wajib diisi.',
            'groom_name.required' => 'Nama mempelai pria wajib diisi.',
            'event_date.required' => 'Tanggal acara wajib diisi.',
            'event_date.date' => 'Tanggal acara tidak valid.',
            'location.required' => 'Lokasi acara wajib diisi.',
            'status.in' => 'Status acara harus aktif atau diarsipkan.',
        ];
    }

    private function ownedCoverPathRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value !== null && ! str_starts_with($value, "covers/{$this->user()->getKey()}/")) {
                $fail('Foto cover harus diunggah melalui akun Anda.');
            }
        };
    }
}
