<?php

require_once('autoload.php');

use David\Liqour\Api;
use David\Http\Client;

$client = new Client();
$api = new Api($client);

$product = $api->getProduct(1);

print_r($product);

$price = $api->getPrice(1);

print_r($price);

$store = $api->getStore(1);

print_r($store);

$products = $api->getProducts();

print_r($products);

$prices = $api->getPrices();

print_r($prices);

$stores = $api->getStores();

print_r($stores);
