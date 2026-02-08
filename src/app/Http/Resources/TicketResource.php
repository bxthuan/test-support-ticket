<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'ai_analysis' => $this->when($this->isAIProcessed(), [
                'category' => $this->category,
                'sentiment' => $this->sentiment,
                'suggested_reply' => $this->suggested_reply,
                'processed_at' => $this->ai_processed_at?->toIso8601String(),
            ]),
            'processing_status' => $this->isAIProcessed() ? 'completed' : 'pending',
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
