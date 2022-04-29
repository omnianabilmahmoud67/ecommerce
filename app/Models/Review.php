<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;
use App\Models\Service_review;

class Review extends Model
{
    protected $guarded = ['id'];

    #reviews
    public function reviews()
    {
        return $this->hasMany('App\Models\Service_review');
    }

    #reviews
    static function reviews_count($service_id , $review_id)
    {
        $count = Service_review::where('service_id' , $service_id)->where('review_id' , $review_id)->count();
        return $count;
    }

    #get title
    public function getTitleAttribute()
    {
        $attr = 'title_' . App::getLocale();
        return $this->attributes[$attr];
    }
}
