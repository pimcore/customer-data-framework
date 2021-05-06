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

class ExportActivitiesFilterParams
{
    /**
     * @var string|bool
     */
    private $type;

    /**
     * @param Request $request
     *
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        $params = new static();
        $params->setType($request->get('type', false));
        $params->setModifiedSinceTimestamp($request->get('modifiedSinceTimestamp'));
        $params->setAllParams($request->request->all());

        return $params;
    }

    /**
     * @var int
     */
    private $modifiedSinceTimestamp;

    /**
     * @var array
     */
    private $allParams;

    /**
     * @return string|bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type |boolean
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getModifiedSinceTimestamp()
    {
        return $this->modifiedSinceTimestamp;
    }

    /**
     * @param int $modifiedSinceTimestamp
     */
    public function setModifiedSinceTimestamp($modifiedSinceTimestamp)
    {
        $this->modifiedSinceTimestamp = $modifiedSinceTimestamp;
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
}
