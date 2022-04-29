<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Review;
use App\Models\Service_review;

class cartResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id'                 => (int)    $this->id,
            'title'              => (string) $this->service->title,
            'desc'               => (string) $this->service->desc,
            'service_id'         => (int)    $this->service_id,
            // 'group_id'           => (int)    $this->group_id,
            'size'               => (string) $this->size,
            'color'              => (string) $this->color,
            'color_hex'          => (string) $this->hex,
            'total'              => (float)  $this->total,
            'count'              => (int)    $this->count,
            'delivery'           => (float)  $this->service->delivery,
            'first_image'        => $this->service->images->count() > 0 ? url('' . $this->service->images->first()->image) : url('public/none.png'),
            // 'groups'             => groupResource::collection($this->group->group_properties),
        ];
    }
}
