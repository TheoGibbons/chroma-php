<?php

namespace TheoGibbons\ChromaPHP\Tests;

use PHPUnit\Framework\TestCase;
use TheoGibbons\ChromaPHP\ChromaDB;
use TheoGibbons\ChromaPHP\ChromaDBClient;

class ChromaDBIntegrationTest extends TestCase
{
    private ChromaDB $chroma;
    private string $tenant = 'default_tenant';
    private string $database = 'default_database';
    private string $collectionName;

    protected function setUp(): void
    {
        $this->chroma = ChromaDBClient::connect([
            'host' => 'http://localhost',
            'port' => 8000,
        ]);

        $this->collectionName = 'test_collection_' . uniqid();
    }

    public function testFullLifecycle()
    {
        // Heartbeat
        $heartbeat = $this->chroma->heartbeat();
        $this->assertIsArray($heartbeat);

        // Version
        $version = $this->chroma->version();
        $this->assertIsString($version);

        // Identity
        $identity = $this->chroma->identity();
        $this->assertIsArray($identity);
        $this->assertArrayHasKey('tenant', $identity);

        // Create Collection
        $created = $this->chroma->createCollection($this->tenant, $this->database, [
            'name'     => $this->collectionName,
            'metadata' => ['test' => 'yes'],
        ]);
        $this->assertArrayHasKey('id', $created);
        $collectionId = $created['id'];

        // List Collections
        $collections = $this->chroma->listCollections($this->tenant, $this->database);
        $names = array_column($collections, 'name');
        $this->assertContains($this->collectionName, $names);

        // Add data
        $addResult = $this->chroma->addToCollection($this->tenant, $this->database, $collectionId, [
            'ids'        => ['doc1'],
            'embeddings' => [[1.0, 2.0, 3.0, 4.0]],
            'documents'  => ['Hello World'],
        ]);
        $this->assertIsArray($addResult);

        // Query
        $queryResult = $this->chroma->queryCollection($this->tenant, $this->database, $collectionId, [
            'query_embeddings' => [[1.0, 2.0, 3.0, 4.0]],
            'n_results'        => 1,
        ]);
        $this->assertIsArray($queryResult);
        $this->assertArrayHasKey('ids', $queryResult);

        // Verify collection exists
        $collections = $this->chroma->listCollections($this->tenant, $this->database);
        $found = !empty(array_filter($collections, fn($c) => $c['id'] === $collectionId));
        $this->assertTrue($found, 'Collection was not found before delete.');

        // Delete
        $this->chroma->deleteCollection($this->tenant, $this->database, $this->collectionName);

        // Confirm deletion
        $collections = $this->chroma->listCollections($this->tenant, $this->database);
        $names = array_column($collections, 'name');
        $this->assertNotContains($this->collectionName, $names);
    }

    protected function tearDown(): void
    {
        try {
            $this->chroma->deleteCollection($this->tenant, $this->database, $this->collectionName);
        } catch (\Exception $e) {
            // Safe ignore if already deleted
        }
    }

}
