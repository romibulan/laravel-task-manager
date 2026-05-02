<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;


class TaskCollection extends ResourceCollection
{

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }


    public function with($request)
    {
        return [
            'count' => $this->collection->countBy('status'),
        ];
    }
}
