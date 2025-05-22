<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('api')->check();
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher');
        $teacher = Teacher::find($teacherId) ?? null;
        $userId = $teacher?->user?->id;

        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'email',
                'max:255',
                'unique:users,email,' . ($userId ?? null),
            ],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:15'],
            'national_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                'unique:users,national_id,' . ($userId ?? null),
            ],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5096'], // Max 5MB
            'education' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'The email has already been taken.',
            'national_id.unique' => 'The national ID has already been taken.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image must not exceed 5MB.',
        ];
    }
}
