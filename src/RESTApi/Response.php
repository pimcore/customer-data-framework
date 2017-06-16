<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.12.2016
 * Time: 13:25
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

class Response {

    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_CREATED = 201;
    const RESPONSE_CODE_NO_CONTENT = 204;
    const RESPONSE_CODE_BAD_REQUEST = 400;
    const RESPONSE_CODE_NOT_FOUND = 404;

    private $data;
    private $responseCode;
    private $headers = [];

    public function __construct($data = null, $responseCode = self::RESPONSE_CODE_OK, array $headers = [])
    {
        $this->data = $data;
        $this->responseCode = $responseCode;

        $this->setHeaders($headers);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data = null)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getHeader($key)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
