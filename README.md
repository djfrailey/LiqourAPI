# Oregon Liquor Prices API PHP Wrapper

This library provides programmatic access to the Oregon Liquor Prices API.

### Requirements

- PHP > 7.0

### Usage

```php
use David\Http\Client;
use David\Liqour\Api;

$client = new Client();
$api = new Api($client);

$prices = $api->getPrices();
```

To get a listing of products you may
```php
$product = $api->getProducts();
```

To get the next page in any listing you may pass the offset parameter.

Ex.

```php
$products = $api->getProducts(['offset' => 20]);
```

You may also pass a length parameter to change the number of records returned.

Ex.

```php
$products = $api->getProducts(['length' => 40]);
```

To get a single product you may:
```php
$product = $api->getProduct($productId);
```

To get prices for a specific product you may:
```php
$prices = $api->getProductPrices($productId);
```

To get a listing of all stores you may:
```php
$stores = $api->getStores();
```

To get a single store you may:
```php
$store = $api->getStore($storeId);
```

To get a listing of product prices you may:
```php
$prices = $api->getPrices();
```

To get a single price you may:
```php
$price = $api->getPrice($priceId);
```