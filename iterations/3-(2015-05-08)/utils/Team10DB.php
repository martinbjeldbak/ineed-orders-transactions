<?php namespace iNeed;

use GuzzleHttp\Message\ResponseInterface;

include __DIR__ .'/../vendor/autoload.php';

class Team10DB {
    private static $root = 'https://ineed-db.mybluemix.net/api/';
    private static $initialized = false;
    private static $client;

    private static function intialize() {
        if(self::$initialized)
            return;

        self::$client = new \GuzzleHttp\Client();
    }


    /**
     * Makes a POST request to Team 10's database endpoint.
     *
     * @param $paths String Relative path (appended to https://ineed-db.mybluemix.net/api/)
     * @param $data Array of data to be sent
     * @return ResponseInterface with data
     */
    static function post($paths, $data) {
        self::intialize();

        $res = self::$client->post(self::$root . $paths, [
            'json' => $data
        ]);

        return $res;
    }
}