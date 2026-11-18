<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CatalogIndexRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:150'],
            'currency' => ['sometimes', 'in:MXN,USD'],
            'stock' => ['sometimes', 'boolean'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'in:newest,name,price_asc,price_desc'],
        ];
    }
}
