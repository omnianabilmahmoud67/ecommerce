<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Country extends Model
{
    protected $guarded = ['id'];

    #neighborhoods
    public function neighborhoods()
    {
        return $this->hasMany('App\Models\Neighborhood');
    }

    #cities
    public function cities()
    {
        return $this->hasMany('App\Models\City');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
