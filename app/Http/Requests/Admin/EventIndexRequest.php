<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Cursor;
use Illuminate\Validation\Rule;
use Closure;

class EventIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Event::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'cursor' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $parameters = Cursor::fromEncoded($value)?->toArray();

                    if (! is_array($parameters)
                        || ! is_string($parameters['created_at'] ?? null)
                        || ! is_string($parameters['id'] ?? null)
                        || ! is_bool($parameters['_pointsToNextItems'] ?? null)) {
                        $fail('Kursor halaman tidak valid.');
                    }
                },
            ],
        ];
    }
}
