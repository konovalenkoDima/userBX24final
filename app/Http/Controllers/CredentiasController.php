<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Credentias;
use Illuminate\Database\Eloquent\Model;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class CredentiasController extends Controller
{
    public function setCredentias(Request $request)
    {
        if ((!is_array($request)) || (count($request)!=10)){
            $record = new Credentias;
            $record->domain = $request->DOMAIN;
            $record->lang = $request->LANG;
            $record->app_sid = $request->APP_SID;
            $record->auth_id = $request->AUTH_ID;
            $record->auth_expire = $request->AUTH_EXPIRES;
            $record->refresh_id = $request->REFRESH_ID;
            $record->member_id = $request->member_id;
            $record->save();
            return view('install.install');
        } else {
            echo 'Incorrect data in Request';
            die();
        }

    }

    public function getUser(Request $request)
    {
        try {
            $record = Credentias::where('domain', $request->DOMAIN)
                ->latest()
                ->first();
            if (is_null($record)){
                throw new \Exception('Ошибка записи данных при установке приложения.');
            }
        } catch (\Exception $error){
            echo $error;
            die();
        }

        if (date_diff(new \DateTime($record->created_at), new \DateTime())->format("h") >= 1)
        {
            $grand_type = 'refresh_token';
            $client_id = env('B24_APPLICATION_ID');
            $client_secret = env('B24_APPLICATION_SECRET');
            $refresh_token = $record->refresh_id;
            $url = 'https://oauth.bitrix.info/oauth/token/?grant_type='.$grand_type.'&client_id='.$client_id.
                    '&client_secret='.$client_secret.'&refresh_token='.$refresh_token;

            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_HEADER => true,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true
            ]);
            $result = json_decode(curl_exec($curl));

            $newRecord = new Credentias;
            $newRecord->domain = $record->domain;
            $newRecord->lang =$record->lang;
            $newRecord->app_sid=$record->app_sid;
            $newRecord->auth_id=$result['access_token'];
            $newRecord->auth_expire = $record->auth_expire ;
            $newRecord->refresh_id = $result['refresh_token'];
            $newRecord->member_id = $record->member_id;
            $newRecord->save();

            curl_close($curl);
            $record = Credentias::where('domain', $request->DOMAIN)
                ->latest()
                ->first();
        }

        $obB24App = new \Bitrix24\Bitrix24(false);
        $obB24App->setApplicationSecret(env('B24_APPLICATION_SECRET'));
        $obB24App->setApplicationId(env('B24_APPLICATION_ID'));
        $obB24App->setApplicationScope((array)json_decode(env('B24_APPLICATION_SCOPE')));

        $obB24App->setDomain($record->domain);
        $obB24App->setMemberId($record->member_id);
        $obB24App->setAccessToken($record->auth_id);
        $obB24App->setRefreshToken($record->refresh_id);

        $obB24User = new \Bitrix24\User\User($obB24App);
        $arBX24Users = $obB24User->get('', '', '');

        return view('userList.index', ['users' => $arBX24Users]);
    }
}