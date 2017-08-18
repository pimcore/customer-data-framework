<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\RESTApi\Exception;

use CustomerManagementFrameworkBundle\RESTApi\Response;

class ResourceNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public function getResponseCode()
    {
        return Response::HTTP_NOT_FOUND;
    }
}
