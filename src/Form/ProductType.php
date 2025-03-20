<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Specification;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SpecificationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function __construct(
        private SpecificationRepository $specificationRep,
        private ProductRepository $productRep,
        private CategoryRepository $categoryRep,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('condition', ChoiceType::class, [
                'choices' => ['Used' => 'used', 'New' => 'new', 'Refurbished' => 'refurbished']
            ])
            ->add('weight', NumberType::class)
            ->add('length', NumberType::class, ['required' => false,])
            ->add('price', NumberType::class)
            ->add('amount', NumberType::class)
            ->add('type', TextType::class)
            ->add('brand', TextType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choices' => $this->categoryRep->getAllWithChildrenAndParents(),
                'choice_label' => function (?Category $category): string {
                    return $category->getName() . (is_null($category->getChildren()[0]) ? '' : '⤵');
                },
                // 'choice_value' => 'id',
                'group_by' => function (?Category $category): string {
                    return $category->getParent()?->getName() ?? 'Main node' . ' children';
                },
            ])
            ->add('specifications', EntityType::class, [
                'class' => Specification::class,
                'choices' => $this->specificationRep->findAll(),
                // 'choice_value' => 'id',
                'choice_label' => 'value',
                'group_by' => 'property',
                'multiple' => true,
                'required' => false,
            ])
            ->add('related', EntityType::class, [
                'class' => Product::class,
                'choices' => $this->productRep->findAll(),
                // 'choice_value' => 'id',
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
            ->add('description', DescriptionType::class, ['required' => false,])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
