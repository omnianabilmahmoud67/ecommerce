<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App;

class order_itemResource extends JsonResource
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

        $service_title = App::getLocale() == 'en' ? 'service_title_en' : 'service_title_ar';
        return [
            'id'            => (int)     $this->id,
            'service_id'    => (int)     $this->service_id,
            'service_title' => (string)  $this->$service_title,
            'first_image'   => !is_null($this->service) && $this->service->images->count() > 0 ? url('' . $this->service->images->first()->image) : url('public/none.png'),
            'count'         => (float)   $this->count,
            'total'         => (float)   $this->total,
            'delivery'      => (float)   $this->delivery,
            'size'          => (string)  $this->size,
            'color'         => (string)  $this->color,
        ];
    }
}
