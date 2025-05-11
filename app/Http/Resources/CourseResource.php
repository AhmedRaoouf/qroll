<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'doctor'     => $this->doctor ? [
                'id'   => $this->doctor->id,
                'name' => $this->doctor->user->name ?? null,
            ] : null,
            'teacher'    => $this->teacher ? [
                'id'   => $this->teacher->id,
                'name' => $this->teacher->user->name ?? null,
            ] : null,
        ];
    }
}
