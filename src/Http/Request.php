<?php

declare(strict_types=1);

namespace David\Http;

use David\Bag\Bag;
use \RuntimeException;

class Request
{
    const HTTP_1_0 = 1.0;
    const HTTP_1_1 = 1.1;
    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";
    const METHOD_POST = "POST";
    const METHOD_DELETE = "DELETE";

    private $url;
    private $headers;
    private $followLocation = 1;
    private $method = self::METHOD_GET;
    private $userAgent = "PHP/HTTP-Client";
    private $contentBody = "";
    private $proxyUri = "";
    private $requestFullUri = false;
    private $maxRedirects = 20;
    private $protocolVersion = self::HTTP_1_1;
    private $timeout = 30.0;
    private $ignoreErrors = false;

    public function __construct(string $url = null)
    {
        $this->setUrl($url);
        $this->headers = new Bag();
    }

    public function send() : Response
    {
        $context = $this->createStreamContext();
        $handle = fopen($this->url, 'r', false, $context);

        if ($handle === false) {
            throw new RuntimeException("Something bad happened while trying to request $this->url");
        }

        $meta = stream_get_meta_data($handle);
        $contents = stream_get_contents($handle);

        if ($contents === false) {
            throw new RuntimeException("Something bad happened while reading request stream ($this->url)");
        }

        return new Response($this->url, $contents, $meta);
    }

    public function setProtocolVersion(float $version) : Request
    {
        $this->protocolVersion = $version;
        return $this;
    }

    public function getProtocolVersion() : float
    {
        return $this->protocolVersion;
    }

    public function setMaxRedirects(int $max) : Request
    {
        $this->maxRedirects = $max;
        return $this;
    }

    public function getMaxRedirects() : int
    {
        return $this->maxRedirects;
    }

    public function setRequestFullUri(bool $requestFullUri) : Request
    {
        $this->requestFullUri = $requestFullUri;
        return $this;
    }

    public function getRequestFullUri() : bool
    {
        return $this->requestFullUri;
    }

    public function setIgnoreErrors(bool $ignore) : Request
    {
        $this->ignoreErrors = $ignore;
        return $this;
    }

    public function getIgnoreErrors() : bool
    {
        return $this->ignoreErrors;
    }

    public function setTimeout(float $timeout) : Request
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getTimeout() : float
    {
        return $this->timeout;
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

    public function setProxy(string $proxyUri) : Request
    {
        $this->proxyUri = $proxyUri;
        return $this;
    }

    public function getProxy() : string
    {
        return $this->proxyUri;
    }

    public function setUserAgent(string $userAgent) : Request
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getUserAgent() : string
    {
        return $this->userAgent;
    }

    public function setFollowLocation(bool $follow = false) : Request
    {
        $this->followLocation = (int) $follow;
        return $this;
    }

    public function getFollowLocation() : bool
    {
        return $this->followLocation;
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

    private function createStreamContext()
    {
        $formattedHeaders = $this->getFormattedHeaders();

        $http = [
            'method' => $this->method,
            'header' => $formattedHeaders,
            'user_agent' => $this->userAgent,
            'content' => $this->contentBody,
            'proxy' => $this->proxyUri,
            'request_fulluri' => $this->requestFullUri,
            'follow_location' => $this->followLocation,
            'max_redirects' => $this->maxRedirects,
            'protocol_version' => $this->protocolVersion,
            'timeout' => $this->timeout,
            'ignore_errors' => $this->ignoreErrors,
        ];

        $options = compact('http');

        return stream_context_create($options);
    }

    private function getFormattedHeaders()
    {
        $formattedHeaders = [];
        $headers = $this->headers->toGenerator();

        foreach($headers as $header => $value) {
            if (is_numeric($header)) {
                $formattedHeaders[] = $value;
            } else {
                $formattedHeaders[$header] = $value;
            }
        }

        return $formattedHeaders;
    }
}
