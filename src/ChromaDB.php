<?php

namespace TheoGibbons\ChromaPHP;

class ChromaDB extends ChromaDBInternals
{
    const URI_PREFIX = "/api/v2";

    private function uri(string $pathTemplate, array $params = []): string
    {
        foreach ($params as $key => $value) {
            $encoded = rawurlencode((string)$value);
            $pathTemplate = str_replace("{" . $key . "}", $encoded, $pathTemplate);
        }

        return self::URI_PREFIX . '/' . ltrim($pathTemplate, '/');
    }

    public function heartbeat(): array
    {
        return $this->getJson($this->uri('heartbeat'));
    }

    public function healthcheck(): array
    {
        return $this->getJson($this->uri('healthcheck'));
    }

    public function preFlightChecks(): array
    {
        return $this->getJson($this->uri('pre-flight-checks'));
    }

    public function version(): string
    {
        return $this->getText($this->uri('version'));
    }

    public function identity(): array
    {
        return $this->getJson($this->uri('auth/identity'));
    }

    public function reset(): bool
    {
        $response = $this->postJson($this->uri('reset'), []);
        return ($response['ok'] ?? false) === true;
    }

    public function createTenant(array $payload): array
    {
        return $this->postJson($this->uri('tenants'), $payload);
    }

    public function getTenant(string $tenant): array
    {
        return $this->getJson($this->uri("tenants/{tenant}", ['tenant' => $tenant]));
    }

    public function listDatabases(string $tenant): array
    {
        return $this->getJson($this->uri("tenants/{tenant}/databases", ['tenant' => $tenant]));
    }

    public function createDatabase(string $tenant, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases", ['tenant' => $tenant]), $payload);
    }

    public function getDatabase(string $tenant, string $database): array
    {
        return $this->getJson($this->uri("tenants/{tenant}/databases/{database}", [
            'tenant'   => $tenant,
            'database' => $database,
        ]));
    }

    public function deleteDatabase(string $tenant, string $database): void
    {
        $this->delete($this->uri("tenants/{tenant}/databases/{database}", [
            'tenant'   => $tenant,
            'database' => $database,
        ]));
    }

    public function listCollections(string $tenant, string $database): array
    {
        return $this->getJson($this->uri("tenants/{tenant}/databases/{database}/collections", [
            'tenant'   => $tenant,
            'database' => $database,
        ]));
    }

    public function createCollection(string $tenant, string $database, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections", [
            'tenant'   => $tenant,
            'database' => $database,
        ]), $payload);
    }

    public function getCollection(string $tenant, string $database, string $collectionId): array
    {
        return $this->getJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]));
    }

    public function updateCollection(string $tenant, string $database, string $collectionId, array $payload): void
    {
        $this->putJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function deleteCollection(string $tenant, string $database, string $collectionName): void
    {
        $this->delete($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionName,
        ]));
    }

    public function addToCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/add", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function countCollection(string $tenant, string $database, string $collectionId): int
    {
        return $this->getJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/count", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]));
    }

    public function getFromCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/get", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function deleteFromCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/delete", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    /**
     *
     *
     * @param array $payload might look like:
     * * [
     * *     "query_embeddings" => [$this->createEmbedding($documents)],
     * *     "n_results"        => 10,
     * *     "where"            => [
     * *         "\$and" => [
     * *             ["entity_id" => $bankTransaction->entity_id],
     * *             ["currency" => $bankTransaction->currency_code],
     * *         ]
     * *     ]
     * * ]
     *
     * @see https://cookbook.chromadb.dev/core/filters/ for "where" syntax
     */
    public function queryCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/query", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function updateInCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/update", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function upsertInCollection(string $tenant, string $database, string $collectionId, array $payload): array
    {
        return $this->postJson($this->uri("tenants/{tenant}/databases/{database}/collections/{collection}/upsert", [
            'tenant'     => $tenant,
            'database'   => $database,
            'collection' => $collectionId,
        ]), $payload);
    }

    public function countCollections(string $tenant, string $database): int
    {
        $response = $this->getJson($this->uri("tenants/{tenant}/databases/{database}/collections_count", [
            'tenant'   => $tenant,
            'database' => $database,
        ]));
        return (int)($response['count'] ?? 0);
    }
}
