<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class DashboardPeriodRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('access-admin') ?? false; }
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'currency' => ['nullable', 'in:MXN,USD'],
        ];
    }
}
