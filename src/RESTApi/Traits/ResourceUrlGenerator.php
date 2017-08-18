<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
