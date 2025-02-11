<?php

namespace App\Twig\Components;

use App\Service\CatalogBuilder;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
final class Catalog
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public array $catalog;
    public array $children;
    public array $parents;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'c'))]
    public array $activeLastNodes = [];
    public LoggerInterface $logger;

    public function __construct(
        CatalogBuilder $builder,
        CategoryRepository $categoryRepository,
        LoggerInterface $logger
    ) {
        $logger->info('creating new Catalog');
        $rawArr = $categoryRepository->getRawTree();
        $result = $builder->build($rawArr);

        $this->catalog = $result['catalog'];
        $this->children = $result['children'];
        $this->parents = $result['parents'];

        $this->logger = $logger;
    }

    #[LiveListener('redraw')]
    public function defineActiveCatalogs(#[LiveArg] array $newCatalogs = [])
    {
        $this->logger->info('redrawing all tree');
        $this->logger->info(print_r($newCatalogs, true));
        $this->activeLastNodes = $newCatalogs['chosen'] ?? [];
        // $this->logger->info('drawing');
        // $this->logger->info(print_r($this->activeLastNodes, true));
        $activeCategories = [];
        $activeCategories['active'] = [];
        $activeCategories['chosen'] = [];
        $activeCategories['neutral'] = [];
        foreach ($newCatalogs as $status => $value) {
            foreach ($value as $index) {
                if (array_key_exists($index, $activeCategories['active'])) {
                    continue;
                }
                $activeCategories[$status][$index] = $index;
                while (array_key_exists($index, $this->parents)) {
                    $index = $this->parents[$index];
                    if (
                        array_key_exists($index, $activeCategories['active'])
                        || array_key_exists($index, $activeCategories['chosen'])
                        || array_key_exists($index, $activeCategories['neutral'])
                    ) {
                        break;
                    }
                    $activeCategories[$status][$index] = $index;
                }
            }
        }

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

        $lastNodes = $getLastNodes($newId);

        $collection = new ArrayCollection($lastNodes);
        if ($collection->exists(fn($key, $value) => in_array($value, $this->activeLastNodes))) {
            $result = array_diff($this->activeLastNodes, $lastNodes);
        } else {
            $result = array_merge($lastNodes, $this->activeLastNodes);
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }
}
