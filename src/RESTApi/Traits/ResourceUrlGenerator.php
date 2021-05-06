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
