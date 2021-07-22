<?php
use App\Credentias;

    $grand_type = 'refresh_token';
    $client_id = env('B24_APPLICATION_ID');
    $client_secret = env('B24_APPLICATION_SECRET');

    $portals = Credentias::distinct()
                            ->get();
    foreach ($portals as $item)
    {
        $refresh_token = $item->refresh_id;
        $url = 'https://oauth.bitrix.info/oauth/token/?grant_type='.$grand_type.'&client_id='.$client_id.
            '&client_secret='.$client_secret.'&refresh_token='.$refresh_token;

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $result = json_decode(curl_exec($curl));

        $record = new Credentias;
        $record->domain = $item->domain;
        $record->lang =$item->lang;
        $record->app_sid=$item->app_sid;
        $record->auth_id=$result['access_token'];
        $record->auth_expire = $item->auth_expire ;
        $record->refresh_id = $result['refresh_token'];
        $record->member_id = $item->member_id;
        $record->save();

        curl_close($curl);
    }