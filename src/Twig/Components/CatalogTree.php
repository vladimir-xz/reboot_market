<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Service\CatalogBuilder;
use App\Repository\CategoryRepository;
use Psr\Log\LoggerInterface;

#[AsTwigComponent]
final class CatalogTree
{
    public array $catalog;

    public function __construct(
        CatalogBuilder $builder,
        CategoryRepository $categoryRepository,
        LoggerInterface $logger
    ) {
        $rawArr = $categoryRepository->getRawTree();
        $result = $builder->build($rawArr);
        $logger->alert('Are we building this again?');

        $this->catalog = $result['catalog'];
    }
}
