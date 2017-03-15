<?php

declare(strict_types=1);

namespace Djfrailey\Http;

use Djfrailey\Bag\Bag;

/**
 * A class to represent an HTTP response.
 */
class Response
{

    /**
     * A map of status codes to messages.
     * @var array
     */
    private $statusMessageMap = [
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",
        208 => "Already Reported",
        226 => "IM Used",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        421 => "Misdirected Request",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        444 => "Connection Closed Without Response",
        451 => "Unavailable For Legal Reasons",
        499 => "Client Closed Request",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        508 => "Loop Detected",
        510 => "Not Extended",
        511 => "Network Authentication Required",
        599 => "Network Connect Timeout Error",
    ];

    /**
     * Response headers
     * @var Bag
     */
    private $headers;

    /**
     * Requested URI that generated the response.
     * @var string
     */
    private $uri;

    /**
     * Content body of the response.
     * @var mixed
     */
    private $contentBody;

    /**
     * Response status code sent by the server.
     * @var int
     */
    private $statusCode;

    /**
     * HTTP Protocol version returned by the server.
     * @var float
     */
    private $protocolVersion;

    /**
     * Response character set.
     * @var string
     */
    private $charset;

    /**
     * Response content type.
     * @var string
     */
    private $contentType;

    /**
     * Object constructor
     * @param string $uri             URI that generated the response.
     * @param string|array $contentBody Raw content body as a string, or a json decoded string.
     * @param int    $statusCode
     * @param float  $protocolVersion
     * @param string $charset
     * @param string $contentType
     * @param array  $headers
     */
    public function __construct(
        string $uri,
        $contentBody,
        int $statusCode,
        float $protocolVersion,
        string $charset,
        string $contentType,
        array $headers
    ) {
    
        $this->uri             = $uri;
        $this->contentBody     = $contentBody;
        $this->statusCode      = $statusCode;
        $this->protocolVersion = $protocolVersion;
        $this->charset         = $charset;
        $this->contentType     = $contentType;
        $this->headers         = new Bag($headers);
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

    public function getContentType() : String
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

    public function isSuccess() : bool
    {
        return $this->statusCode < 400;
    }

    public function getStatusMessage() : string
    {
        $statusMessage = "";

        if (isset($this->statusMessageMap[$this->statusCode])) {
            $statusMessage = $this->statusMessageMap[$this->statusCode];
        }

        return $statusMessage;
    }
}
