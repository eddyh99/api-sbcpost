<?php
use App\Models\ValidateToken;

function getAppId($token){
    $token=explode(" ",$token)[1];
    $validtoken = new ValidateToken();
    $app = $validtoken->checkAPIkey($token);
    return $app->appid;
}

