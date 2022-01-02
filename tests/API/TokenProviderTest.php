<?php

namespace API;

use GuzzleHttp\Exception\GuzzleException;
use Jaschavanaalst\Discogs\API\TokenProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class TokenProviderTest extends TestCase
{
    public function testConstruct(): TokenProvider
    {
        $provider = new TokenProvider();

        self::assertInstanceOf(TokenProvider::class, $provider);

        return $provider;
    }

    /**
     * @depends testConstruct
     * @throws GuzzleException
     */
    public function testRequestToken(TokenProvider $provider): void
    {
        $response = $provider->requestToken();

        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @depends testConstruct
     * @throws GuzzleException
     */
    public function testGetTokenData(TokenProvider $provider): void
    {
        $data = $provider->getTokenData();

        self::assertIsArray($data);
        self::assertArrayHasKey('oauth_token', $data);
        self::assertArrayHasKey('oauth_token_secret', $data);
    }

    /**
     * @depends testConstruct
     * @throws GuzzleException
     */
    public function testGetToken(TokenProvider $provider): void
    {
        $provider->getToken();
    }
}
