<?php

declare(strict_types=1);

namespace David\Http;

use David\Bag\Bag;

class Response
{
    private $headers;
    private $requestedUri;
    private $contentBody;
    private $statusCode;
    private $protocolVersion;
    private $charset;
    private $contentType;

    public function __construct(string $requestedUri, string $contentBody, array $responseMeta)
    {
        $this->headers = new Bag();
        $this->requestedUri = $requestedUri;
        $this->parseResponseMeta($responseMeta);
        $this->parseContentBody($contentBody);
    }

    public function getContentBody()
    {
        return $this->contentBody;
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

    private function parseResponseMeta(array $responseMeta)
    {
        if (isset($responseMeta['wrapper_data']) === true) {
            $wrapperData = $responseMeta['wrapper_data'];
            
            $protocolAndCode = array_shift($wrapperData);
            $this->parseProtocolAndCode($protocolAndCode);
            $this->parseHeaders($wrapperData);
            $this->parseContentType();
        }
    }

    private function parseContentBody(string $contentBody)
    {
        if ($this->isJson()) {
            $contentBody = json_decode($contentBody);

            if ($contentBody === false) {
                $jsonError = json_last_error_msg();
                throw new RuntimeException("JSON Parse Error: $jsonError");
            }
        }

        $this->contentBody = $contentBody;
    }

    private function parseProtocolAndCode(string $protocolAndCode)
    {
        $protoclAndCode = preg_quote($protocolAndCode, '/');
        preg_match('/^HTTP\/(\d\.\d)\ ([\d]{1,3})/', $protocolAndCode, $protocolAndCodeMatch);

        if ($protocolAndCodeMatch) {
            list(, $protocol, $statusCode) = $protocolAndCodeMatch;
            $this->protocolVersion = floatval($protocol);
            $this->statusCode = intval($statusCode);
        }
    }

    private function parseHeaders(array $headers)
    {
        foreach($headers as $rawHeader) {
            $firstColon = strpos($rawHeader, ':');
            $header = substr($rawHeader, 0, $firstColon);
            $value = substr($rawHeader, $firstColon + 1);

            $header = trim($header);
            $value = trim($value);
            
            $this->headers->set($header, $value);
        }
    }

    private function parseContentType()
    {
        $contentType = $this->headers->get('Content-Type');

        if ($contentType) {
            list($contentType, $charsetString) = explode(' ', $contentType);
            list(,$charset) = explode('=', $charsetString);

            $contentType = rtrim($contentType, ';');

            $this->contentType = $contentType;
            $this->charset = $charset;
        }
    }
}