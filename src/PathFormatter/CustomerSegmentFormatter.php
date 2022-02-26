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

namespace CustomerManagementFrameworkBundle\PathFormatter;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\Element\ElementInterface;

class CustomerSegmentFormatter
{
    /**
     * @param array $result containing the nice path info. Modify it or leave it as it is. Pass it out afterwards!
     * @param ElementInterface $source the source object
     * @param array $targets list of nodes describing the target elements
     * @param array $params optional parameters. may contain additional context information in the future. to be defined.
     *
     * @return mixed list of display names.
     */
    public static function formatPath($result, ElementInterface $source, $targets, $params)
    {
        $context = $params['context'];

        foreach ($targets as $key => $item) {
            $newPath = $item['path'] .  ' - ' . time();
            if (isset($context['language'])) {
                $newPath .= ' ' . $context['language'];
            }

            if ($item['type'] === 'object') {
                $targetObject = Concrete::getById($item['id']);
                if ($targetObject instanceof CustomerSegment) {
                    $newPath = '<strong>' . $targetObject->getName() . '</strong>';

                    /** @var CustomerSegmentGroup|null $group */
                    $group = $targetObject->getGroup();
                    if ($group) {
                        $newPath .= ' [' . $group->getName() . ']';
                    }
                }
            }

            // don't forget to use the same key, otherwise the matching doesn't work
            $result[$key] = $newPath;
        }

        return $result;
    }
}
