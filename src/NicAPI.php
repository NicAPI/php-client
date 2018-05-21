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

    /**
     * @param $path
     * @param array $data
     * @param string $channel
     * @return array|string
     */
    public static function get($path, $data = [], $channel = 'default')
    {
        return self::prepareRequest($path, $data, 'GET', $channel);
    }

    /**
     * @param $path
     * @param array $data
     * @param string $channel
     * @return array|string
     */
    public static function post($path, $data = [], $channel = 'default')
    {
        return self::prepareRequest($path, $data, 'POST', $channel);
    }

    /**
     * @param $path
     * @param array $data
     * @param string $channel
     * @return array|string
     */
    public static function put($path, $data = [], $channel = 'default')
    {
        return self::prepareRequest($path, $data, 'PUT', $channel);
    }

    /**
     * @param $path
     * @param array $data
     * @param string $channel
     * @return array|string
     */
    public static function delete($path, $data = [], $channel = 'default')
    {
        return self::prepareRequest($path, $data, 'DELETE', $channel);
    }

    public static function prepareRequest($path, $data, $method, $channel)
    {
        if (!isset(self::$channels[$channel]))
            return false;

        $api = self::$channels[$channel];
        if (!$api instanceof NicAPI)
            return false;

        $response = $api->request($path, $data, $method);

        return self::processRequest($response);
    }

}
