<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 11/07/2017
 * Time: 16:34
 */

namespace CustomerManagementFrameworkBundle\Authentication\UserPasswordEncoder;

use Pimcore\Model\Object\ClassDefinition;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordFieldPasswordEncoder implements PasswordEncoderInterface
{

    /**
     * Encodes the raw password.
     *
     * @param string $raw The password to encode
     * @param string $salt The salt
     *
     * @return string The encoded password
     */
    public function encodePassword($raw, $salt)
    {
        return $raw;
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw A raw password
     * @param string $salt The salt
     *
     * @return bool true if the password is valid, false otherwise
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {

        $class = ClassDefinition::getById(\Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassId());


        if ($passwordField = $class->getFieldDefinition('password')) {
            $customer = \Pimcore::getContainer()->get('cmf.customer_provider')->create(["password" => $encoded]);

            return $passwordField->verifyPassword($raw, $customer, true);
        }

        throw new \Exception("no password field added in customer class");
    }

}