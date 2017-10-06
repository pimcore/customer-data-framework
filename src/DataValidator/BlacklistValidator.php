<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\DataValidator;

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
        $blacklistFile = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.general.mailBlackListFile');

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
