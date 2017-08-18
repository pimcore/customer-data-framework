<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

use Symfony\Component\HttpFoundation\Request;

interface CrudHandlerInterface
{
    /**
     * GET /
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listRecords(Request $request);

    /**
     * GET /{id}
     *
     * @param Request $request
     * @param array $params
     *
     * @return Response
     */
    public function readRecord(Request $request);

    /**
     * POST /
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createRecord(Request $request);

    /**
     * PUT /{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param Request $request
     * @param array $params
     *
     * @return Response
     */
    public function updateRecord(Request $request);

    /**
     * DELETE /{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteRecord(Request $request);
}
