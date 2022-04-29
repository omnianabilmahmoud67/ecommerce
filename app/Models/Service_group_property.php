<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Service_group_property extends Model
{
    protected $table   = 'service_group_properties';
    protected $guarded = ['id'];

    #service
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    #group
    public function group()
    {
        return $this->belongsTo('App\Models\Service_group');
    }

    #property
    public function property()
    {
        return $this->belongsTo('App\Models\Property');
    }

    #item
    public function item()
    {
        return $this->belongsTo('App\Models\Property_item' , 'property_item_id');
    }
}
