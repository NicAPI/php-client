<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 24.03.18
 * Time: 17:13
 */

namespace NicAPI;


use GuzzleHttp\Client;

abstract class Handler
{

    private static $httpClient;
    private static $authToken;

    public static function setApiToken($authToken) {
        self::$authToken = $authToken;
    }

    /**
     * @param $httpClient \GuzzleHttp\Client
     */
    public static function setHttpClient($httpClient = null)
    {
        self::$httpClient = $httpClient ?: new Client([
            'allow_redirects' => false,
            'timeout' => 120
        ]);
    }

    public function __construct($authToken, $httpClient)
    {
        self::setApiToken($authToken);
        self::setHttpClient($httpClient);
    }

}