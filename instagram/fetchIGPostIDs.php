<?php 
    function delete_files($target) {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK );
            foreach( $files as $file ){
                delete_files( $file );      
            }
            if(file_exists($target)) 
                rmdir( $target );
        } elseif(is_file($target)) {
            unlink( $target );  
        }
    }
    $myfile = fopen("accessToken.json", "r+") or die("Unable to open file!");
    $token = json_decode(fread($myfile, filesize("accessToken.json")));
    fclose($myfile);
    $url = 'https://graph.instagram.com/me/media?fields=id&access_token='.$token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "I-am-browser");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);

    if(is_array($response) && !array_key_exists("error", $response) && array_key_exists("data", $response)){

        $data = $response['data'];
        if(file_exists("posts")){
            delete_files("posts");
        }
        mkdir("posts", 0777, true);
        $i = 1;
        foreach ($data as $post) {
            if($i > 4) break;
            $url = "https://graph.instagram.com/".$post['id']."?fields=media_type,media_url,thumbnail_url&access_token=".$token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "I-am-browser");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, true);

            $mediaURL = $response['media_url'];
            $extension = pathinfo(parse_url($mediaURL, PHP_URL_PATH), PATHINFO_EXTENSION);
            if($extension == "mp4") {
                continue;
                /*$thumbnail = $response['thumbnail_url'];
                file_put_contents("posts/".$i.".jpg", file_get_contents($thumbnail));*/
            } else {
                file_put_contents("posts/".$i.".jpg", file_get_contents($mediaURL));
                $i++;
            }

            echo "imgHasBeenDownloaded? ";
            var_dump(file_exists("posts/".$i.".jpg"));
        }
    }
?>