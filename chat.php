<?php

require 'chatterbotapi.php';

$s = $_POST['question'];

if (preg_match('(^recherche)', $s)) {
    $search = explode('recherche', $s);

    if ($search[1] != null) {
        $url = 'http://api.redtube.com/?data=redtube.Videos.searchVideos&output=json&search=' . urlencode($search[1]) . '&thumbsize=all';

        $json = json_decode(file_get_contents($url));

        if (isset($json->message) && $json->message == "No Videos found!") {
            $json = json_encode(array('url' => $url, 'rep' => 'Rien.. :('));
            echo $json;
        }

        $allVideos = array();
        foreach ($json->videos as $video) {
            array_push($allVideos, $video->video->url);
        }

        $allVideos = array();
        foreach ($json->videos as $video) {
            array_push($allVideos, $video->video->url);
        }

        $s = array_rand($allVideos);
        $json = json_encode(array('url' => $url, 'rep' => '<a href="' . $allVideos[$s] . '">Petit coquin</a>'));

    } else {
        $url = 'http://www.redtube.com/';

        $json = json_encode(array('url' => $url, 'rep' => '<a href="' . $url . '">Petit coquin</a>'));
    }


    echo $json;
} else if (preg_match('(^gif)', $s)) {
    $search = explode('gif', $s);

    if ($search[1] == null) {
        $url = 'http://giphy.com/';
        $json = json_encode(array('url' => $url, 'rep' => '<a href="'.$url.'">Va voir ici ;)</a>'));
        echo $json;
    }

    $url = 'http://api.giphy.com/v1/gifs/search?q=' . urlencode($search[1]) . '&api_key=dc6zaTOxFJmzC';
    $json = json_decode(file_get_contents($url));

    if (empty($json->data)) {
        $json = json_encode(array('url' => $url, 'rep' => 'Rien.. :('));
        echo $json;
    }

    $allGifs = array();
    foreach ($json->data as $gifs) {
        array_push($allGifs, $gifs->images->original->url);
    }

    $s = array_rand($allGifs);
    $json = json_encode(array('url' => $url, 'rep' => '<img style="max-width:300px;" src="' . $allGifs[$s] . '">'));
    echo $json;
} else {

    $factory = new ChatterBotFactory();

    $bot1 = $factory->create(ChatterBotType::CLEVERBOT);
    $bot1session = $bot1->createSession('fr');

    $s = $bot1session->think($s);
    $url = "https://api.naturalreaders.com/v2/tts/?t=" . urlencode($s) . "&r=21&s=1&requesttoken=9b15e67917d975b26e414926a1ec37d";
    $json = json_encode(array('url' => $url, 'rep' => $s));
    echo $json;
}