<?php namespace iNeed;

use GuzzleHttp\Client;

include __DIR__ .'/../vendor/autoload.php';

class Team10DB {
    private static $root = 'https://ineed-db.mybluemix.net/api/';
    private static $initialized = false;
    private static $client;

    private static function intialize() {
        if(self::$initialized)
            return;

        self::$client = new Client();
    }


    static function post($path, $data) {
        self::intialize();

        $res = self::$client->post(self::$root . $path, [
            'json' => $data
        ]);

        return $res;
    }
}