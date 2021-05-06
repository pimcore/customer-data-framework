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

namespace CustomerManagementFrameworkBundle\DataTransformer\Terms;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;
use CustomerManagementFrameworkBundle\Model\AbstractTermSegmentBuilderDefinition;
use Pimcore\Model\DataObject\TermSegmentBuilderDefinition;

class TermSegmentBuilderTransformer implements DataTransformerInterface
{
    const OPTION_TERM_SEGMENT_BUILDER_DEFINITION = 'termSegmentBuilderDefinition';

    public function transform($data, $options = [])
    {
        if (empty($options[self::OPTION_TERM_SEGMENT_BUILDER_DEFINITION]) || !($options[self::OPTION_TERM_SEGMENT_BUILDER_DEFINITION] instanceof TermSegmentBuilderDefinition)) {
            throw new \Exception('no termSegmentBuilderDefinition option given');
        }

        /**
         * @var AbstractTermSegmentBuilderDefinition $def ;
         */
        $def = $options[self::OPTION_TERM_SEGMENT_BUILDER_DEFINITION];

        $terms = $def->definitionsToArray();

        foreach ($terms as $targetTerm => $matchingTerms) {
            foreach ($matchingTerms as $matchingTerm) {
                if ($matchingTerm == $data) {
                    return $targetTerm;
                }

                if (@preg_match($matchingTerm, $data)) {
                    return $targetTerm;
                }
            }
        }

        return false;
    }
}
