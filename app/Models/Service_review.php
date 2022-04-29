<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service_review extends Model
{
    protected $guarded = ['id'];

    #service
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    #review
    public function review()
    {
        return $this->belongsTo('App\Models\Review');
    }
}
