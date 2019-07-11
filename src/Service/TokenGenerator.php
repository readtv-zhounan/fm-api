<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\HttpClient\HttpClient;

class TokenGenerator
{
    private $host;
    private $clientId;
    private $clientSecret;
    private $memcacheUrl;

    public function __construct($host, $clientId, $clientSecret, $memcacheUrl)
    {
        $this->host = $host;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->memcacheUrl = $memcacheUrl;
    }

    public function getToken()
    {
        $client = MemcachedAdapter::createConnection(
            $this->memcacheUrl
        );
        $cache = new MemcachedAdapter($client, $namespace = '', 0);
        $token = $cache->getItem('access_token');
        if ($token->isHit()) {
            return $token->get();
        }
        $tokenResponse = json_decode($this->getTokenResponseFromApi(), true);
        $token->set($tokenResponse['data']['access_token'])->expiresAfter($tokenResponse['data']['expires_in']);
        $cache->save($token);

        return $tokenResponse['data']['access_token'];
    }

    private function getTokenResponseFromApi()
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $this->host.'/auth/v7/access', [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);
        if ($response->getStatusCode() == 200) {
            return $response->getContent();
        }
        throw new \Exception($response->getContent());
    }
}
