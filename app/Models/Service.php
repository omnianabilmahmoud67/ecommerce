<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service extends Model
{
    protected $guarded = ['id'];

    #section
    public function section()
    {
        return $this->belongsTo('App\Models\Section');
    }

    #sub_section
    public function sub_section()
    {
        return $this->belongsTo('App\Models\Sub_section');
    }

    #images
    public function images()
    {
        return $this->hasMany('App\Models\Service_image');
    }

    #groups
    public function groups()
    {
        return $this->hasMany('App\Models\Service_group');
    }

    #group_properties
    public function group_properties()
    {
        return $this->hasMany('App\Models\Service_group_property');
    }

    #likes
    public function likes()
    {
        return $this->hasMany('App\Models\Service_like');
    }

    #reviews
    public function reviews()
    {
        return $this->hasMany('App\Models\Service_review')->orderBy('id' , 'desc');
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }

    #get short_desc
    public function getShortDescAttribute()
    {
        $attr = 'short_desc_' . App::getLocale();
        return $this->attributes[$attr];
    }

    #get desc
    public function getDescAttribute()
    {
        $attr = 'desc_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
