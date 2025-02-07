<?php

namespace App\Twig\Components;

use App\Service\CatalogBuilder;
use App\Repository\CategoryRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
final class Catalog
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public array $catalog;
    public array $children;
    public array $parents;
    public array $activeCategories;

    public function __construct(
        CatalogBuilder $builder,
        CategoryRepository $categoryRepository,
    ) {
        $rawArr = $categoryRepository->getRawTree();
        $result = $builder->build($rawArr);

        $this->catalog = $result['catalog'];
        $this->children = $result['children'];
        $this->parents = $result['parents'];
    }

    #[LiveListener('redraw')]
    public function defineActiveCatalogs(#[LiveArg] array $newCatalogs = [])
    {
        $activeCategories = [];
        foreach ($newCatalogs as $index) {
            $activeCategories[$index] = $index;
            while (array_key_exists($index, $this->parents)) {
                $index = $this->parents[$index];
                if (array_key_exists($index, $activeCategories)) {
                    break;
                }
                $activeCategories[$index] = $index;
            }
        }
        $this->activeCategories = $activeCategories;

        $this->dispatchBrowserEvent('catalog:renew', [
            'activeCategories' => $activeCategories,
        ]);
    }

    #[LiveAction]
    public function updateCategories(#[LiveArg] int $newId)
    {
        $children = $this->children;
        $getLastNodes = function ($id) use ($children, &$getLastNodes) {
            if (!array_key_exists($id, $children)) {
                return [$id];
            }

            $result = array_map(function ($id) use ($getLastNodes) {
                return $getLastNodes($id);
            }, $children[$id]);

            return array_merge(...$result);
        };

        $newCatalogs = $getLastNodes($newId);

        $this->emit('search', [
            'newCatalogs' => $newCatalogs,
        ]);
    }
}
