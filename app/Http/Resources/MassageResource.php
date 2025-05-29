<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MassageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => [
                'id' => $this->sender->admin->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
            ],
            'receiver' => [
                'id' => $this->receiver->studnet->id,
                'name' => $this->receiver->name,
                'email' => $this->receiver->email,
            ],
            'message' => $this->message,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
