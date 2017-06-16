<?php

namespace Website\Auth;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * Helper for building and reading/writing the registration form
 */
class RegistrationFormHandler
{
    /**
     * @return \Zend_Form
     */
    public function buildRegistrationForm(CustomerInterface $customer = null)
    {
        $email = new \Zend_Form_Element_Text('email');
        $email
            ->setLabel('E-Mail')
            ->setAttrib('type', 'email')
            ->setRequired(true)
            ->addValidators([
                new \Zend_Validate_EmailAddress()
            ]);

        $firstname = new \Zend_Form_Element_Text('firstname');
        $firstname
            ->setLabel('First Name')
            ->setRequired(true);

        $lastname = new \Zend_Form_Element_Text('lastname');
        $lastname
            ->setLabel('Last Name')
            ->setRequired(true);

        $form = new \Zend_Form();
        $form->addElements([$email, $firstname, $lastname]);

        if ($customer) {
            $this->mapCustomerValuesToForm($customer, $form);
        }

        return $form;
    }

    /**
     * Pre-fill form with customer values
     *
     * @param CustomerInterface $customer
     * @param \Zend_Form $form
     */
    public function mapCustomerValuesToForm(CustomerInterface $customer, \Zend_Form $form)
    {
        /**
         * @var string $name
         * @var \Zend_Form_Element $element
         */
        foreach ($form->getElements() as $name => $element) {
            $getter = 'get' . ucfirst($name);
            if (!method_exists($customer, $getter)) {
                continue;
            }

            $value = $customer->$getter();
            if (!$value) {
                continue;
            }

            $element->setValue($value);
        }
    }

    /**
     * Map form values to customer
     *
     * @param array $values
     * @param CustomerInterface $customer
     */
    public function mapFormValuesToCustomer(array $values, CustomerInterface $customer)
    {
        foreach ($values as $name => $value) {
            if (!$value) {
                continue;
            }

            $setter = 'set' . ucfirst($name);
            if (!method_exists($customer, $setter)) {
                continue;
            }

            $customer->$setter($value);
        }
    }
}
