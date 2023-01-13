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

namespace CustomerManagementFrameworkBundle\Model\SegmentAssignment\SegmentAssignment;

use CustomerManagementFrameworkBundle\Model\SegmentAssignment\SegmentAssignment;
use CustomerManagementFrameworkBundle\Model\SegmentAssignmentInterface;
use Pimcore\Model\Dao\AbstractDao;

class Dao extends AbstractDao
{
    const TABLE_NAME = 'plugin_cmf_segment_assignment';

    const ATTRIBUTE_SEGMENT_IDS = 'segments';
    const ATTRIBUTE_ELEMENT_ID = 'elementId';
    const ATTRIBUTE_ELEMENT_TYPE = 'elementType';
    const ATTRIBUTE_BREAKS_INHERITANCE = 'breaksInheritance';

    /**
     * @var SegmentAssignmentInterface|null
     */
    protected $model = null;

    /**
     * @return SegmentAssignmentInterface|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param SegmentAssignmentInterface|null $model
     *
     * @return $this
     */
    public function setModel($model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param SegmentAssignmentInterface|null $model
     */
    public function __construct(?SegmentAssignmentInterface $model)
    {
        $this->setModel($model);
    }

    public function getByIdAndType(string $elementId, string $elementType): SegmentAssignmentInterface
    {
        $row = $this->db->fetchAssociative('SELECT * FROM '. static::TABLE_NAME .' WHERE '. static::ATTRIBUTE_ELEMENT_ID .' = ? AND '. static::ATTRIBUTE_ELEMENT_TYPE .' = ?', [$elementId, $elementType]);
        $segmentIds = explode(',', $row[static::ATTRIBUTE_SEGMENT_IDS]);

        return new SegmentAssignment($segmentIds, $row[static::ATTRIBUTE_ELEMENT_ID], $row[static::ATTRIBUTE_ELEMENT_TYPE], $row[static::ATTRIBUTE_BREAKS_INHERITANCE]);
    }
}
