<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credentias extends Model
{
    protected $table = 'credentias';

    protected $fillable = [
        'domain',
        'lang',
        'app_sid',
        'auth_id',
        'auth_expire',
        'refresh_id',
        'member_id'
    ];
}
