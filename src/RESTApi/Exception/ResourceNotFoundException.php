<?php

namespace CustomerManagementFrameworkBundle\RESTApi\Exception;

use CustomerManagementFrameworkBundle\RESTApi\Response;

class ResourceNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public function getResponseCode()
    {
        return Response::HTTP_NOT_FOUND;
    }
}
