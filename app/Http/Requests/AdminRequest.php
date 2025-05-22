<?php

namespace App\Http\Requests;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('api')->check(); // Restrict to authenticated API users
    }

    public function rules(): array
    {
        $adminId = $this->route('admin'); // Get admin ID from route
        $admin = Admin::find($adminId) ?? null;
        $userId = $admin?->user?->id; // Get associated user ID

        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'national_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:15'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5096'], // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'The email has already been taken.',
            'national_id.unique' => 'The national ID has already been taken.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image must not exceed 5MB.',
            'password.min' => 'The password must be at least 6 characters.',
        ];
    }
}
