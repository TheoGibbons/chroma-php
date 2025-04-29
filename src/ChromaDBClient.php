<?php

namespace TheoGibbons\ChromaPHP;

class ChromaDBClient
{

    public static function connect(array $options = []): ChromaDB
    {
        $host = $options['host'] ?? 'localhost';
        $port = $options['port'] ?? 8000;
        $authToken = $options['authToken'] ?? null;

        $baseUrl = $host . ':' . $port;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if (!empty($authToken)) {
            $headers['Authorization'] = 'Bearer ' . $authToken;
        }

        return new ChromaDB(new \GuzzleHttp\Client(['base_uri' => $baseUrl, 'headers' => $headers,]));
    }

}