<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFrameworkBundle\DataValidator;


class EmailValidator implements DataValidatorInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function isValid($data)
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }
}