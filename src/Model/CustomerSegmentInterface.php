<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Model;

use Pimcore\Model\DataObject\CustomerSegmentGroup;

interface CustomerSegmentInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     *
     * @return string
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getReference();

    /**
     * @param string $reference
     *
     * @return void
     */
    public function setReference($reference);

    /**
     * @return CustomerSegmentGroup
     */
    public function getGroup();

    /**
     * @param CustomerSegmentGroup $group
     *
     * @return void
     */
    public function setGroup($group);

    /**
     * @return bool
     */
    public function getCalculated();

    /**
     * @param bool $calculated
     *
     * @return void
     */
    public function setCalculated($calculated);

    /**
     * @return bool
     */
    public function getUseAsTargetGroup();

    /**
     * @return string
     */
    public function getTargetGroup();

    /**
     * @param string $targetGroup
     * @return $this
     */
    public function setTargetGroup($targetGroup);

    /**
     * @return []
     */
    public function getDataForWebserviceExport();

    /**
     * @return void
     */
    public function save();

    /**
     * @return void
     */
    public function delete();
}
