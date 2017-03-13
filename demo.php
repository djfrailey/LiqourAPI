<?php

require_once('autoload.php');

use David\Liqour\Api;
use David\Http\Client;

$client = new Client();
$api = new Api($client);

$product = $api->getProduct(5111);
print_r($product);
$prices = $product->getPrices();
print_r($prices);