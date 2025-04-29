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