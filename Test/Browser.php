<?php

namespace Enlightener\Test\Cors;

use Illuminate\Http\Request;

class Browser
{
    /**
     * The request instance.
     * 
     * @var Request
     */
    protected $request;

    /**
     * Get the request instance.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Create a new request instance.
     */
    public function createRequest(): self
    {
        $this->request = new Request;

        return $this;
    }

    /**
     * Create a new preflight request instance with the arguments.
     */
    public function createPreflightRequest(array $headers): Request
    {
        return $this->createRequest()
                    ->setMethod('OPTIONS')
                    ->addHeaders($headers)
                    ->getRequest();
    }

    /**
     * Add headers to the request.
     */
    public function addHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $this->request->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Dynamically handle calls into the browser instance.
     */
    public function __call(string $method, array $parameters): self
    {
        if ($method == 'setMethod') {
            $this->request->{$method}(...$parameters);
        }

        return $this;
    }
}
