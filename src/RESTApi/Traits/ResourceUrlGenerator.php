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

namespace CustomerManagementFrameworkBundle\RESTApi\Traits;

use Pimcore\View\Helper\Url;

trait ResourceUrlGenerator
{
    /**
     * @var string
     */
    protected $apiResourceRoute;

    /**
     * @param string $apiResourceRoute
     *
     * @return $this
     */
    public function setApiResourceRoute($apiResourceRoute)
    {
        $this->apiResourceRoute = $apiResourceRoute;

        return $this;
    }

    /**
     * Generate record URL
     *
     * @param int $id
     *
     * @return string|null
     */
    protected function generateResourceApiUrl($id)
    {
        if (!$this->apiResourceRoute) {
            return null;
        }

        return \Pimcore::getContainer()->get('router')->generate(
            $this->apiResourceRoute,
            [
                'id' => $id,
            ]
        );
    }
}
