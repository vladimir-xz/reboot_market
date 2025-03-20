<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isMain', CheckboxType::class, ['label' => 'Use as main image', 'required' => false,])
            ->add('uploadImages', FileType::class, [
                'label' => 'Image (JPG or PNG file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                'required' => false,

                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/svg+xml'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
            ->add('Delete', SubmitType::class, ['attr' => ['data-action' => 'form-collection#remove']])
        ;

        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
        //     $image = $event->getData();
        //     $form = $event->getForm();

        //     if (!is_null($image)) {
        //         $package = new Package(new EmptyVersionStrategy());
        //         $path = $image->getPath();
        //         $form->remove('uploadImages');
        //         $form->add('uploadImages', FileType::class, [
        //             'label' => "<img src='{$package->getUrl($path)}' alt='image'>",
        //             'label_html' => true,

        //             // unmapped means that this field is not associated to any entity property
        //             'mapped' => false,

        //             'required' => false,

        //             'constraints' => [
        //                 new File([
        //                     'maxSize' => '1024k',
        //                     'mimeTypes' => [
        //                         'image/jpeg',
        //                         'image/png',
        //                         'image/svg+xml'
        //                     ],
        //                     'mimeTypesMessage' => 'Please upload a valid image',
        //                 ])
        //             ],
        //         ]);
        //     }
        // });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
