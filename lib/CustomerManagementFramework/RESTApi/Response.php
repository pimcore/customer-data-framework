<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.12.2016
 * Time: 13:25
 */

namespace CustomerManagementFramework\RESTApi;

class Response {

    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_BAD_REQUEST = 400;
    const RESPONSE_CODE_NOT_FOUND = 404;

    private $data;
    private $responseCode;

    public function __construct($data, $responseCode = self::RESPONSE_CODE_OK)
    {
        $this->data = $data;
        $this->responseCode = $responseCode;
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
    public function setData($data)
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



}