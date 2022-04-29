<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class addressResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id'                => (int)    $this->id,
            'first_name'        => (string) $this->first_name,
            'last_name'         => (string) $this->last_name,
            'country_id'        => (int)    $this->country_id,
            'country_title'     => (string) $this->country->title,
            'city'              => (string) $this->city,
            'first_street'      => (string) $this->first_street,
            'second_street'     => (string) $this->second_street,
            'phone'             => (string) $this->phone,
            'phone_code'        => (string) $this->phone_code,
            'email'             => (string) $this->email,
            'notes'             => (string) $this->notes,
        ];
    }
}
