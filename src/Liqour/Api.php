<?php

namespace David\Liqour;

use David\Http\Client;
use David\Http\Response;
use David\Bag\Bag;

use \stdClass;
use \DateTime;
use \InvalidArgumentException;

/**
 * Class representation of the Oregon Liquor Prices API.
 *
 * http://www.oregonliquorprices.com
 */
class Api
{

    /**
     * The base endpoint for the API.
     * @var string
     */
    private $endpoint = "http://www.oregonliquorprices.com/api/v1";

    /**
     * Factory to create resources from responses.
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * The HTTP client to use for API requests.
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->resourceFactory = new ResourceFactory();

        $this->client->setFollowLocation(1);
    }

    /**
     * Get a bag full of prices.
     *
     * Valid parameters include:
     *     length - Number of results to return in a request
     *     offset - Number of records to skip
     *     product - ID of the product to retrieve prices for
     * 
     * @param  array  $params Get parameters to pass with the request.
     * @return Bag
     */
    public function getPrices(array $params = []) : Bag
    {
        $response = $this->request("price", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createPriceBag($contentBody->objects);
    }

    /**
     * Get a price by ID.
     * 
     * @param  int    $priceId
     * @return Price
     */
    public function getPrice(int $priceId) : Price
    {
        $response = $this->request("price/$priceId");
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createPrice($contentBody);
    }

    /**
     * Get a bag full of products.
     *
     * Valid parameters include:
     *
     *     length - Number of results to return in a request
     *     offset - Number of records to skip
     *     size - Filters products by bottle size.
     *     proof - Filters products by proof.
     *     on_sale - Filters products by sale status.
     *     
     * @param  array  $params
     * @return Bag
     */
    public function getProducts(array $params = []) : Bag
    {
        $response = $this->request("product", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createPriceBag($contentBody);
    }

    /**
     * Get product by ID.
     * @param  int    $productId 
     * @return Product
     */
    public function getProduct(int $productId) : Product
    {
        $response = $this->request("product/$productId");
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createProduct($contentBody);
    }

    public function getProductPrices($productOrId) : Bag
    {
        $isInteger = is_integer($productId);
        $isProduct = $productOrId instanceof Product;

        if ($isInteger === false && $isProduct === false) {
            throw new InvalidArgumentException("Argument must be an integer or an instance of a Product");
        }

        $id = $productOrId;

        if ($isProduct === true) {
            $id = $productOrId->getId();
        }

        return $this->api->getPrices(['product' => $id]);
    }

    /**
     * Get a bag of Stores.
     *
     * Valid parameters include:
     *     length - Number of results to return in a request
     *     offset - Number of records to skip    
     * 
     * @param  array  $params
     * @return Bag
     */
    public function getStores(array $params = []) : Bag
    {
        $response = $this->request("store", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createStoreBag($contentBody->objects);
    }

    /**
     * Get a store by ID
     * 
     * @param  int    $storeId
     * @param  array  $params 
     * @return Store
     */
    public function getStore(int $storeId) : Store
    {
        $response = $this->request("store/$storeId", $params);
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createStore($contentBody);
    }

    /**
     * Internal method used to pipe requests to the http client.
     * 
     * @param  string $endpoint
     * @param  array  $params
     * @return Response
     */
    private function request(string $endpoint, array $params = []) : Response
    {
        $defaults = [
            'limit' => 20
        ];

        $params['format'] = 'json';
        $params = array_merge($defaults, $params);

        $endpoint = "$this->endpoint/$endpoint";
        
        return $this->client->get($endpoint, $params);
    }
}