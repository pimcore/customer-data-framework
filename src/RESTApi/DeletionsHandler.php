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

use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Symfony\Component\HttpFoundation\Request;

class DeletionsHandler extends AbstractHandler
{
    use LoggerAware;

    /**
     * GET /deletions
     *
     */
    public function listRecords(Request $request)
    {
        $entityType = $request->query->getString('entityType');
        $deletionsSinceTimestamp = $request->query->getInt('deletionsSinceTimestamp');

        $timestamp = time();

        if (!$entityType) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'parameter entityType is required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        if (!in_array($entityType, ['activities', 'customers'])) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'entityType must be activities or customers',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        $result = \Pimcore::getContainer()->get('cmf.activity_store')->getDeletionsData(
            $entityType,
            $deletionsSinceTimestamp
        );
        $result['success'] = true;
        $result['timestamp'] = $timestamp;

        return new Response($result);
    }
}
