<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class sub_sectionResource extends JsonResource
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
            'id'       => (int)    $this->id,
            'title'    => (string) $this->title,
            'image'    => !is_null($this->image) ? url('' . $this->image) : url('public/none.png'),
        ];
    }
}
