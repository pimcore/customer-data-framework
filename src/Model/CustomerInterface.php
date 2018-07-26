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

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\SaveOptions;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\Data\Consent;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\Element\ElementInterface;

interface CustomerInterface extends ElementInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public static function classId();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return bool
     */
    public function getPublished();

    /**
     * @return bool
     */
    public function getActive();

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setActive($active);

    /**
     * @return string
     */
    public function getGender();

    /**
     * @param $gender
     *
     * @return void
     */
    public function setGender($gender);

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @param $firstname
     *
     * @return void
     */
    public function setFirstname($firstname);

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @param $lastname
     *
     * @return void
     */
    public function setLastname($lastname);

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @param $street
     *
     * @return void
     */
    public function setStreet($street);

    /**
     * @return string
     */
    public function getZip();

    /**
     * @param $zip
     *
     * @return void
     */
    public function setZip($zip);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param $city
     *
     * @return void
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @param $countryCode
     *
     * @return void
     */
    public function setCountryCode($countryCode);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param $email
     *
     * @return void
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @param $phone
     *
     * @return void
     */
    public function setPhone($phone);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]|null
     */
    public function getManualSegments();

    /**
     * @param array $segments
     *
     * @return void
     */
    public function setManualSegments($segments);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]|null
     */
    public function getCalculatedSegments();

    /**
     * @param array $segments
     *
     * @return void
     */
    public function setCalculatedSegments($segments);

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments();

    /**
     * @return string
     */
    public function getIdEncoded();

    /**
     * @param $idEncoded
     *
     * @return void
     */
    public function setIdEncoded($idEncoded);

    /**
     * @return Consent|bool|null
     */
    public function getProfilingConsent();

    /**
     * @return array
     */
    public function cmfToArray();

    /**
     * @return array
     */
    public function getRelatedCustomerGroups();

    /**
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveDirty($disableVersions = true);

    /**
     * @param SaveOptions $saveOptions
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveWithOptions(SaveOptions $saveOptions, $disableVersions = false);

    /**
     * @return CustomerSaveManagerInterface
     */
    public function getSaveManager();
}
