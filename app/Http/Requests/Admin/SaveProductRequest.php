<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function messages(): array { return ['name.regex'=>'El nombre solo admite letras sin acentos, números y espacios.', 'price_mxn.gt'=>'El precio MXN debe ser mayor que cero.', 'price_usd.gt'=>'El precio USD debe ser mayor que cero.', '*.required'=>'Este campo es obligatorio.']; }

    public function rules(): array
    {
        return [
            'sku' => ['required','string','max:64', \Illuminate\Validation\Rule::unique('products','sku')->ignore($this->route('product')?->id)],
            'slug' => ['required','string','max:220','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', \Illuminate\Validation\Rule::unique('products','slug')->ignore($this->route('product')?->id)],
            'name' => ['required','string','max:255','regex:/^[A-Za-z0-9]+(?: [A-Za-z0-9]+)*$/'],
            'short_description' => ['required','string','max:500'],
            'long_description' => ['required','string','max:20000'],
            'price_mxn' => ['required','numeric','gt:0','max:999999.99','decimal:0,2'],
            'price_usd' => ['required','numeric','gt:0','max:999999.99','decimal:0,2'],
            'historical_cost_mxn' => ['required','numeric','min:0','max:999999.9999','decimal:0,4'],
            'stock' => ['required','integer','min:0','max:4294967295'],
            'image_url' => ['required','url:http,https','max:2048'],
            'description' => ['nullable','string','max:20000'],
            'brand_id' => ['nullable','integer','exists:brands,id'],
            'primary_category_id' => ['nullable','integer','exists:categories,id'],
            'origin_country' => ['nullable','string','regex:/^[A-Z]{2}$/'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['draft','active','inactive'])],
            'available_from' => ['nullable','date'],
            'available_until' => ['required','date','after_or_equal:available_from'],
        ];
    }
}
