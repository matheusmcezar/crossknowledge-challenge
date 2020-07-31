<?php

class SimpleJsonRequest
{
    private static function makeRequest(string $method, string $url, array $parameters = null, array $data = null)
    {
        // instance of request
        $request = new Request($method, $url, $parameters, $data);

        $redis = new Redis();
        $redis->connect("127.0.0.1");

        // if there is a data int the hash position (redis)
        if ($data = $redis->get($request->getHash())) {
            $redis->close();
            return $data;
        }
        // else do it for the first time
        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null
            ]
        ];

        $url .= ($parameters ? '?' . http_build_query($parameters) : '');

        $data = file_get_contents($url, false, stream_context_create($opts));
        // store the data and return
        $redis->set($request->getHash(), $data);
        return $data;
    }

    public static function get(string $url, array $parameters = null)
    {
        return json_decode(self::makeRequest('GET', $url, $parameters));
    }

    public static function post(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('POST', $url, $parameters, $data));
    }

    public static function put(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PUT', $url, $parameters, $data));
    }   

    public static function patch(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PATCH', $url, $parameters, $data));
    }

    public static function delete(string $url, array $parameters = null, array $data = null)
    {
        return json_decode(self::makeRequest('DELETE', $url, $parameters, $data));
    }
}

// class to generate a hash from the request object
class Request {
    public function __construct(string $method, string $url, array $parameters = null, array $data = null) {
        $this->method = $method;
        $this->url = $url;
        $this->parameters = $parameters;
        $this->data = $data;
    }

    public function getHash() {
        return md5(json_encode($this));
    }
}