<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 10:57
 */

namespace CustomerManagementFrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client_id',TextType::class)
            ->add('client_secret', TextType::class)
            ->add('_submit', SubmitType::class, [
                'label' => 'Login'
            ])->setMethod("POST")
        ;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        // we need to set this to an empty string as we want _username as input name
        // instead of login_form[_username] to work with the form authenticator out
        // of the box
        return '';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

}