<?php

declare(strict_types=1);

namespace David\Http;

use David\Bag\Bag;

class Response
{
    private $headers;
    private $uri;
    private $contentBody;
    private $statusCode;
    private $protocolVersion;
    private $charset;
    private $contentType;

    public function __construct(string $uri, $contentBody, int $statusCode, float $protocolVersion, string $charset, string $contentType, array $headers)
    {
        $this->uri = $uri;
        $this->contentBody = $contentBody;
        $this->statusCode = $statusCode;
        $this->protocolVersion = $protocolVersion;
        $this->charset = $charset;
        $this->contentType = $contentType;
        $this->headers = new Bag($headers);
    }

    public function setUri(string $uri) : Response
    {
        $this->uri = $uri;
        return $this;
    }

    public function setContentBody($contentBody) : Response
    {
        $this->contentBody = $contentBody;
        return $this;
    }

    public function setStatusCode(int $statusCode) : Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setProtocolVersion(float $protocolVersion) : Response
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    public function setCharset(string $charset) : Response
    {
        $this->charset = $charset;
        return $this;
    }

    public function setContentType(string $contentType) : Response
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function setHeaders(array $headers) : Response
    {
        $this->headers = new Bag($headers);
        return $this;
    }

    public function getContentBody()
    {
        return $this->contentBody;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getUri() : string
    {
        return $this->requestedUri;
    }

    public function getHeaders() : Bag
    {
        return $this->headers;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function getCharset() : string
    {
        return $this->charset;
    }

    public function isJson() : bool
    {
        return $this->contentType === 'application/json';
    }
}