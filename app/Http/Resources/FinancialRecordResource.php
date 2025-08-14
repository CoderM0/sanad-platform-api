<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialRecordResource extends JsonResource
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
            'patient' => $this->patient ? new PatientResource($this->patient) : null,
            'doctor' => $this->doctor ? new DoctorResource($this->doctor) : null,
            'card_number' => $this->card_number,
            'amount' => $this->amount,
            'reservation_date' => $this->reservation_date,
            'status' => $this->status == "unpaid" ? "غير مدفوع" : " مدفوع",
            'updated_at' => $this->updated_at
        ];
    }
}
