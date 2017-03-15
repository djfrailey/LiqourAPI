<?php

namespace David\Liqour;

use David\Bag\Bag;

use \stdClass;
use \DateTime;

/**
 * Factory to turn API responses into their object representations.
 */
class ResourceFactory
{
    public function createPriceBag(array $prices) : Bag
    {
        $priceBag = new Bag();
        
        foreach($prices as $rawPrice) {
            $price = $this->createPrice($rawPrice);
            $priceBag->set((string) $price->getId(), $price);
        }

        return $priceBag;
    }

    public function createPrice(stdClass $price) : Price
    {
        $createdAt = new DateTime($price->created_at);
        $modifiedAt = new DateTime($price->modified_at);

        return new Price(
            $price->id,
            $price->amount,
            $createdAt,
            $modifiedAt,
            $price->product,
            $price->resource_uri
        );
    }

    public function createStoreBag(array $stores) : Bag
    {
        $storeBag = new Bag();

        foreach($stores as $rawStore)
        {
            $store = $this->createStore($rawStore);
            $storeBag->set($store->getId(), $store);
        }

        return $storeBag;
    }

    public function createStore(stdClass $store) : Store
    {
        return new Store(
            $store->id,
            $store->key,
            $store->name,
            $store->address,
            $store->address_raw,
            $store->phone,
            $store->county,
            $store->hours_raw,
            $store->latitude,
            $store->longitude,
            $store->resource_uri
        );
    }

    public function createProductBag(array $products) : Bag
    {
        $productBag = new Bag();

        foreach($products as $rawProduct) {
            $product = $this->createProduct($rawProduct);
            $productBag->set($product->getId(), $product);
        }

        return $productBag;
    }

    public function createProduct(stdClass $product) : Product
    {
        $createdAt = new DateTime($product->created_at);
        $modifiedAt = new DateTime($product->modified_at);

        $productDTO = new Product(
            $product->id,
            $product->title,
            $product->age,
            $product->bottles_per_case,
            $product->description,
            $product->on_sale,
            $product->proof,
            $product->size,
            $product->code,
            $product->slug,
            $createdAt,
            $modifiedAt,
            $product->resource_uri
        );
        
        return $productDTO;
    }
}