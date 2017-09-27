<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
