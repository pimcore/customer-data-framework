<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:13
 */

namespace CustomerManagementFramework\Model;

interface CustomerSegmentInterface {

    public function getId();
    public function getName();
    public function setName($name);
    public function getReference();
    public function setReference($reference);
    public function getGroup();
    public function setGroup($group);
}