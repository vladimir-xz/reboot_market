<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'attr' => ['autocomplete' => 'new-password']
            ])
            ->add('repeatPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password']
            ])
            ->add('formOfAddress', TextType::class, [
                'attr' => ['autocomplete' => 'honorific-prefix']
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('company', TextType::class, [
                'attr' => ['autocomplete' => 'organization']
            ])
            ->add('vatNumber', TextType::class)
            ->add('agreeTerms', CheckboxType::class, ['mapped' => false])
            ->add('register', SubmitType::class)
        ;

        $builder->add('addresses', CollectionType::class, [
            'entry_type' => AddressType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
