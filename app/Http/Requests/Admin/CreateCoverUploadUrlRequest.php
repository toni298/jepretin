<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCoverUploadUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('uploadCover', Event::class) ?? false;
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
            'content_type.in' => 'Foto cover harus berformat JPG, PNG, atau WebP.',
        ];
    }
}
