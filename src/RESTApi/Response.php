<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
