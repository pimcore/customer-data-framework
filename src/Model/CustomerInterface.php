<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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

    public function getActive(): ?bool;

    /**
     *
     * @return $this
     */
    public function setActive(?bool $active);

    public function getGender(): ?string;

    /**
     *
     * @return $this
     */
    public function setGender(?string $gender);

    public function getFirstname(): ?string;

    /**
     *
     * @return $this
     */
    public function setFirstname(?string $firstname);

    public function getLastname(): ?string;

    /**
     *
     * @return $this
     */
    public function setLastname(?string $lastname);

    public function getStreet(): ?string;

    /**
     *
     * @return $this
     */
    public function setStreet(?string $street);

    public function getZip(): ?string;

    /**
     *
     * @return $this
     */
    public function setZip(?string $zip);

    public function getCity(): ?string;

    /**
     *
     * @return $this
     */
    public function setCity(?string $city);

    public function getCountryCode(): ?string;

    /**
     *
     * @return $this
     */
    public function setCountryCode(?string $countryCode);

    public function getEmail(): ?string;

    /**
     *
     * @return $this
     */
    public function setEmail(?string $email);

    public function getPhone(): ?string;

    /**
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
     *
     * @return $this
     */
    public function setManualSegments(?array $segments);

    /**
     * @return CustomerSegmentInterface[]|ObjectMetadata[]
     */
    public function getCalculatedSegments(): array;

    /**
     *
     * @return $this
     */
    public function setCalculatedSegments(?array $segments);

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments();

    public function getIdEncoded(): ?string;

    /**
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
