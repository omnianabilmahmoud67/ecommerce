<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Cart extends Model
{
    protected $table   = 'carts';
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
        return $this->belongsTo('App\Models\Service_group_property');
    }

    #user
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
