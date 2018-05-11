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
    private static $httpClient;
    private static $url;
    private static $apiToken;

    public function __construct($apiToken, $url = null, $httpClient = null)
    {
        self::setApiToken($apiToken);
        self::setUrl($url ?: 'https://nicapi.eu/api/v1/');
        self::setHttpClient($httpClient);
    }

    public static function init($apiToken, $url = null, $httpClient = null)
    {
        self::setApiToken($apiToken);
        self::setUrl($url ?: 'https://nicapi.eu/api/v1/');
        self::setHttpClient($httpClient);
    }

    public static function setApiToken($apiToken)
    {
        self::$apiToken = $apiToken;
    }

    public static function setUrl($url)
    {
        self::$url = $url;
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


    /**
     * @param string $actionPath The resource path you want to request, see more at the documentation.
     * @param array $params Array filled with request params
     * @param string $method HTTP method used in the request
     *
     * @return bool|ResponseInterface
     */
    private static function request($actionPath, $params = [], $method = 'GET')
    {
        if (!substr($actionPath, 0, 8) == 'https://')
            $url = self::$url.$actionPath;
        else
            $url = $actionPath;
        if (!is_array($params)) {
            return false;
        }
        $params['authToken'] = self::$apiToken;
        switch ($method) {
            case 'GET':
                return self::$httpClient->get($url, [
                    'verify' => false,
                    'query'  => $params,
                ]);
                break;
            case 'POST':
                return self::$httpClient->post($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => self::$apiToken,
                    ],
                    'form_params'   => $params,
                ]);
                break;
            case 'PUT':
                return self::$httpClient->put($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => self::$apiToken,
                    ],
                    'form_params'   => $params,
                ]);
            case 'DELETE':
                return self::$httpClient->delete($url, [
                    'verify' => false,
                    'query'  => [
                        'authToken' => self::$apiToken,
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
            return $result;
        } else {
            return $response;
        }
    }

    /**
     * @param $path
     * @param array $data
     * @return array|string
     */
    public static function get($path, $data = [])
    {
        $response = self::request($path, $data);

        return self::processRequest($response);
    }

    /**
     * @param $path
     * @param array $data
     * @return array|string
     */
    public static function post($path, $data = [])
    {
        $response = self::request($path, $data, 'POST');

        return self::processRequest($response);
    }

    /**
     * @param $path
     * @param array $data
     * @return array|string
     */
    public static function put($path, $data = [])
    {
        $response = self::request($path, $data, 'PUT');

        return self::processRequest($response);
    }

    /**
     * @param $path
     * @param array $data
     * @return array|string
     */
    public static function delete($path, $data = [])
    {
        $response = self::request($path, $data, 'DELETE');

        return self::processRequest($response);
    }

}
