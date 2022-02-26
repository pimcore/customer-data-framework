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
     * @var int
     */
    private $modificationTimestamp;

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
        $params->setModificationTimestamp(intval($request->get('modificationTimestamp')));
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
     * @return int[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param int[]|int $segments
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

    /**
     * @return int
     */
    public function getModificationTimestamp(): int
    {
        return $this->modificationTimestamp;
    }

    /**
     * @param int $modificationTimestamp
     */
    public function setModificationTimestamp(int $modificationTimestamp): void
    {
        $this->modificationTimestamp = $modificationTimestamp;
    }
}
