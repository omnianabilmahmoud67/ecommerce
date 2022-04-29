<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class User_address extends Model
{
    protected $table   = 'user_addresses';
    protected $guarded = ['id'];

    #country
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    #user
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
