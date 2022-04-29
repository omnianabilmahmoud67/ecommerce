<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\dataResource;

class groupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            // 'id'       => (int) $this->id,
            'title'    => (string) $this->property->title,
            'value'    => (string) $this->item->title_en,
        ];
    }
}
