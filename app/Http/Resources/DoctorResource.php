<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'user_id' => $this->user_id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'avatar' => $this->user->avatar,
            'email' => $this->user->email,
            'phone_number' => $this->phone_number,
            'achievements' => $this->achievements,
            'specialization' => $this->specialization,
            'created_at' => $this->created_at,
        ];
    }
}
