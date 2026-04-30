<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;



class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   =>  $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'extra' => $this->mergeWhen(isset($this->transitions), [
                'transitions' => $this->transitions,
                'label' => $this->label,
                'color' => $this->color,
            ]),

        ];
    }
}
