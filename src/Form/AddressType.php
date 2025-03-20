<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Country;
use App\Repository\CountryRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function __construct(private CountryRepository $countryRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstLine', TextType::class, [
                'label' => 'Street',
                'attr' => ['autocomplete' => 'address-line1'],
                'help' => 'Street address, company name',
            ])
            ->add('secondLine', TextType::class, [
                'label' => 'House No.',
                'help' => 'Building, unit, suite, unit, floor',
            ])
            ->add('town', TextType::class)
            ->add('postcode', TextType::class)
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choices' => $this->countryRepository->findAll(),
                'choice_value' => 'id',
                'choice_label' => function (?Country $country): string {
                    return $country ? ucfirst($country->getName()) : '';
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
