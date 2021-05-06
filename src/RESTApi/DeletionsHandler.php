<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
     * @param Request $request
     */
    public function listRecords(Request $request)
    {
        $entityType = $request->get('entityType');
        $deletionsSinceTimestamp = $request->get('deletionsSinceTimestamp');

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
