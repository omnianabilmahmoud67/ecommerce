<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service_group extends Model
{
    protected $guarded = ['id'];

    #service
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    #group_properties
    public function group_properties()
    {
        return $this->hasMany('App\Models\Service_group_property' , 'group_id');
    }
}
