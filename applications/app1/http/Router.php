<?php

namespace http;

class Router {

    /** @var Rest[string] */
    private $endpoints = [];

    /**
     * @param Rest $rest
     * @return void
     */
    public function addRestEndpoint(Rest $rest) /* : void */
    {
        $this->endpoints[$rest->name()] = $rest;
    }

    public function resolve() : string
    {
        list($obj, $id) = $this->path();
        foreach ($this->endpoints as $ep) {
            if ($ep->name() === $obj) {
                list($status, $items) = call_user_func([$ep, $this->method()], $id);
                http_response_code($status);
                return json_encode($items, JSON_FORCE_OBJECT);
            }
        }

        http_response_code(404);
        return '';
    }

    public function method() : string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return array
     */
    public function path() : array
    {
        $path = $_SERVER['PATH_INFO'];

        return array_values(array_filter(explode('/', $path)));
    }
}