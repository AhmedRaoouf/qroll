<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        $studentId = $this->route('student');
        $student = Student::find($studentId) ?? null;
        $userId = $student?->user?->id;

        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'email',
                'max:255',
                'unique:users,email,' . ($userId),
            ],
            'phone' => ['nullable', 'string', 'max:15'],
            'national_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                'unique:users,national_id,' . ($userId),
            ],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8'],
            'academic_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'integer',
                'unique:students,academic_id,' . ($studentId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_id.integer' => 'The academic ID must be an integer.',
            'academic_id.unique' => 'The academic ID has already been taken.',
            'email.unique' => 'The email has already been taken.',
            'national_id.unique' => 'The national ID has already been taken.',
        ];
    }
}
