<?php

namespace App\Form;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function __construct(private CategoryRepository $categoryRep)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('parent', EntityType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
