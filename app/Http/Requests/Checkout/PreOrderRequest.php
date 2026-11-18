<?php
namespace App\Http\Requests\Checkout;
use Illuminate\Foundation\Http\FormRequest;
class PreOrderRequest extends FormRequest {
 public function authorize(): bool { return $this->user() !== null; }
 public function rules(): array {
        return [
            'email' => ['required', 'email', 'max:254'],
            'currency' => ['required', 'in:MXN,USD'],
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required', 'integer', 'min:1', 'distinct',
            ],
            'items.*.quantity' => [
                'required', 'integer', 'min:1', 'max:99',
            ],
        ];
 }
}
