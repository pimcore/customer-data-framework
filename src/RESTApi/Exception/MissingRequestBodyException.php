<?php

namespace CustomerManagementFrameworkBundle\RESTApi\Exception;

use CustomerManagementFrameworkBundle\RESTApi\Response;

class MissingRequestBodyException extends \RuntimeException implements ExceptionInterface
{
    public function getResponseCode()
    {
        return Response::HTTP_BAD_REQUEST;
    }

}
