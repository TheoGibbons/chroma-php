<?php

namespace TheoGibbons\ChromaPHP;

use GuzzleHttp\Client;

class ChromaDBInternals
{

    function __construct(
        public Client $httpClient
    )
    {
    }

    public function getText(string $path): string
    {
        try {
            $response = $this->httpClient->get($path);
            return trim($response->getBody()->getContents(), '"');
        } catch (\Exception $e) {
            $this->handleChromaApiException($e);
        }
    }

    public function getJson(string $path): array
    {
        try {
            $response = $this->httpClient->get($path);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->handleChromaApiException($e);
        }
    }

    public function postJson(string $path, array $payload): array
    {
        try {
            $response = $this->httpClient->post($path, [
                'json' => $payload
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->handleChromaApiException($e);
        }
    }

    public function putJson(string $path, array $payload): array
    {
        try {
            $response = $this->httpClient->put($path, [
                'json' => $payload,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->handleChromaApiException($e);
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->httpClient->delete($path);
        } catch (\Exception $e) {
            $this->handleChromaApiException($e);
        }
    }

    private function handleChromaApiException(\Exception $e): void
    {
        throw $e;
    }

}