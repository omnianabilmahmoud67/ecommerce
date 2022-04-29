<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\sub_sectionResource;

class sectionResource extends JsonResource
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
            'id'           => (int)    $this->id,
            'title'        => (string) $this->title,
            'image'        => !is_null($this->image) ? url('' . $this->image) : url('public/none.png'),
            'sub_sections' => sub_sectionResource::collection($this->sub_sections),
        ];
    }
}
