<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\DataValidator;


interface DataValidatorInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function isValid($data);
}