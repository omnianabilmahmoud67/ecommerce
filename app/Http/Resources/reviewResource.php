<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class reviewResource extends JsonResource
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
            'rate'         => (float)  $this->rate,
            'review_id'    => (int)    $this->review_id,
            'review_title' => (string) $this->review->title,
        ];
    }
}
