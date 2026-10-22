<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGuestPhotoUploadUrlRequest extends FormRequest
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
        return [
            'content_type' => ['required', 'string', Rule::in(['image/jpeg', 'image/png', 'image/webp'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content_type.in' => 'Foto harus berformat JPG, PNG, atau WebP.',
        ];
    }
}
