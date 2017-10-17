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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\CustomerSegment;

class AddSegment extends AbstractAction
{
    const OPTION_SEGMENT_ID = 'segmentId';
    const OPTION_REMOVE_OTHER_SEGMENTS_FROM_SEGMENT_GROUP = 'removeOtherSegmentsFromGroup';

    public function process(ActionDefinitionInterface $actionDefinition, CustomerInterface $customer)
    {
        $options = $actionDefinition->getOptions();

        if (empty($options[self::OPTION_SEGMENT_ID])) {
            $this->logger->error('AddSegment action: segmentId option not set');
        }

        if ($segment = CustomerSegment::getById(intval($options[self::OPTION_SEGMENT_ID]))) {
            $this->logger->info(
                sprintf(
                    'AddSegment action: add segment %s (%s) to customer %s (%s)',
                    (string)$segment,
                    $segment->getId(),
                    (string)$customer,
                    $customer->getId()
                )
            );

            $deleteSegments = [];

            if ($options{self::OPTION_REMOVE_OTHER_SEGMENTS_FROM_SEGMENT_GROUP} && ($segmentGroup = $segment->getGroup(
                ))
            ) {
                $deleteSegments = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentsFromSegmentGroup(
                    $segmentGroup,
                    [$segment]
                );
            }

            \Pimcore::getContainer()->get('cmf.segment_manager')->mergeSegments(
                $customer,
                [$segment],
                $deleteSegments,
                'AddSegment action trigger action'
            );
            \Pimcore::getContainer()->get('cmf.segment_manager')->saveMergedSegments($customer);
        } else {
            $this->logger->error(
                sprintf('AddSegment action: segment with ID %s not found', $options[self::OPTION_SEGMENT_ID])
            );
        }
    }

    public static function createActionDefinitionFromEditmode(\stdClass $setting)
    {
        $action = parent::createActionDefinitionFromEditmode($setting);

        $options = $action->getOptions();

        if (isset($options['segment'])) {
            $segment = CustomerSegment::getByPath($options['segment']);
            $options['segmentId'] = $segment->getId();
            unset($options['segment']);
        }

        $action->setOptions($options);

        return $action;
    }

    public static function getDataForEditmode(ActionDefinitionInterface $actionDefinition)
    {
        $options = $actionDefinition->getOptions();

        if (isset($options['segmentId'])) {
            if ($segment = CustomerSegment::getById(intval($options['segmentId']))) {
                $options['segment'] = $segment->getFullPath();
            }
        }

        $actionDefinition->setOptions($options);

        return $actionDefinition->toArray();
    }
}
