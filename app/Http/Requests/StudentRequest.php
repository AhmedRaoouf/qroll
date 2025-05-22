<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        $userId = $this->user?->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:15',
            'national_id' => 'required|string|max:255|unique:users,national_id,' . $userId,
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'password' => $this->isMethod('post') ? 'required|string|min:8' : 'nullable|string|min:8',
            'academic_id' => 'required|integer|unique:students,academic_id,' . $this->route('student')?->id,
        ];
    }

    public function messages(): array
    {
        return [
            'academic_id.integer' => 'The academic ID must be an integer.',
            'academic_id.unique' => 'The academic ID has already been taken.',
        ];
    }
}
