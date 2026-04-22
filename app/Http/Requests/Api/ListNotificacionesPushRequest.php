<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListNotificacionesPushRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'estado' => ['nullable', 'in:todas,no_leidas,leidas'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
