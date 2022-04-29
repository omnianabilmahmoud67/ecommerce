<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Order_item extends Model
{
    protected $table   = 'order_items';
    protected $guarded = ['id'];

    #order
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }
    
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
}
