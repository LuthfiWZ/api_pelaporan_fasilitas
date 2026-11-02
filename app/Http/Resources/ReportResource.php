<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'reporter' => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'building' => $this->building,
            'room' => $this->room,
            'photo' => $this->photo,
            'photo_url' => $this->photo
                ? $request->getSchemeAndHttpHost().'/storage/'.ltrim((string) $this->photo, '/')
                : null,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'note' => $this->note,
            'timeline' => ReportStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
