<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $this->user()->id,
            'phone'       => 'nullable|string|max:15',
            'national_id' => 'required|string|unique:users,national_id,' . $this->user()->id,
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string|max:255',
            'image'     => 'nullable|image|max:5096',
        ];
    }
}
