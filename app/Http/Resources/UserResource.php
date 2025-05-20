<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'national_id' => $this->national_id,
            'birth_date'  => $this->birth_date,
            'address'     => $this->address,
            'image'       => asset($this->image),
            'role'        => $this->role?->name,
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];

        // Add extra info based on role
        switch (strtolower($this->role?->name)) {
            case 'doctor':
                $data['education'] = $this->doctor?->education;
                break;
            case 'teacher':
                $data['education'] = $this->teacher?->education;
                break;
            case 'student':
                $data['academic_id'] = $this->student?->academic_id;
                break;
        }

        return $data;
    }
}
