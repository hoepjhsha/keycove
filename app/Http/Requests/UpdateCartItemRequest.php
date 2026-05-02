<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Abstracts\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
