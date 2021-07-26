<?php

use App\Credentias;

$grant_type = 'refresh_token';
$client_id = env('B24_APPLICATION_ID');
$client_secret = env('B24_APPLICATION_SECRET');

$portals = Credentias::all();
foreach ($portals as $item) {
    $dateDiff = date_diff(new \DateTime($item->updated_at), new \DateTime(), true);
    if (($dateDiff->format('%h') != '0') || ($dateDiff->format('$a')) > 0) {
        $obB24App = new \Bitrix24\Bitrix24(false);
        $obB24App->setApplicationSecret(env('B24_APPLICATION_SECRET'));
        $obB24App->setApplicationId(env('B24_APPLICATION_ID'));
        $obB24App->setApplicationScope((array)json_decode(env('B24_APPLICATION_SCOPE')));

        $obB24App->setDomain($item->domain);
        $obB24App->setMemberId($item->member_id);
        $obB24App->setAccessToken($item->auth_id);
        $obB24App->setRefreshToken($item->refresh_id);
        $obB24App->setRedirectUri('/');

        $newKeys = $obB24App->getNewAccessToken();
        $record = new Credentias;
        $newAuth = $record->updateAuthdata($newKeys, $item->domain);

        $obB24App->setAccessToken($item->auth_id);
        $obB24App->setRefreshToken($item->refresh_id);
    }
}