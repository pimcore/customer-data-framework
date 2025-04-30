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

use Symfony\Component\HttpFoundation\Request;

interface CrudHandlerInterface
{
    /**
     * GET /
     *
     *
     * @return Response
     */
    public function listRecords(Request $request);

    /**
     * GET /{id}
     *
     *
     * @return Response
     */
    public function readRecord(Request $request);

    /**
     * POST /
     *
     *
     * @return Response
     */
    public function createRecord(Request $request);

    /**
     * PUT /{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     *
     * @return Response
     */
    public function updateRecord(Request $request);

    /**
     * DELETE /{id}
     *
     *
     * @return Response
     */
    public function deleteRecord(Request $request);
}
