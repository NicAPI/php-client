<?php

namespace Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use NicAPI\NicAPI;
use PHPUnit\Framework\TestCase;

class TlsVerificationTest extends TestCase
{

    public function methodProvider()
    {
        return [['get'], ['post'], ['put'], ['delete']];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testCertificatesAreVerifiedByDefault($method)
    {
        $history = [];
        $api = new NicAPI('token', 'https://connect.nicapi.eu/api/v1/', $this->client($history));

        $api->$method('dummy', []);

        $this->assertCount(1, $history);
        $this->assertTrue($history[0]['options']['verify']);
    }

    /**
     * @dataProvider methodProvider
     */
    public function testClientVerifyConfigIsNotOverridden($method)
    {
        $history = [];
        $api = new NicAPI('token', 'https://connect.nicapi.eu/api/v1/', $this->client($history, ['verify' => '/path/to/ca.pem']));

        $api->$method('dummy', []);

        $this->assertCount(1, $history);
        $this->assertSame('/path/to/ca.pem', $history[0]['options']['verify']);
    }

    private function client(array &$history, array $config = [])
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"status":"success"}'),
        ]));
        $stack->push(Middleware::history($history));

        return new Client($config + ['handler' => $stack]);
    }

}
