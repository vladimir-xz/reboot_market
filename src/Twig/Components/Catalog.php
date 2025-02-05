<?php

namespace App\Twig\Components;

use App\Service\CatalogBuilder;
use App\Repository\CategoryRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Catalog
{
    use DefaultActionTrait;

    public array $catalog;

    public function __construct(
        CatalogBuilder $builder,
        CategoryRepository $categoryRepository,
    ) {
        $rawArr = $categoryRepository->getRawTree();
        $this->catalog = $builder->build($rawArr);
    }
}
