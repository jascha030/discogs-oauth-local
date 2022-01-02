<?php

namespace Jaschavanaalst\Discogs\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
use Swoole\Coroutine\Http\Server;
use function Co\run;

class TokenProvider
{
    private array $data;

    /**
     * @throws GuzzleException
     */
    public function getToken(): bool
    {
        $this->data = $this->getTokenData();
        $this->authorizeRequest($this->data['oauth_token']);

        return $this->responseServer();
    }

    /**
     * @throws GuzzleException
     */
    public function getTokenData(): array
    {
        $response = $this->requestToken();

        parse_str($response->getBody()->getContents(), $data);

        return $data;
    }

    public function responseServer(): bool
    {
        return run(function () {
            $server = @new Server('127.0.0.1', 8080);

            @$server->handle('/', function (\Swoole\Http\Request $request, Response $response) use ($server) {
                if (isset ($request->get['oauth_token'])) {
                    file_put_contents(dirname(__FILE__, 3) . '/token.json', json_encode($request->get, JSON_THROW_ON_ERROR));
                    $response->end("Discogs has authorized your request.");
                    @$server->shutdown();
                }
            });

            @$server->start();
        });
    }

    /**
     * @throws GuzzleException
     */
    public function requestToken(): ResponseInterface
    {
        $request = new Request('GET', 'https://api.discogs.com/oauth/request_token', [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => implode(',', [
                'OAuth oauth_consumer_key="dcHnQedYLAmhBgBBkZQA"',
                'oauth_nonce="' . time() . '"',
                'oauth_signature="HFqaUwdVfXmqVxLsOFwWjJdqJqnnIcwc&"',
                'oauth_signature_method="PLAINTEXT"',
                'oauth_timestamp="' . time() . '"',
                'oauth_callback="http://127.0.0.1:8080/"',
            ])
        ]);

        return (new Client())->send($request);
    }

    public function authorizeRequest(string $token): void
    {
        shell_exec("open https://discogs.com/oauth/authorize?oauth_token={$token}");
    }
}