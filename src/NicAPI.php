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

    private static $channels = [];

    public function __construct($apiToken, $url = null, $httpClient = null)
    {
        $this->setApiToken($apiToken);
        $this->setUrl($url ?: 'https://nicapi.eu/api/v1/');
        $this->setHttpClient($httpClient);
    }

    public static function init($apiToken, $url = null, $httpClient = null, $channel = 'default')
    {
        self::$channels[$channel] = new NicAPI($apiToken, $url, $httpClient);
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
        if (!is_array($params)) {
            return false;
        }
        $params['authToken'] = $this->apiToken;

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
            self::$success = $result->status == 'success';

            return $result;
        } else {
            self::$success = false;
            return $response;
        }
    }

    private static $success = null;

    public static function wasSuccess()
    {
        return self::$success;
    }

    public function prepareRequest($path, $data, $method)
    {
        $response = $this->request($path, $data, $method);

        return self::processRequest($response);
    }

    public static function channel($channel) :? NicAPI
    {
        if (!$channel)
            $channel = 'default';

        if (!isset(self::$channels[$channel]) || !(($api = self::$channels[$channel]) instanceof NicAPI)) {
            NicAPI::init(null, 'https://connect.nicapi.eu/api/v1/', null, $channel);
            return self::$channels[$channel];
        }

        return $api;
    }


    public function __call($name, $arguments)
    {
        foreach (['get', 'post', 'put', 'delete'] as $item) {
            if ($name == $item) {
                return call_user_func([$this, 'prepareRequest'], isset($arguments[0]) ? $arguments[0] : null, isset($arguments[1]) ? $arguments[1] : [], strtoupper($item));
            }
        }
    }

    public static function __callStatic($name, $arguments)
    {
        foreach (['get', 'post', 'put', 'delete'] as $item) {
            if ($name == $item) {
                $return = call_user_func([self::class, 'channel'], 'default');
                return call_user_func([$return, $item], $arguments[0], isset($arguments[1]) ? $arguments[1] : null);
            }
        }
    }

}
