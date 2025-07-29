<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            'patient' => new PatientResource($this->patient),
            'doctor' => new DoctorResource($this->doctor),
            'rate' => $this->rate,
            'comment' => $this->comment,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
