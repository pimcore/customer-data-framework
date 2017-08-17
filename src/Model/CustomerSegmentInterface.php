<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:13
 */

namespace CustomerManagementFrameworkBundle\Model;

use Pimcore\Model\Object\CustomerSegmentGroup;

interface CustomerSegmentInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     * @return string
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getReference();

    /**
     * @param string $reference
     * @return void
     */
    public function setReference($reference);

    /**
     * @return CustomerSegmentGroup
     */
    public function getGroup();

    /**
     * @param CustomerSegmentGroup $group
     * @return void
     */
    public function setGroup($group);

    /**
     * @return bool
     */
    public function getCalculated();

    /**
     * @param bool $calculated
     * @return void
     */
    public function setCalculated($calculated);

    /**
     * @return []
     */
    public function getDataForWebserviceExport();

    /**
     * @return void
     */
    public function save();

    /**
     * @return void
     */
    public function delete();
}