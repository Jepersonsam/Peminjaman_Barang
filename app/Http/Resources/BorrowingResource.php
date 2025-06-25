<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'code' => $this->user->code,
            ],
            'item' => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'serial_code' => $this->item->serial_code,
            ],
            'borrow_date' => $this->borrow_date->format('Y-m-d'),
            'return_date' => optional($this->return_date)->format('Y-m-d'),
            'is_returned' => $this->is_returned,
        ];
    }
}
