<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use App\Service\CatalogHandler;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\CategoryRepository;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
final class CatalogTree
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public array $catalog;
    #[LiveProp]
    public array $treeMap = [];

    public function __construct(
        LoggerInterface $log,
        CatalogHandler $builder,
    ) {
        $this->catalog = $builder->getCatalog();

        // $this->catalog = json_decode($result['catalog'], true);
    }
}
