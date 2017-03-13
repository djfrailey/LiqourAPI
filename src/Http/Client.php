<?php

namespace David\Http;

use \RuntimeException;

class Client
{
    const HTTP_1_0 = 1.0;
    const HTTP_1_1 = 1.1;

    private $followLocation = 1;
    private $userAgent = "PHP/HTTP-Client";
    private $proxyUri = "";
    private $requestFullUri = false;
    private $maxRedirects = 20;
    private $protocolVersion = self::HTTP_1_1;
    private $timeout = 30.0;
    private $ignoreErrors = false;

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

    public function setProtocolVersion(float $version) : Request
    {
        $this->protocolVersion = $version;
        return $this;
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

    public function getProtocolVersion() : float
    {
        return $this->protocolVersion;
    }

    public function get(string $endpoint, array $params = []) : Response
    {
        $request = new Request();
        $request->setUrl($endpoint)
        ->setQueryParams($params);

        return $this->send($request);
    }

    public function send(Request $request) : Response
    {
        $url = $request->getUrlWithQuery();
        $context = $this->createStreamContext($request);
        $handle = @fopen($url, 'r', false, $context);

        if ($handle === false) {
            throw new RuntimeException("Something bad happened while trying to request $url");
        }

        $meta = stream_get_meta_data($handle);
        $contents = stream_get_contents($handle);
        
        @fclose($handle);

        if ($contents === false) {
            throw new RuntimeException("Something bad happened while reading request stream ($url)");
        }

        return $this->createResponse($url, $meta, $contents);
    }

    private function createStreamContext(Request $request)
    {
        $formattedHeaders = $this->formatRequestHeaders($request);

        $http = [
            'method' => $request->getMethod(),
            'header' => $formattedHeaders,
            'user_agent' => $this->userAgent,
            'content' => $request->getContentBody(),
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

    private function formatRequestHeaders(Request $request) : array
    {
        $formattedHeaders = [];
        $headers = $request->getHeaders()->toGenerator();

        foreach($headers as $header => $value) {
            if (is_numeric($header)) {
                $formattedHeaders[] = $value;
            } else {
                $formattedHeaders[$header] = $value;
            }
        }

        return $formattedHeaders;
    }

    private function createResponse(string $url, array $meta, string $contentBody) : Response
    {
        $parsedResponseMeta = $this->parseResponseMeta($meta);
        $parsedContentBody = $this->parseContentBody($contentBody, $parsedResponseMeta['contentType']);

        $response = new Response(
            $url,
            $parsedContentBody,
            $parsedResponseMeta['code'],
            $parsedResponseMeta['protocol'],
            $parsedResponseMeta['charset'],
            $parsedResponseMeta['contentType'],
            $parsedResponseMeta['headers']
        );

        return $response;
    }

    private function parseResponseMeta(array $responseMeta)
    {
        $parsedResponseMeta = [
            'headers' => [],
            'protocol' => 0,
            'code' => 0,
            'contentType' => "",
            'charset' => ""
        ];

        if (isset($responseMeta['wrapper_data']) === true) {
            $wrapperData = $responseMeta['wrapper_data'];
            $protocolAndCode = array_shift($wrapperData);
            
            $headers = $this->parseHeaders($wrapperData);
            $rawContentType = "";

            if (isset($headers['Content-Type']) === true) {
                $rawContentType = $headers['Content-Type'];
            }

            list($protocol, $code) = $this->parseProtocolAndCode($protocolAndCode);
            list($contentType, $charset) = $this->parseContentType($rawContentType);
            
            $parsedResponseMeta['headers'] = $headers;
            $parsedResponseMeta['protocol'] = $protocol;
            $parsedResponseMeta['code'] = $code;
            $parsedResponseMeta['contentType'] = $contentType;
            $parsedResponseMeta['charset'] = $charset;
        }

        return $parsedResponseMeta;
    }

    private function parseContentBody(string $contentBody, string $contentType)
    {
        if ($contentType === 'application/json') {
            $contentBody = json_decode($contentBody);

            if ($contentBody === false) {
                $jsonError = json_last_error_msg();
                throw new RuntimeException("JSON Parse Error: $jsonError");
            }
        }

        return $contentBody;
    }

    private function parseProtocolAndCode(string $protocolAndCode) : array
    {
        $protocol = 0;
        $statusCode = 0;

        $protocolAndCode = preg_quote($protocolAndCode, '/');
        preg_match('/^HTTP\/(\d\.\d)\ ([\d]{1,3})/', $protocolAndCode, $protocolAndCodeMatch);

        if ($protocolAndCodeMatch) {
            list(, $protocol, $statusCode) = $protocolAndCodeMatch;
            $protocol = floatval($protocol);
            $statusCode = intval($statusCode);
        }

        return [$protocol, $statusCode];
    }

    private function parseHeaders(array $headers) : array
    {
        $parsedHeaders = [];

        foreach($headers as $rawHeader) {
            $firstColon = strpos($rawHeader, ':');
            $header = substr($rawHeader, 0, $firstColon);
            $value = substr($rawHeader, $firstColon + 1);

            $header = trim($header);
            $value = trim($value);
            
            $parsedHeaders[$header] = $value;
        }

        return $parsedHeaders;
    }

    private function parseContentType(string $rawContentType) : array
    {
        $contentType = "";
        $encoding = "";

        $contentTypeSplit = explode(' ', $rawContentType);

        if (isset($contentTypeSplit[0]) === true) {
            $contentType = $contentTypeSplit[0];
            $contentType = rtrim($contentType, ';');
        }

        if (isset($contentTypeSplit[1]) === true) {
            $charsetString = $contentTypeSplit[1];
            list(,$charset) = explode('=', $charsetString);
        }

        return [$contentType, $encoding];
    }
}