<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class User extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'photo',
        'role', 
        'api_token',
        'email_verified_at',
        'provider_name',
        'provider_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token'
    ];
}
