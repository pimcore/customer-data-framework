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

namespace CustomerManagementFrameworkBundle\RESTApi\Traits;

use CustomerManagementFrameworkBundle\RESTApi\Response;

trait ResponseGenerator
{
    /**
     * Create a JSON response with normalized body containing timestamp
     *
     * @param array|null $data
     * @param $code
     *
     * @return Response
     */
    protected function createResponse(array $data = null, $code = Response::HTTP_OK)
    {
        $responseData = null;
        if (null !== $data) {
            $responseData = [
                'timestamp' => time(),
            ];

            $responseData['data'] = $data;
        }

        return new Response($responseData, $code);
    }

    /**
     * Create error response
     *
     * @param $errors
     * @param int $code
     *
     * @return Response
     */
    protected function createErrorResponse($errors, $code = Response::HTTP_BAD_REQUEST)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $response = new Response(
            [
                'success' => false,
                'errors' => $errors,
            ],
            $code
        );

        return $response;
    }
}
