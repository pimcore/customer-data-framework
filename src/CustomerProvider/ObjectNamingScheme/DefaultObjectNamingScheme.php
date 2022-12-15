<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\Concrete;
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
     * @param Concrete&CustomerInterface $customer
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

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $pathField = Service::getVersionDependentDatabaseColumnName('path');
        $keyField = Service::getVersionDependentDatabaseColumnName('key');
        $typeField = Service::getVersionDependentDatabaseColumnName('type');
        $parentIdField = Service::getVersionDependentDatabaseColumnName('parentId');
        $creationDateField = Service::getVersionDependentDatabaseColumnName('creationDate');

        $folders->setCondition(
            $idField . ' in (
                select '. $idField .' from (
                    select `'. $idField .'`, `'. $pathField .'`, `'. $keyField .'`, `'. $typeField .'`, (select count(*) from objects where `' . $parentIdField . '` = `o`. `'. $idField ."`) as counter from objects o) as temp where counter=0 and type = 'folder' and (". $pathField .' like ? or '. $pathField .' like ?) and '. $creationDateField .' < ?)',
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
     * @param string $namingScheme
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
