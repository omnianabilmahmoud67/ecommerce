<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service_image extends Model
{
    protected $guarded = ['id'];

    #service
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
