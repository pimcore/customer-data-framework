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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;

use Pimcore\Model\Element\ElementInterface;

/**
 * Interface TypeMapperInterface
 *
 * Interface for mapping types to element type strings used in database communication
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper
 */
interface TypeMapperInterface
{
    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided element
     *
     * @param ElementInterface $element
     *
     * @return string
     */
    public function getTypeStringByObject(ElementInterface $element): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided sub type
     *
     * @param string $subType
     *
     * @return string
     */
    public function getTypeStringBySubType(string $subType): string;
}
