<?php 
    $myfile = fopen("accessToken.json", "r+") or die("Unable to open file!");

    $token = json_decode(fread($myfile,filesize("accessToken.json")));
    
    $url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token='.$token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "I-am-browser");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);
    if(is_array($response) && !array_key_exists("error", $response) && array_key_exists("access_token", $response)){
        $newToken = $response['access_token'];
        fclose($myfile);
        unlink("accessToken.json");
        touch("accessToken.json");
        $myfile = fopen("accessToken.json", "r+") or die("Unable to open file!");
        fwrite($myfile, json_encode($newToken));   
    }
    fclose($myfile);
?>