<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Order extends Model
{
    protected $table   = 'orders';
    protected $guarded = ['id'];

    #items
    public function items()
    {
        return $this->hasMany('App\Models\Order_item');
    }

    #promo
    public function promo()
    {
        return $this->belongsTo('App\Models\Promo_code' , 'promo_id');
    }
    
    #country
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    #address
    public function address()
    {
        return $this->belongsTo('App\Models\User_address');
    }

    #user
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
