<?php

namespace Djfrailey\Liqour;

use Djfrailey\Http\Client;
use Djfrailey\Http\Response;
use Djfrailey\Bag\Bag;

use \stdClass;
use \DateTime;
use \InvalidArgumentException;
use \RuntimeException;

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
        $this->client          = $client;
        $this->resourceFactory =  new ResourceFactory();
    }

    /**
     * Get a bag full of prices.
     *
     * Valid parameters include:
     *     limit - Number of results to return in a request
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

        $objects = $contentBody->objects ?? [];

        return $this->resourceFactory->createPriceBag($objects);
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
     *     limit - Number of results to return in a request
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

        $objects = $contentBody->objects ?? [];

        return $this->resourceFactory->createProductBag($objects);
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
        $isInteger = is_integer($productOrId);
        $isProduct = $productOrId instanceof Product;

        if ($isInteger === false && $isProduct === false) {
            throw new InvalidArgumentException("Argument must be an integer or an instance of a Product");
        }

        $id = $productOrId;

        if ($isProduct === true) {
            $id = $productOrId->getId();
        }

        return $this->getPrices(['product' => $id]);
    }

    /**
     * Get a bag of Stores.
     *
     * Valid parameters include:
     *     limit - Number of results to return in a request
     *     offset - Number of records to skip
     *
     * @param  array  $params
     * @return Bag
     */
    public function getStores(array $params = []) : Bag
    {
        $response = $this->request("store", $params);
        $contentBody = $response->getContentBody();

        $objects = $contentBody->objects ?? [];

        return $this->resourceFactory->createStoreBag($objects);
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
        $response = $this->request("store/$storeId");
        $contentBody = $response->getContentBody();
        return $this->resourceFactory->createStore($contentBody);
    }

    /**
     * Internal method used to pipe requests to the http client.
     *
     * @param  string $endpoint
     * @param  array  $params
     * @throws RuntimeException if the API request was not successful.
     * @return Response
     */
    private function request(string $endpoint, array $params = []) : Response
    {
        $defaults = [
            'limit' => 20
        ];

        $params['format'] = 'json';
        $params = array_merge($defaults, $params);

        $endpoint = rtrim($endpoint, '/');
        $finalEndpoint = "$this->endpoint/$endpoint/";
        
        $response = $this->client->get($finalEndpoint, $params);

        if ($response->isSuccess() === false) {
            $statusCode = $response->getStatusCode();
            $statusMessage = $response->getStatusMessage();
            throw new RuntimeException("$statusCode: $statusMessage");
        }
        
        return $response;
    }
}
