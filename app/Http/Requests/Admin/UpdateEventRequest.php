<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->can('update', $event) ?? false);
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

    private function ownedCoverPathRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value !== null && ! str_starts_with($value, "covers/{$this->user()->getKey()}/")) {
                $fail('Foto cover harus diunggah melalui akun Anda.');
            }
        };
    }
}
