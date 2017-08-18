<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Language;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class LanguageFromCountry implements DataTransformerInterface
{
    /**
     * Tries to determine language based on country code (approximate -> warn will in many be wrong).
     *
     * @param mixed $data
     * @param array $options
     *
     * @return string|false
     */
    public function transform($data, $options = [])
    {
        $countryCode = trim($data);

        if (strlen($countryCode) != 2) {
            return false;
        }

        $localelist = \Pimcore::getContainer()->get('pimcore.locale')->getLocaleList();

        foreach ($localelist as $locale) {
            $locale = explode('_', $locale);
            if (isset($locale[1])) {
                if (strtolower($locale[1]) == strtolower($countryCode)) {
                    return $locale[0];
                }
            }
        }

        return false;
    }
}
