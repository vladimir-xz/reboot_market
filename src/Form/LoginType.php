<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $log
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userRepository = $this->userRepository;
        $passwordHasher = $this->passwordHasher;
        $builder
            ->add('email', EmailType::class, [
                'setter' => function (User &$user, ?string $email, FormInterface $form) use ($userRepository): void {
                    if ($email) {
                        $desiredUser = $userRepository->findOneBy(['email' => $email]);
                        $user = $desiredUser;
                    }
                },
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'always_empty' => false,
                // 'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Callback(['callback' => function ($value, ExecutionContextInterface $ec) use ($passwordHasher) {
                        $user = $ec->getRoot()->getData() ?? new User();
                        if (!$value || !$passwordHasher->isPasswordValid($user, $value)) {
                            $ec->addViolation('Wrong username or password');
                        }
                    }])
                ],
            ])
            ->add('remember_me', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // enable/disable CSRF protection for this form
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'user_login',
        ]);
    }
}
