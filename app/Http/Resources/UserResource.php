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
                $data['doctor_id'] = $this->doctor?->id;
                $data['education'] = $this->doctor?->education;
                $data['courses']   = CourseResource::collection($this->doctor?->courses);
                break;

            case 'teacher':
                $data['teacher_id'] = $this->teacher?->id;
                $data['education']  = $this->teacher?->education;
                $data['courses']    = CourseResource::collection($this->teacher?->courses);
                break;

            case 'student':
                $data['student_id']  = $this->student?->id;
                $data['academic_id'] = $this->student?->academic_id;
                $data['courses']     = CourseResource::collection($this->student?->courses);
                break;
        }


        return $data;
    }
}
