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
 * @category   Pimcore
 * @package    Object
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Import\ColumnConfig\Operator;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\DataObject\Import\ColumnConfig\AbstractConfigElement;
use Pimcore\DataObject\Import\ColumnConfig\Operator\AbstractOperator;
use Pimcore\Model\DataObject\AbstractObject;

class CustomerSegments extends AbstractOperator
{
    /**
     * @var bool
     */
    private $replaceSegments;

    public function __construct(\stdClass $config, $context = null)
    {
        parent::__construct($config, $context);

        $this->replaceSegments = (bool) $config->replaceSegments;
    }

    public function process($element, &$target, array &$rowData, $colIndex, array &$context = [])
    {
        if (!$target instanceof CustomerInterface) {
            return;
        }

        $originalCellData = $rowData[$colIndex];

        $segmentIds = explode(',',$originalCellData);

        array_map('trim', $segmentIds);

        $segments = [];

        foreach($segmentIds  as $id) {

            if(is_numeric($id)) {
                $segment = $this->getSegmentManager()->getSegmentById($id);
                if (!$segment instanceof CustomerSegmentInterface) {
                    continue;
                }
                $segments[] = $segment;
            } else {
                $segment = AbstractObject::getByPath($id);
                if (!$segment instanceof CustomerSegmentInterface) {
                    continue;
                }
                $segments[] = $segment;
            }
        }


        $removeSegments = [];
        if($this->replaceSegments) {
            $_allSegments = $target->getAllSegments();
            $allSegments = [];
            foreach ($_allSegments as $segment) {
                $allSegments[$segment->getId()] = $segment;
            }

            foreach($segments as $segment) {
                if(isset($allSegments[$segment->getId()])) {
                    unset($allSegments[$segment->getId()]);
                }
            }

            $removeSegments = array_values($allSegments);
        }

        $this->getSegmentManager()->mergeSegments($target, $segments, $removeSegments);


        $rowData[$colIndex] = '';
    }

    private function getSegmentManager(): SegmentManagerInterface
    {
        return \Pimcore::getContainer()->get(SegmentManagerInterface::class);
    }
}
