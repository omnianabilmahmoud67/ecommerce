<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Neighborhood extends Model
{
    protected $guarded = ['id'];

    #city
    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    #country
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
