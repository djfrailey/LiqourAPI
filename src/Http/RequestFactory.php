<?php

declare(strict_types=1);

namespace David\Http;

class RequestFactory
{
    public function get(string $uri, array $params = []) : Request
    {
        $queryString = "";

        if ($params) {
            $queryParamString = http_build_query($params);
            $queryString = "?$queryParamString";
        }

        $request = new Request($uri);
        return $request;
    }

    public function post(string $uri, $body) : Request
    {
        $request = new Request($uri);
        
        $request->setMethod(Request::METHOD_POST);

        if (is_array($body)) {
            $body = http_build_query($body);
            $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $request->setHeader('Content-Length', strlen($body));
        }

        $request->setContent($body);

        return $request;
    }

    public function put(string $uri, $body) : Request
    {
        $request = $this->post($uri, $body);
        $request->setMethod(Request::METHOD_PUT);
        return $request;
    }

    public function delete(string $uri) : Request
    {
        $request = new Request($uri);
        $request->setMethod(Request::METHOD_DELETE);
        return $request;
    }
}