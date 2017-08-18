<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataValidator;

use CustomerManagementFrameworkBundle\Config;

class BlacklistValidator implements DataValidatorInterface
{
    /**
     * returns false if given email is on the blacklist
     *
     * @param $item
     *
     * @return bool
     */
    public function isValid($data)
    {
        $config = Config::getConfig();

        $blacklistFile = (string)$config->General->mailBlackListFile;

        $blacklistedMails = [];

        if ($blacklistFile && file_exists($blacklistFile)) {
            $blacklistedMails = preg_split("/\r\n|\n|\r/", file_get_contents($blacklistFile));

            foreach ($blacklistedMails as $key => $value) {
                $blacklistedMails[$key] = trim(strtolower($value));
            }
        }

        return !in_array($data, $blacklistedMails);
    }
}
