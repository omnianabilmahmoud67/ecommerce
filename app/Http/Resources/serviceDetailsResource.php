<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Review;
use App\Models\Service_review;

class serviceDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id'                 => (int)    $this->id,
            'title'              => (string) $this->title,
            'price'              => (float)  $this->price,
            'delivery'           => (float)  $this->delivery,
            'desc'               => (string) $this->desc,
            'additional_info'    => (string) $this->short_desc,
            'first_image'        => $this->images->count() > 0 ? url('' . $this->images->first()->image) : url('public/none.png'),
            'is_fav'             => $request->has('user_id') ? is_favourite($request->user_id , $this->id) : false,
            'rate'               => (float) $this->reviews->avg('rate'),
            'rate_count'         => (float) $this->reviews->count(),
            'images'             => imageResource::collection($this->images),
            'groups'             => get_service_group($this->id),
            'reviews_data'       => reviewDataResource::collection(Review::get()),
            'service_reviews'    => reviewResource::collection(Service_review::where('service_id' , $this->id)->get()),
        ];
    }
}
