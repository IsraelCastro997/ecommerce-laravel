<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ChangeProductStatusRequest extends FormRequest {
 public function authorize(): bool {return $this->user()?->can('access-admin') ?? false;}
 public function rules(): array {return ['status'=>['required',Rule::in(['active','inactive'])]];}
}
