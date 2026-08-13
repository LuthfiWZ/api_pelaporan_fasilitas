<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReportStatusLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'note' => $this->note,
            'photo' => $this->photo,
            'photo_url' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'created_at' => $this->created_at,
        ];
    }
}
