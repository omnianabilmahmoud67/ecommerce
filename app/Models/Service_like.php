<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service_like extends Model
{
    protected $guarded = ['id'];

    #service
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    #user
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
