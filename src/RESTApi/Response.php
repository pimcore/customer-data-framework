<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response extends JsonResponse
{
    const RESPONSE_CODE_OK = 200;

    const RESPONSE_CODE_CREATED = 201;

    const RESPONSE_CODE_NO_CONTENT = 204;

    const RESPONSE_CODE_BAD_REQUEST = 400;

    const RESPONSE_CODE_NOT_FOUND = 404;
}
