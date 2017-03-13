<?php

namespace David\Liqour;

class ResourceFactory
{
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function createPriceBag(array $contentBody) : Bag
    {
        $prices = new Bag();
        
        foreach($contentBody as $rawPrice) {
            $price = $this->createPrice($rawPrice);
            $prices->set((string) $price->getId(), $price);
        }

        return $prices;
    }

    public function createPrice(stdClass $contentBody) : Price
    {
        $createdAt = new DateTime($contentBody->created_at);
        $modifiedAt = new DateTime($contentBody->modified_at);

        return new Price(
            $contentBody->id,
            $contentBody->amount,
            $createdAt,
            $modifiedAt,
            $contentBody->product,
            $contentBody->resource_uri
        );
    }

    public function createStoreBag(array $contentBody) : Bag
    {
        $stores = new Bag();

        foreach($contentBody as $rawStore)
        {
            $store = $this->createStore($rawStore);
            $stores->set($store->getId(), $store);
        }

        return $stores;
    }

    public function createStore(stdClass $contentBody) : Store
    {
        return new Store(
            $contentBody->id,
            $contentBody->key,
            $contentBody->name,
            $contentBody->address,
            $contentBody->address_raw,
            $contentBody->phone,
            $contentBody->county,
            $contentBody->hours_raw,
            $contentBody->latitude,
            $contentBody->longitude,
            $contentBody->resource_uri
        );
    }

    public function createProductBag(array $contentBody) : Bag
    {
        $products = new Bag();

        foreach($contentBody as $rawProduct) {
            $product = $this->createProduct($rawProduct);
            $products->set($product->getId(), $product);
        }

        return $products;
    }

    public function createProduct(stdClass $contentBody) : Product
    {
        $createdAt = new DateTime($contentBody->created_at);
        $modifiedAt = new DateTime($contentBody->modified_at);

        $product = new Product(
            $contentBody->id,
            $contentBody->title,
            $contentBody->age,
            $contentBody->bottles_per_case,
            $contentBody->description,
            $contentBody->on_sale,
            $contentBody->proof,
            $contentBody->size,
            $contentBody->code,
            $contentBody->slug,
            $createdAt,
            $modifiedAt,
            $contentBody->resource_uri
        );

        $prices = $this->api->getPrices(['product' => $contentBody->id]);
        $product->setPrices($prices);
        
        return $product;
    }
}