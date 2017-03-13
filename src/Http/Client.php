<?php

namespace David\Http;

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

    public function send(Request $request) : Response
    {
        $context = $this->createStreamContext($request);
        $handle = @fopen($this->url, 'r', false, $context);

        if ($handle === false) {
            throw new RuntimeException("Something bad happened while trying to request $this->url");
        }

        $meta = stream_get_meta_data($handle);
        $contents = stream_get_contents($handle);

        if ($contents === false) {
            throw new RuntimeException("Something bad happened while reading request stream ($this->url)");
        }

        return $this->createResponse($meta, $contents);
    }

    private function createStreamContext(Request $request)
    {
        $formattedHeaders = $this->getFormattedHeaders($request);

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

    private function getFormattedHeaders(Request $request) : array
    {
        $formattedHeaders = [];
        $headers = $request->headers->toGenerator();

        foreach($headers as $header => $value) {
            if (is_numeric($header)) {
                $formattedHeaders[] = $value;
            } else {
                $formattedHeaders[$header] = $value;
            }
        }

        return $formattedHeaders;
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
            $contentTypeSplit = explode(' ', $contentType);

            if (isset($contentTypeSplit[0]) === true) {
                $contentType = $contentTypeSplit[0];
                $contentType = rtrim($contentType, ';');
                $this->contentType = $contentType;
            }

            if (isset($contentTypeSplit[1]) === true) {
                $charsetString = $contentTypeSplit[1];
                list(,$charset) = explode('=', $charsetString);
                $this->charset = $charset;
            }
        }
    }

    private function createResponse(array $meta, string $data) : Response
    {

    }
}