<?php

namespace CustomerManagementFramework\View\Helper;

class MinifiedAssetUrl extends \Zend_View_Helper_Abstract
{
    /**
     * Get URL with .min extension (e.g. .min.js instead of .js) if condition matches. Condition defaults
     * to pimcore not being in debug mode, but can be overridden with a callable or boolean.
     *
     * @param $url
     * @param null|bool|callable $condition
     * @param string $minifiedExtension
     * @return string
     */
    public function minifiedAssetUrl($url, $condition = null, $minifiedExtension = 'min')
    {
        if (null === $condition) {
            $condition = !\Pimcore::inDebugMode();
        } else if (is_callable($condition)) {
            $condition = call_user_func($condition, $url, $minifiedExtension);
        }

        if (!$condition) {
            return $url;
        }

        $parts = explode('.', $url);

        $extension = array_pop($parts);
        $extension = $minifiedExtension . '.' . $extension;

        $parts[] = $extension;

        return implode('.', $parts);
    }
}
