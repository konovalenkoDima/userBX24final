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
        if ((!is_array($request)) || (count($request) != 10)) {
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
            $record = new Credentias;
            $accessData = $record->getAuthdata($request->DOMAIN);
            if (is_null($accessData)) {
                throw new \Exception('Ошибка записи данных при установке приложения.');
            }
        } catch (\Exception $error) {
            echo $error;
            die();
        }

        $obB24App = new \Bitrix24\Bitrix24(false);
        $obB24App->setApplicationSecret(env('B24_APPLICATION_SECRET'));
        $obB24App->setApplicationId(env('B24_APPLICATION_ID'));
        $obB24App->setApplicationScope((array)json_decode(env('B24_APPLICATION_SCOPE')));

        $obB24App->setDomain($accessData->domain);
        $obB24App->setMemberId($accessData->member_id);
        $obB24App->setAccessToken($accessData->auth_id);
        $obB24App->setRefreshToken($accessData->refresh_id);
        $obB24App->setRedirectUri('/');

        $dateDiff = date_diff(new \DateTime($accessData->updated_at), new \DateTime(), true);

        if (($dateDiff->format('%h') != '0') || ($dateDiff->format('$a')) > 0) {
            $newKeys = $obB24App->getNewAccessToken();
            $record->updateAuthdata($newKeys, $request->DOMAIN);
        }
        $obB24App->setAccessToken($accessData->auth_id);
        $obB24App->setRefreshToken($accessData->refresh_id);

        $obB24User = new \Bitrix24\User\User($obB24App);
        $arBX24Users = $obB24User->get('', '', '');

        return view('userList.index', ['users' => $arBX24Users]);
    }
}