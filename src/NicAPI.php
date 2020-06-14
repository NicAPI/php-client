<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 24.03.18
 * Time: 16:33
 */

namespace NicAPI;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class NicAPI
{

    /** @var Client $httpClient */
    private $httpClient;
    private $url;
    private $apiToken;

    private static $timezone = 'UTC';

    private static $channels = [];

    public function __construct($apiToken, $url = null, $httpClient = null)
    {
        $this->setApiToken($apiToken);
        $this->setUrl($url ?: 'https://connect.nicapi.eu/api/v1/');
        $this->setHttpClient($httpClient);
    }

    public static function init($apiToken, $url = null, $httpClient = null, $channel = 'default')
    {
        static::$channels[$channel] = new static($apiToken, $url, $httpClient);
    }

    public static function setTimezone($tz) {
        static::$timezone = $tz;
    }

    private function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    private function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param $httpClient \GuzzleHttp\Client
     */
    private function setHttpClient($httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Client([
            'allow_redirects' => false,
            'timeout' => 120
        ]);
    }


    /**
     * @param string $actionPath The resource path you want to request, see more at the documentation.
     * @param array $params Array filled with request params
     * @param string $method HTTP method used in the request
     *
     * @return bool|ResponseInterface
     */
    private function request($actionPath, $params = [], $method = 'GET')
    {
        if (substr($actionPath, 0, 8) != 'https://')
            $url = $this->url.$actionPath;
        else
            $url = $actionPath;
//        $url = preg_replace('/(\/+)/','/', $url);

        if (!is_array($params)) {
            return false;
        }
        $params['authToken'] = $this->apiToken;
        $params['config'] = [];
        $params['config']['timezone'] = static::$timezone;

        $params = DateTimeMigrator::formatValues($params);

        switch ($method) {
            case 'GET':
                return $this->httpClient->get($url, [
                    'verify' => false,
                    'query'  => $params,
                ]);
                break;
            case 'POST':
                return $this->httpClient->post($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => $this->apiToken,
                    ],
                    'form_params'   => $params,
                ]);
                break;
            case 'PUT':
                return $this->httpClient->put($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => $this->apiToken,
                    ],
                    'form_params'   => $params,
                ]);
            case 'DELETE':
                return $this->httpClient->delete($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => $this->apiToken,
                    ],
                    'form_params'   => $params,
                ]);
            default:
                return false;
        }
    }

    /**
     * @param $response ResponseInterface
     *
     * @return array|string
     */
    private static function processRequest($response)
    {
        $response = $response->getBody()->__toString();
        $result = json_decode($response);
        if (json_last_error() == JSON_ERROR_NONE) {
            static::$success = $result->status == 'success';

            return $result;
        } else {
            static::$success = false;
            return $response;
        }
    }

    private static $success = null;

    public static function wasSuccess()
    {
        return static::$success;
    }

    public function prepareRequest($path, $data, $method)
    {
        $response = $this->request($path, $data, $method);

        return static::processRequest($response);
    }

    /**
     * @return NicAPI|null
    */
    public static function channel($channel)
    {
        if (!$channel)
            $channel = 'default';

        if (!isset(static::$channels[$channel]) || !(($api = static::$channels[$channel]) instanceof static)) {
            static::init(null, 'https://connect.nicapi.eu/api/v1/', null, $channel);
            return static::$channels[$channel];
        }

        return $api;
    }


    public function __call($name, $arguments)
    {
        foreach (['get', 'post', 'put', 'delete'] as $item) {
            if ($name == $item) {
                dump(get_class($this));
                return call_user_func([$this, 'prepareRequest'], isset($arguments[0]) ? $arguments[0] : null, isset($arguments[1]) ? $arguments[1] : [], strtoupper($item));
            }
        }
    }

    public static function __callStatic($name, $arguments)
    {
        foreach (['get', 'post', 'put', 'delete'] as $item) {
            if ($name == $item) {
                $return = call_user_func([static::class, 'channel'], 'default');
                return call_user_func([$return, $item], $arguments[0], isset($arguments[1]) ? $arguments[1] : null);
            }
        }
    }

}
