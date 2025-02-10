<?php

namespace App\Twig\Components;

use App\Service\CatalogBuilder;
use App\Repository\CategoryRepository;
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
        $this->activeLastNodes = $newCatalogs;
        // $this->logger->info('drawing');
        // $this->logger->info(print_r($this->activeLastNodes, true));
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

        $this->dispatchBrowserEvent('catalog:renew', [
            'activeCategories' => $activeCategories,
        ]);
    }

    #[LiveAction]
    public function updateCategories(#[LiveArg] int $newId)
    {
        // $this->logger->info('start');
        // $this->logger->info(print_r($this->activeLastNodes, true));
        // $log = $this->logger;
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
        // $this->logger->info($newId);

        $lastNodes = $getLastNodes($newId);

        // $this->logger->info($lastNodes[0]);
        // $this->logger->info(print_r($this->activeLastNodes, true));


        if (in_array($lastNodes[0], $this->activeLastNodes)) {
            // $this->logger->info('deleting');
            // $this->logger->info('previous');
            // $this->logger->info(print_r($this->activeLastNodes, true));
            $result = array_diff($this->activeLastNodes, $lastNodes);
            // $this->logger->info('now');
            // $this->logger->info(print_r($result, true));
        } else {
            // $this->logger->info('adding');
            // $this->logger->info('previous');
            // $this->logger->info(print_r($this->activeLastNodes, true));
            $result = array_merge($lastNodes, $this->activeLastNodes);
            // $this->logger->info('now');
            // $this->logger->info(print_r($result, true));
        }

        $this->emit('search', [
            'newCatalogs' => $result,
        ]);
    }
}
