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

    public function getAuthData($domain)
    {
        $record = Credentias::where('domain', $domain)
            ->first();

        return $record;
    }

    public function updateAuthData($keyArray, $domain)
    {
        Credentias::where('domain', $domain)
            ->update([
                'auth_id',
                $keyArray['access_token'],
                'refresh_id',
                $keyArray['refresh_token']
            ]);

        return true;
    }
}
