<?php

namespace David\Http;

use \RuntimeException;

/**
 * A naive HTTP Client.
 */
class Client
{
    const HTTP_1_0 = 1.0;
    const HTTP_1_1 = 1.1;

    /**
     * Follow Location headers.
     * @var integer
     */
    private $followLocation = 0;

    /**
     * User Agent string to send with the request.
     * @var string
     */
    private $userAgent = "PHP/HTTP-Client";

    /**
     * Address of a proxy to make the request through.
     * @var string
     */
    private $proxyUri = "";

    /**
     * Indicates that the entire uri should be used when
     * sending the request. Some proxies may require it.
     * @var boolean
     */
    private $requestFullUri = false;

    /**
     * The number of redirects to follow.
     * @var integer
     */
    private $maxRedirects = 20;

    /**
     * The HTTP Version to send with a request.
     * @var float
     */
    private $protocolVersion = self::HTTP_1_1;

    /**
     * The amount of time in seconds to wait for a response to the request.
     * @var float
     */
    private $timeout = 30.0;

    /**
     * Ignores internal errors thrown by PHP when making requests.
     * @var boolean
     */
    private $ignoreErrors = false;

    public function setProxy(string $proxyUri) : Client
    {
        $this->proxyUri = $proxyUri;
        return $this;
    }

    public function getProxy() : string
    {
        return $this->proxyUri;
    }

    public function setUserAgent(string $userAgent) : Client
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getUserAgent() : string
    {
        return $this->userAgent;
    }

    public function setFollowLocation(bool $follow = false) : Client
    {
        $this->followLocation = (int) $follow;
        return $this;
    }

    public function getFollowLocation() : bool
    {
        return $this->followLocation;
    }

    public function setProtocolVersion(float $version) : Client
    {
        $this->protocolVersion = $version;
        return $this;
    }

    public function setMaxRedirects(int $max) : Client
    {
        $this->maxRedirects = $max;
        return $this;
    }

    public function getMaxRedirects() : int
    {
        return $this->maxRedirects;
    }

    public function setRequestFullUri(bool $requestFullUri) : Client
    {
        $this->requestFullUri = $requestFullUri;
        return $this;
    }

    public function getRequestFullUri() : bool
    {
        return $this->requestFullUri;
    }

    public function setIgnoreErrors(bool $ignore) : Client
    {
        $this->ignoreErrors = $ignore;
        return $this;
    }

    public function getIgnoreErrors() : bool
    {
        return $this->ignoreErrors;
    }

    public function setTimeout(float $timeout) : Client
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

    /**
     * Convenience method to create and send a GET Request
     * @param  string $endpoint URL to request.
     * @param  array  $params   Parameters to pass as GET params
     * @return Response
     */
    public function get(string $endpoint, array $params = []) : Response
    {
        $request = new Request();
        $request->setUrl($endpoint)
        ->setQueryParams($params);

        return $this->send($request);
    }

    /**
     * Sends an HTTP request through the client.
     * @param  Request $request
     * @throws  RutimeException if the requested URL cannot be opened.
     * @throws  RuntimeException if the open stream cannot be flushed.
     * @return Response
     */
    public function send(Request $request) : Response
    {
        $url = $request->getUrlWithQuery();
        $context = $this->createStreamContext($request);
        
        try {
        
            $handle = @fopen($url, 'r', false, $context);

            $meta = [];
            $contents = "";

            if ($handle) {
                $meta = stream_get_meta_data($handle);
                $contents = stream_get_contents($handle);
                
                @fclose($handle);
            }

            return $this->createResponseFromData($url, $meta, $contents);
        
        } catch (RuntimeException $ex) {
            
            return new Response(
                $url,
                $ex->getMessage(),
                $ex->getCode(),
                $this->getProtocolVersion(),
                "",
                "",
                []
            );
        
        }
    }

    public function onStreamNotification(
        $notificationCode,
        $severity,
        $message,
        $messageCode,
        $bytesTransferred,
        $bytesMax
    )
    {
        if ($notificationCode === STREAM_NOTIFY_FAILURE) {
            throw new RuntimeException($message, $messageCode);
        }
    }

    /**
     * Generates a stream context to be used with the next request.
     * @param  Request $request
     * @return resource
     */
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

        $notification = [$this, 'onStreamNotification'];

        $options = compact('http');
        $params = compact('notification');

        return stream_context_create($options, $params);
    }

    /**
     * Formats the request headers to be sent by the client.
     * @param  Request $request
     * @return array
     */
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

    /**
     * Creates a response from a given request.
     * @param  string $url
     * @param  array  $meta
     * @param  string $contentBody
     * @return Response
     */
    private function createResponseFromData(string $url, array $meta, string $contentBody) : Response
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

    /**
     * Parses the request streams meta data into useable data.
     * @param  array  $responseMeta
     * @return array
     */
    private function parseResponseMeta(array $responseMeta) : array
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

    /**
     * JSON Decodes the content body if the content type is application/json
     * 
     * @param  string $contentBody
     * @param  string $contentType
     * @return mixed
     */
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

    /**
     * Parses the HTTP Protocol and Status Code of a raw response.
     *
     * The first index of the tuple will be the parsed protocol or 0.
     * The second index of the tuple will be the parsed status code or 0.
     * 
     * @param  string $protocolAndCode 
     * @return array
     */
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

    /**
     * Parses an array of raw headers into something more friendly.
     * @param  array  $headers
     * @return array
     */
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

    /**
     * Parses the content type and character set from a raw content type header.
     *
     * The first index in the tuple will be the parsed content type or empty string.
     * The second index in the tuple will be the parsed character set or empty string.
     * 
     * @param  string $rawContentType
     * @return array
     */
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