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
    public function getActive(): ?bool;

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setActive(?bool $active);

    /**
     * @return string
     */
    public function getGender(): ?string;

    /**
     * @param $gender
     *
     * @return void
     */
    public function setGender(?string $gender);

    /**
     * @return string
     */
    public function getFirstname(): ?string;

    /**
     * @param $firstname
     *
     * @return void
     */
    public function setFirstname(?string $firstname);

    /**
     * @return string
     */
    public function getLastname(): ?string;

    /**
     * @param $lastname
     *
     * @return void
     */
    public function setLastname(?string $lastname);

    /**
     * @return string
     */
    public function getStreet(): ?string;

    /**
     * @param $street
     *
     * @return void
     */
    public function setStreet(?string $street);

    /**
     * @return string
     */
    public function getZip(): ?string;

    /**
     * @param $zip
     *
     * @return void
     */
    public function setZip(?string $zip);

    /**
     * @return string
     */
    public function getCity(): ?string;

    /**
     * @param $city
     *
     * @return void
     */
    public function setCity(?string $city);

    /**
     * @return string
     */
    public function getCountryCode(): ?string;

    /**
     * @param $countryCode
     *
     * @return void
     */
    public function setCountryCode(?string $countryCode);

    /**
     * @return string
     */
    public function getEmail(): ?string;

    /**
     * @param $email
     *
     * @return void
     */
    public function setEmail(?string $email);

    /**
     * @return string
     */
    public function getPhone(): ?string;

    /**
     * @param $phone
     *
     * @return void
     */
    public function setPhone(?string $phone);

    /**
     * @return string|null
     */
    public function getCustomerLanguage();

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]
     */
    public function getManualSegments(): array;

    /**
     * @param array $segments
     *
     * @return void
     */
    public function setManualSegments(?array $segments);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]
     */
    public function getCalculatedSegments(): array;

    /**
     * @param array $segments
     *
     * @return void
     */
    public function setCalculatedSegments(?array $segments);

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments();

    /**
     * @return string
     */
    public function getIdEncoded(): ?string;

    /**
     * @param $idEncoded
     *
     * @return void
     */
    public function setIdEncoded(?string $idEncoded);

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
