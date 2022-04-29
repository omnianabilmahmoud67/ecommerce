<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class City extends Model
{
    protected $guarded = ['id'];

    #country
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    #neighborhoods
    public function neighborhoods()
    {
        return $this->hasMany('App\Models\Neighborhood');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
