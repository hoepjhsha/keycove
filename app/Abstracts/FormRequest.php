<?php

declare(strict_types=1);

namespace App\Abstracts;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

abstract class FormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        //
    }

    /**
     * Get the validated data from the request with only specified keys.
     */
    public function validatedOnly(array $keys): array
    {
        return collect($this->validated())
            ->only($keys)
            ->toArray();
    }

    /**
     * Get the validated data from the request except specified keys.
     */
    public function validatedExcept(array $keys): array
    {
        return collect($this->validated())
            ->except($keys)
            ->toArray();
    }
}
