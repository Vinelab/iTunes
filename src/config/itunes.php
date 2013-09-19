<?php

return array(

    'api' => array(

        'version' => 2,

        'url' => 'https://itunes.apple.com',

        'search_uri' => '/search',

        'lookup_uri' => '/lookup'
    ),

    'limit' => '50', // 200 max

    'language' => 'en_us', // or ja_jp

    'explicit' => 'Yes', // or No

    'cache' => 60 // in minutes

);