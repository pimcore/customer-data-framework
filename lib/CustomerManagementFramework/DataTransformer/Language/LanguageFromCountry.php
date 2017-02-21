<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-21
 * Time: 11:19
 */

namespace CustomerManagementFramework\DataTransformer\Language;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class LanguageFromCountry implements DataTransformerInterface
{
    /**
     * Tries to determine language based on country code.
     *
     * @param mixed $data
     * @param array $options
     * @return string|false
     */
    public function transform($data, $options = [])
    {
        $countryCode = $data;

        if(strlen(trim($countryCode)) != 2) {
            return false;
        }

        $localelist = \Zend_Locale::getLocaleList();

        foreach($localelist as $locale => $trash) {

            $locale = explode('_', $locale);
            if(isset($locale[1])) {

                if(strtolower($locale[1]) == strtolower(trim($countryCode))) {
                    return $locale[0];
                }

            }
        }

        return false;
    }

}