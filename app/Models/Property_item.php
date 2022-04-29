<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Property_item extends Model
{
    protected $table   = 'property_items';
    protected $guarded = ['id'];

    #property
    public function property()
    {
        return $this->belongsTo('App\Models\Property' , 'property_id ' , 'id');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
