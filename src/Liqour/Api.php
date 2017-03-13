<?php

namespace David\Liqour;

use David\Http\Client;
use David\Http\Response;
use David\Bag\Bag;

use \stdClass;
use \DateTime;

class Api
{
    private $endpoint = "http://www.oregonliquorprices.com/api/v1";
    private $resourceFactory;
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->resourceFactory = new ResourceFactory($this);
    }

    public function getPrices(array $params = []) : Bag
    {
        $response = $this->request("price", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createPriceBag($contentBody->objects);
    }

    public function getPrice(int $priceId, array $params = []) : Price
    {
        $response = $this->request("price/$priceId", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createPrice($contentBody);
    }

    public function getProducts(array $params = []) : Bag
    {
        return $this->request("product", $params);
    }

    public function getProduct(int $productId, array $params = []) : Product
    {
        $response = $this->request("product/$productId", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createProduct($contentBody);
    }

    public function getStores(array $params = []) : Bag
    {
        $response = $this->request("store", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createStoreBag($contentBody->objects);
    }

    public function getStore(int $storeId, array $params = []) : Store
    {
        $response = $this->request("store/$storeId", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createStore($contentBody);
    }

    private function request(string $endpoint, array $params = []) : Response
    {
        $defaults = [
            'format' => 'json',
            'limit' => 20
        ];

        $params = array_merge($defaults, $params);

        $endpoint = "$this->endpoint/$endpoint";
        
        return $this->client->get($endpoint, $params);
    }
}