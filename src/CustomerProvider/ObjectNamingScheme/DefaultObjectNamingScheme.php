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

namespace CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\DataObject\Service;

class DefaultObjectNamingScheme implements ObjectNamingSchemeInterface
{
    use LoggerAware;

    /**
     * @var string
     */
    private $namingScheme;

    /**
     * @var string
     */
    private $parentPath;

    /**
     * @var string
     */
    private $archiveDir;

    /**
     * DefaultObjectNamingScheme constructor.
     *
     * @param string $namingScheme
     * @param string $parentPath
     * @param string $archiveDir
     */
    public function __construct($namingScheme, $parentPath, $archiveDir)
    {
        $this->namingScheme = $namingScheme;
        $this->parentPath = $parentPath;
        $this->archiveDir = $archiveDir;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $parentPath
     * @param string $namingScheme
     *
     * @return void
     */
    public function apply(CustomerInterface $customer)
    {
        $namingScheme = $this->determineNamingScheme($customer);
        $parentPath = $customer->getPublished() ? $this->parentPath : $this->archiveDir;

        if ($namingScheme) {
            $namingScheme = $this->extractNamingScheme($customer, $namingScheme);

            $key = $namingScheme[sizeof($namingScheme) - 1];
            unset($namingScheme[sizeof($namingScheme) - 1]);

            $parentPath .= '/'.implode('/', $namingScheme);
            $customer->setKey($key);
        }

        $parentPath = $this->correctPath($parentPath);

        $customer->setParent(Service::createFolderByPath($parentPath));

        if (!$customer->getKey()) {
            $customer->setKey(uniqid());
        }
        Objects::checkObjectKey($customer);
    }

    public function cleanupEmptyFolders()
    {
        if (!$this->parentPath) {
            return;
        }

        if (!$this->namingScheme) {
            return;
        }

        $folders = new Listing;

        // apply it for folders older then 10 minutes only
        $timestamp = time() - 60 * 10;

        $archiveDir = $this->archiveDir ?: $this->parentPath;

        $folders->setCondition(
            "o_id in (select o_id from (select o_id, o_path, o_key, o_type, (select count(*) from objects where o_parentId = o.o_id) as counter from objects o) as temp where counter=0 and o_type = 'folder' and (o_path like ? or o_path like ?) and o_creationDate < ?)",
            [
                str_replace('//', '/', $this->parentPath.'/%'),
                str_replace('//', '/', $archiveDir .'/%'),
                $timestamp
            ]
        );

        foreach ($folders as $folder) {
            if ($folder instanceof Folder) {
                $folder->delete();
                $this->getLogger()->info('delete empty folder '. (string) $folder);
            }
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return string
     */
    public function determineNamingScheme(CustomerInterface $customer)
    {
        return $this->namingScheme;
    }

    private function correctPath($path)
    {
        return str_replace('//', '/', $path);
    }

    /**
     * @param CustomerInterface $customer
     * @param $namingScheme
     *
     * @return array
     */
    private function extractNamingScheme(CustomerInterface $customer, $namingScheme)
    {
        $namingScheme = explode('/', $namingScheme);
        foreach ($namingScheme as $i => $namingSchemeItem) {
            preg_match_all('/{([a-zA-Z0-9]*)}/', $namingSchemeItem, $matchedPlaceholder);

            if (sizeof($matchedPlaceholder)) {
                foreach ($matchedPlaceholder[0] as $j => $placeholder) {
                    $field = $matchedPlaceholder[1][$j];

                    $getter = 'get'.ucfirst($field);
                    if (method_exists($customer, $getter)) {
                        $value = (string)$customer->$getter();
                        $namingScheme[$i] = str_replace($placeholder, $value, $namingScheme[$i]);
                    }
                }
            }
            $namingScheme[$i] = trim($namingScheme[$i]) ?: '--';
            $namingScheme[$i] = Objects::getValidKey($namingScheme[$i]);
        }

        return $namingScheme;
    }
}
