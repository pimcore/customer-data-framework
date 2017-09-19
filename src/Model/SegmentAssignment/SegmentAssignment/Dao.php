<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-12
 * Time: 2:56 PM
 */

namespace CustomerManagementFrameworkBundle\Model\SegmentAssignment\SegmentAssignment;

use CustomerManagementFrameworkBundle\Model\SegmentAssignment\SegmentAssignment;
use CustomerManagementFrameworkBundle\Model\SegmentAssignmentInterface;
use Pimcore\Model\Dao\AbstractDao;

class Dao extends AbstractDao {

    const TABLE_NAME = 'plugin_cmf_segment_assignment';

    const ATTRIBUTE_SEGMENT_IDS = 'segments';
    const ATTRIBUTE_ELEMENT_ID = 'elementId';
    const ATTRIBUTE_ELEMENT_TYPE = 'elementType';
    const ATTRIBUTE_BREAKS_INHERITANCE = 'breaksInheritance';

    /**
     * @var SegmentAssignmentInterface
     */
    protected $model = null;

    /**
     * @return SegmentAssignmentInterface
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param SegmentAssignmentInterface $model
     */
    public function setModel( $model) {
        $this->model = $model;
    }

    /**
     * @param SegmentAssignmentInterface $model
     */
    public function __construct(?SegmentAssignmentInterface $model) {
        $this->setModel($model);
    }

    public function getByIdAndType(string $elementId, string $elementType): SegmentAssignmentInterface {
        $row = $this->db->fetchRow('SELECT * FROM '. static::TABLE_NAME .' WHERE '. static::ATTRIBUTE_ELEMENT_ID .' = ? AND '. static::ATTRIBUTE_ELEMENT_TYPE .' = ?', [$elementId, $elementType]);
        $segmentIds = explode(',', $row[static::ATTRIBUTE_SEGMENT_IDS]);

        return new SegmentAssignment($segmentIds, $row[static::ATTRIBUTE_ELEMENT_ID], $row[static::ATTRIBUTE_ELEMENT_TYPE], $row[static::ATTRIBUTE_BREAKS_INHERITANCE]);
    }
}