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
        return Auth::guard('api')->check();

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::guard()->user();
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'phone'       => 'sometimes|string|max:15',
            'national_id' => 'required|string|unique:users,national_id,' . $user->id,
            'birth_date' => 'sometimes|date|date_format:Y-m-d',
            'address'     => 'sometimes|string|max:255',
            'image'     => 'sometimes|image|max:5096',
        ];
    }
}
