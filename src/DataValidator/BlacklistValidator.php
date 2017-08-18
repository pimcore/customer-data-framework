<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-02
 * Time: 12:35
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
