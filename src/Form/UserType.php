<?php

namespace App\Form;

use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    public function __construct(private LoggerInterface $log)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('formOfAddress', ChoiceType::class, [
                'choices'  => User::PREFIXES,
                'attr' => ['autocomplete' => 'honorific-prefix']
            ])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('company', TextType::class, [
                'attr' => ['autocomplete' => 'organization'],
                'required' => false
            ])
            ->add('vatNumber', TextType::class, ['label' => 'VAT Number', 'required' => false])
        ;

        if ($options['admin_clearance']) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => [
                        'Admin' => 'ROLE_ADMIN',
                        'User' => 'ROLE_USER'
                    ],
                    'multiple' => true,
                ])
                ->add('Edit', SubmitType::class)
            ;
        } else {
            $builder->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['autocomplete' => 'new-password']],
                    'required' => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ])
                ->add('agreeTerms', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                    'constraints' => new IsTrue(['message' => 'You should agree with terms'])
                ])
                ->add('register', SubmitType::class)
            ;
        }

        $builder->add('addresses', CollectionType::class, [
            'entry_type' => AddressType::class,
            'allow_add' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'admin_clearance' => false,
            'validation_groups' => function (FormInterface $form): array {
                $data = $form->getData();

                if ('Company' === $data->getFormOfAddress()) {
                    return ['company', 'Default'];
                }

                return ['person', 'Default'];
            },
        ]);
        $resolver->setAllowedTypes('admin_clearance', 'bool');
    }
}
