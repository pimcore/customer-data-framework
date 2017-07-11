<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-21
 * Time: 11:19
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Language;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class LanguageFromCountry implements DataTransformerInterface {

    /**
     * Tries to determine language based on country code (approximate -> warn will in many be wrong).
     *
     * @param mixed $data
     * @param array $options
     * @return string|false
     */
    public function transform($data, $options = [])
    {
        $countryCode = trim($data);

        if(strlen($countryCode) != 2) {
            return false;
        }

        $localelist = \Pimcore::getContainer()->get('pimcore.locale')->getLocaleList();

        foreach($localelist as $locale) {

            $locale = explode('_', $locale);
            if(isset($locale[1])) {

                if(strtolower($locale[1]) == strtolower($countryCode)) {
                    return $locale[0];
                }

            }
        }

        return false;
    }

}