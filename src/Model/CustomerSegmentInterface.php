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

namespace CustomerManagementFrameworkBundle\Model;

use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\Element\AbstractElement;

interface CustomerSegmentInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param $name
     *
     * @return string
     */
    public function setName(?string $name);

    /**
     * @return string
     */
    public function getReference(): ?string;

    /**
     * @param string $reference
     *
     * @return void
     */
    public function setReference(?string $reference);

    /**
     * @return CustomerSegmentGroup
     */
    public function getGroup(): ?AbstractElement;

    /**
     * @param CustomerSegmentGroup $group
     *
     * @return void
     */
    public function setGroup(?AbstractElement $group);

    /**
     * @return bool
     */
    public function getCalculated(): ?bool;

    /**
     * @param bool $calculated
     *
     * @return void
     */
    public function setCalculated(bool $calculated);

    /**
     * @return bool
     */
    public function getUseAsTargetGroup(): ?bool;

    /**
     * @return string
     */
    public function getTargetGroup(): ?string;

    /**
     * @param string $targetGroup
     *
     * @return $this
     */
    public function setTargetGroup(?string $targetGroup);

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
