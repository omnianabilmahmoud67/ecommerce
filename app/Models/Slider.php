<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded = ['id'];

    #get desc
    public function getDescAttribute()
    {
        $attr = 'desc_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
