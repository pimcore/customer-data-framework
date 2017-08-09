<?php

declare(strict_types=1);

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterFormType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-Mail'
            ])
            ->add('firstname', TextType::class, [
                'label' => 'First Name'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('provider', HiddenType::class)
            ->add('_submit', SubmitType::class, [
                'label' => 'Register'
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
