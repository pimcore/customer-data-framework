<?php

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;

class MinifiedAssetUrl extends Helper
{
    public function getName()
    {
        return 'MinifiedAssetUrl';
    }

    /**
     * Get URL with .min extension (e.g. .min.js instead of .js) if condition matches. Condition defaults
     * to pimcore not being in debug mode, but can be overridden with a callable or boolean.
     *
     * @param $url
     * @param null|bool|callable $condition
     * @param string $minifiedExtension
     *
     * @return string
     */
    public function minifiedAssetUrl($url, $condition = null, $minifiedExtension = 'min')
    {
        if (null === $condition) {
            $condition = !\Pimcore::inDebugMode();
        } else {
            if (is_callable($condition)) {
                $condition = call_user_func($condition, $url, $minifiedExtension);
            }
        }

        if (!$condition) {
            return $url;
        }

        $parts = explode('.', $url);

        $extension = array_pop($parts);
        $extension = $minifiedExtension.'.'.$extension;

        $parts[] = $extension;

        return implode('.', $parts);
    }
}
