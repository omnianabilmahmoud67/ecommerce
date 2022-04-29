<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class userResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id'                => (int)    $this->id,
            'name'              => (string) $this->name,
            'first_name'        => (string) $this->first_name,
            'last_name'         => (string) $this->last_name,
            'email'             => (string) $this->email,
            'full_phone'        => (string) $this->full_phone,
            'phone'             => (string) $this->phone,
            'phone_code'        => (string) $this->phone_code,
            'is_active'         => $this->active == 1  ? true : false,
            'is_blocked'        => $this->blocked == 1 ? true : false,
            'lang'              => !is_null($this->lang)   ? (string) $this->lang : 'ar',
            'avatar'            => !is_null($this->avatar) ? url('' . $this->avatar) : url('public/user.png'),
        ];
    }
}
