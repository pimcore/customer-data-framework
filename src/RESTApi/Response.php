<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.12.2016
 * Time: 13:25
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;

class Response extends JsonResponse
{
    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_CREATED = 201;
    const RESPONSE_CODE_NO_CONTENT = 204;
    const RESPONSE_CODE_BAD_REQUEST = 400;
    const RESPONSE_CODE_NOT_FOUND = 404;
}
