<?php

declare(strict_types=1);

namespace David\Http;

use David\Bag\Bag;
use \RuntimeException;

class Request
{
    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";
    const METHOD_POST = "POST";
    const METHOD_DELETE = "DELETE";

    private $url;
    private $headers;
    private $method = self::METHOD_GET;
    private $contentBody = "";

    public function __construct(string $url = null)
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
}
