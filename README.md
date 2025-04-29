# ChromaDB PHP Client

A lightweight PHP client for interacting with ChromaDB v2 APIs.

> Inspired by [CodeWithKyrian/chromadb-php](https://github.com/CodeWithKyrian/chromadb-php), but that project does not support ChromaDB v2.

## Installation

```bash
composer require theogibbons/chroma-php
```

## Example 1: Check if the server is running

```php
use TheoGibbons\ChromaPHP\ChromaDBClient;

$chroma = ChromaDBClient::connect();

$heartbeat = $chroma->heartbeat();
print_r($heartbeat);
```

## Example 2: Basic Usage ([View full example](/examples/basic_usage.php))

```php
<?php

require '../vendor/autoload.php';

use TheoGibbons\ChromaPHP\ChromaDBClient;

$chroma = ChromaDBClient::connect([
    'host' => 'http://localhost',
    'port' => 8000,
]);

$tenantName = 'default_tenant';
$databaseName = 'default_database';
$collectionName = 'test_collection_' . uniqid();

try {
    echo "\n----------------- Testing connection -----------------\n";
    print_r($chroma->heartbeat());

    echo "\n----------------- Testing version -----------------\n";
    echo "ChromaDB Version: " . $chroma->version() . "\n";

    echo "\n----------------- Testing identity -----------------\n";
    print_r($chroma->identity());

    echo "\n----------------- Creating collection: $collectionName -----------------\n";
    $createResponse = $chroma->createCollection($tenantName, $databaseName, [
        'name'     => $collectionName,
        'metadata' => ['test' => 'yes'],
    ]);
    print_r($createResponse);

    echo "\n----------------- Listing collections -----------------\n";
    foreach ($chroma->listCollections($tenantName, $databaseName) as $collection) {
        echo "- {$collection['name']}\n";
    }

    echo "\n----------------- Adding data to collection -----------------\n";
    print_r($chroma->addToCollection($tenantName, $databaseName, $createResponse['id'], [
        'ids'        => ['doc1'],
        'embeddings' => [[1.0, 2.0, 3.0, 4.0]],
        'documents'  => ['Hello World'],
    ]));

    echo "\n----------------- Querying collection -----------------\n";
    print_r($chroma->queryCollection($tenantName, $databaseName, $createResponse['id'], [
        'query_embeddings' => [[1.0, 2.0, 3.0, 4.0]],
        'n_results'        => 1,
    ]));

    echo "\n----------------- Checking collection exists -----------------\n";
    $found = count(array_filter(
        $chroma->listCollections($tenantName, $databaseName),
        fn($c) => $c['id'] === $createResponse['id']
    )) > 0;
    echo $found ? "Collection found.\n" : "Collection not found.\n";

    echo "\n----------------- Deleting collection -----------------\n";
    $chroma->deleteCollection($tenantName, $databaseName, $collectionName);
    echo "Collection deleted.\n";

    echo "\n✅ Test completed successfully.\n";
} catch (Exception $e) {
    echo "\n❌ Test failed: {$e->getMessage()}\n";
    echo $e->getTraceAsString();
}

die("Done\n");
```

## Running ChromaDB

You’ll need to have ChromaDB running in client/server mode.

The easiest way is to run it in Docker:

```bash
docker run -p 8000:8000 chromadb/chroma
```

Or, use Docker Compose:

```yaml
services:
  chroma:
    image: chromadb/chroma
    ports:
      - "8000:8000"
    volumes:
      - chroma-data:/chroma/chroma

volumes:
  chroma-data:
    driver: local
```

Start it with:

```bash
docker-compose up -d
```

Then visit the Swagger docs link in your browser to check it's running correctly `http://localhost:8000/docs/`

Now you can interact with ChromaDB here `http://localhost:8000`

More info: [Chroma Documentation](https://docs.trychroma.com/deployment)

## Features

- ✅ Full support for Tenants, Databases, and Collections
- 📄 Add, Query, and Upsert documents
- ⚡ Lightweight (only dependency is Guzzle)

## Requirements

- PHP 8.1 or higher
- ChromaDB v2 running in server mode

## License

MIT