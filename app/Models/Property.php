<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Property extends Model
{
    protected $table   = 'properties';
    protected $guarded = ['id'];

    #items
    public function items()
    {
        return $this->hasMany('App\Models\Property_item' , 'property_id' , 'id');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
