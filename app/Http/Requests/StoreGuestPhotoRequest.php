<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreGuestPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('publicEvent');

        return $event instanceof Event && $event->status === 'active';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $event = $this->route('publicEvent');
        $eventId = $event instanceof Event ? preg_quote((string) $event->getKey(), '/') : '';

        return [
            'path' => [
                'required',
                'string',
                'max:512',
                'regex:/\Aevents\/'.$eventId.'\/photos\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|png|webp)\z/i',
            ],
            'filename' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', Rule::in(['image/jpeg', 'image/png', 'image/webp'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'path.regex' => 'Lokasi upload foto tidak valid untuk acara ini.',
            'filename.required' => 'Nama file foto tidak tersedia.',
            'content_type.in' => 'Foto harus berformat JPG, PNG, atau WebP.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['path', 'content_type'])) {
                    return;
                }

                $extension = match ($this->validated('content_type')) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                };

                if (! str_ends_with(strtolower($this->validated('path')), ".{$extension}")) {
                    $validator->errors()->add('path', 'Format foto tidak sesuai dengan file yang diunggah.');
                }
            },
        ];
    }
}
