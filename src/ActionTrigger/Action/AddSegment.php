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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\CustomerSegment;

class AddSegment extends AddTrackedSegment
{
    const OPTION_SEGMENT_ID = 'segmentId';

    protected $name = 'AddSegment';

    public function process(
        ActionDefinitionInterface $actionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $actionDefinition->getOptions();

        if (isset($options[self::OPTION_CONSIDER_PROFILING_CONSENT]) && $options[self::OPTION_CONSIDER_PROFILING_CONSENT] !== false && !$this->consentChecker->hasProfilingConsent($customer)) {
            return;
        }

        if (empty($options[self::OPTION_SEGMENT_ID])) {
            $this->logger->error($this->name . ' action: segmentId option not set');
        }

        if ($segment = CustomerSegment::getById(intval($options[self::OPTION_SEGMENT_ID]))) {
            $this->addSegment(\Pimcore::getContainer()->get('cmf.segment_manager'), $actionDefinition, $customer, $segment);
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
            if ($segment) {
                $options['segmentId'] = $segment->getId();
            }
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
