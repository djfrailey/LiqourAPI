<?php

declare(strict_types=1);

namespace David\Http;

use David\Bag\Bag;
use \RuntimeException;

/**
 * A class to represent an HTTP Request
 */
class Request
{
    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";
    const METHOD_POST = "POST";
    const METHOD_DELETE = "DELETE";

    /**
     * Request URL
     * @var string
     */
    private $url;

    /**
     * Request Headers
     * @var Bag
     */
    private $headers;

    /**
     * Request method. One of METHO_* constants.
     * @var string
     */
    private $method = self::METHOD_GET;

    /**
     * Content body to be sent with POST and PUT requests.
     * @var string
     */
    private $contentBody = "";

    /**
     * Query parameters to be sent with GET requests.
     * @var array
     */
    private $params = [];

    public function __construct(string $url = "")
    {
        $this->setUrl($url);
        $this->headers = new Bag();
    }

    public function setUrl(string $url) : Request
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getUrlWithQuery() : string
    {
        $query = http_build_query($this->params);
        $url = $this->url;

        if ($query) {
            $url .= "?$query";
        }

        return $url;
    }

    public function setContentBody(string $content) : Request
    {
        $this->contentBody = $content;
        return $this;
    }

    public function getContentBody() : string
    {
        return $this->contentBody;
    }

    public function setMethod(string $method) : Request
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function setHeaders(array $headers) : Request
    {
        $this->headers->fill($headers);
        return $this;
    }

    public function setHeader(string $header, string $value) : Request
    {
        $this->headers->set($header, $value);
        return $this;
    }

    public function getHeaders() : Bag
    {
        return $this->headers;
    }

    public function getHeader(string $header)
    {
        return $this->headers->get($header);
    }

    public function getQueryParams() : array
    {
        return $this->params;
    }

    public function setQueryParams(array $params) : Request
    {
        $this->params = $params;
        return $this;
    }
}
