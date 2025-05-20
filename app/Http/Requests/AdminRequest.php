<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user?->id ?? null;
        return [
            'name'        => 'required|string|max:255',
            'email'       => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($userId)],
            'national_id' => ['sometimes', 'required', Rule::unique('users')->ignore($userId)],
            'password'    => 'required|string|min:6',
            'phone'       => 'nullable|string',
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string',
            'image'       => 'nullable|image|max:5096',
        ];
    }
}
