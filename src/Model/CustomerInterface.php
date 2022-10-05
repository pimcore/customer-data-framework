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

/**
 * @method \Pimcore\Model\DataObject\ClassDefinition getClass()
 * @method static setPublished(bool $o_published)
 */
interface CustomerInterface extends ElementInterface
{
    /**
     * @return int
     */
    public static function classId();

    /**
     * @return bool
     */
    public function getPublished();

    /**
     * @return bool|null
     */
    public function getActive(): ?bool;

    /**
     * @param bool|null $active
     *
     * @return void
     */
    public function setActive(?bool $active);

    /**
     * @return string|null
     */
    public function getGender(): ?string;

    /**
     * @param string|null $gender
     *
     * @return void
     */
    public function setGender(?string $gender);

    /**
     * @return string|null
     */
    public function getFirstname(): ?string;

    /**
     * @param string|null $firstname
     *
     * @return void
     */
    public function setFirstname(?string $firstname);

    /**
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * @param string|null $lastname
     *
     * @return void
     */
    public function setLastname(?string $lastname);

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     *
     * @return void
     */
    public function setStreet(?string $street);

    /**
     * @return string|null
     */
    public function getZip(): ?string;

    /**
     * @param string|null $zip
     *
     * @return void
     */
    public function setZip(?string $zip);

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     *
     * @return void
     */
    public function setCity(?string $city);

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string;

    /**
     * @param string|null $countryCode
     *
     * @return void
     */
    public function setCountryCode(?string $countryCode);

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string|null $email
     *
     * @return void
     */
    public function setEmail(?string $email);

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @param string|null $phone
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
     * @param array|null $segments
     *
     * @return void
     */
    public function setManualSegments(?array $segments);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]
     */
    public function getCalculatedSegments(): array;

    /**
     * @param array|null $segments
     *
     * @return void
     */
    public function setCalculatedSegments(?array $segments);

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments();

    /**
     * @return string|null
     */
    public function getIdEncoded(): ?string;

    /**
     * @param string|null $idEncoded
     *
     * @return void
     */
    public function setIdEncoded(?string $idEncoded);

    /**
     * Return type bool is deprecated and will be removed in version 4
     *
     * @return Consent|bool|null
     */
    public function getProfilingConsent() /* :?Consent */;

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
