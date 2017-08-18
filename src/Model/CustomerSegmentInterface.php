<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model;

use Pimcore\Model\Object\CustomerSegmentGroup;

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
