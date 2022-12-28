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
 * @method static setPublished(bool $published)
 */
interface CustomerInterface extends ElementInterface
{
    /**
     * @return string
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
     * @return $this
     */
    public function setActive(?bool $active);

    /**
     * @return string|null
     */
    public function getGender(): ?string;

    /**
     * @param string|null $gender
     *
     * @return $this
     */
    public function setGender(?string $gender);

    /**
     * @return string|null
     */
    public function getFirstname(): ?string;

    /**
     * @param string|null $firstname
     *
     * @return $this
     */
    public function setFirstname(?string $firstname);

    /**
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * @param string|null $lastname
     *
     * @return $this
     */
    public function setLastname(?string $lastname);

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     *
     * @return $this
     */
    public function setStreet(?string $street);

    /**
     * @return string|null
     */
    public function getZip(): ?string;

    /**
     * @param string|null $zip
     *
     * @return $this
     */
    public function setZip(?string $zip);

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     *
     * @return $this
     */
    public function setCity(?string $city);

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string;

    /**
     * @param string|null $countryCode
     *
     * @return $this
     */
    public function setCountryCode(?string $countryCode);

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email);

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @param string|null $phone
     *
     * @return $this
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
     * @return $this
     */
    public function setManualSegments(?array $segments);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]
     */
    public function getCalculatedSegments(): array;

    /**
     * @param array|null $segments
     *
     * @return $this
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

    public function getProfilingConsent(): ?Consent;

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
