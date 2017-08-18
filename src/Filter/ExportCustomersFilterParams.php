<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

class ExportCustomersFilterParams
{
    /**
     * @var bool
     */
    private $includeActivities;

    /**
     * @var array
     */
    private $allParams;

    /**
     * @var int[]
     */
    private $segments;

    /**
     * @param Request $request
     *
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        $params = new static();
        $params->setIncludeActivities($request->get('includeActivities') == 'true' ? true : false);
        $params->setSegments($request->get('segments'));
        $params->setAllParams($request->request->all());

        return $params;
    }

    /**
     * @return bool
     */
    public function getIncludeActivities()
    {
        return $this->includeActivities;
    }

    /**
     * @param bool $includeActivities
     */
    public function setIncludeActivities($includeActivities)
    {
        $this->includeActivities = $includeActivities;
    }

    /**
     * @return array
     */
    public function getAllParams()
    {
        return $this->allParams;
    }

    /**
     * @param array $allParams
     */
    public function setAllParams($allParams)
    {
        $this->allParams = $allParams;
    }

    /**
     * @return int|\int[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param int|\int[] $segments
     */
    public function setSegments($segments)
    {
        if (is_array($segments)) {
            $this->segments = $segments;
        } elseif ($segments) {
            $this->segments = [$segments];
        } else {
            $this->segments = [];
        }
    }
}
