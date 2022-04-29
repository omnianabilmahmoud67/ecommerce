<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use App\Role;

class User extends Authenticatable
{
    // use EntrustUserTrait;
    use Notifiable;


    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\User_address');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function carts()
    {
        return $this->hasMany('App\Models\Cart');
    }

    #store phone
    public function setPhoneAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['phone'] = NULL;
        } else {
            $this->attributes['phone'] = convert_to_english($value);
        }
    }

    #store email
    public function setEmailAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['email'] = NULL;
        } else {
            $this->attributes['email'] = $value;
        }
    }

    #store lang
    public function setLangAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['lang'] = 'ar';
        } else {
            $this->attributes['lang'] = $value;
        }
    }

    #store password
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) $this->attributes['password'] = bcrypt($value);
    }

    #get name
    public function getNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    #get full phone
    public function getFullPhoneAttribute()
    {
        return convert_phone_to_international_format($this->attributes['phone'], $this->attributes['phone_code']);
    }
}
