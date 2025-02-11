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
    public array $choosenNodes = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'c'))]
    public array $excludedNodes = [];
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
        $this->choosenNodes = $newCatalogs['chosen'] ?? [];
        $this->excludedNodes = $newCatalogs['excluded'] ?? [];
        $alreadyProceededIds = [];
        $allParents = $this->parents;
        $buildTree = function ($ids) use (&$alreadyProceededIds, $allParents) {
            if (!$ids) {
                return [];
            }

            $result = [];
            foreach ($ids as $index) {
                if (array_key_exists($index, $alreadyProceededIds)) {
                    continue;
                }
                $result[$index] = $index;
                $alreadyProceededIds[$index] = $index;
                while (array_key_exists($index, $allParents)) {
                    $index = $this->parents[$index];
                    if (array_key_exists($index, $alreadyProceededIds)) {
                        break;
                    }
                    $result[$index] = $index;
                }
            }

            return $result;
        };
        $activeCategories = [];
        $activeCategories['active'] = $buildTree($newCatalogs['active'] ?? []);
        $activeCategories['excluded'] = $buildTree($newCatalogs['excluded'] ?? []);
        $activeCategories['chosen'] = $buildTree($newCatalogs['chosen'] ?? []);
        $activeCategories['neutral'] = $buildTree($newCatalogs['neutral'] ?? []);

        $this->dispatchBrowserEvent('catalog:renew', [
            'activeCategories' => $activeCategories,
        ]);
    }

    #[LiveAction]
    public function updateCategories(#[LiveArg] int $newId, #[LiveArg] bool $ifExclude = false)
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
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => in_array($value, $this->choosenNodes));
        $ifRevertExclude = array_diff($lastNodes, $this->excludedNodes) === [];

        if ($ifRevertExclude) {
            $this->logger->info('What im doing wrong?');
            $this->logger->info('Rever exclude');
            $result['excluded'] = array_diff($this->excludedNodes, $lastNodes);
        } elseif ($ifExclude) {
            $this->logger->info('Maybe this?');
            $this->logger->info('adding new exluding to array');
            $result['excluded'] = array_unique(array_merge($lastNodes, $this->excludedNodes));
            $result['included'] = array_diff($this->choosenNodes, $lastNodes);
        } elseif ($ifAnyExistInActive) {
            $this->logger->info('Deleting new');
            $result['included'] = array_diff($this->choosenNodes, $lastNodes);
        } else {
            $this->logger->info('Adding new without old');
            $choosenWithoutExcluded = array_diff($lastNodes, $this->excludedNodes);
            $result['included'] = array_merge($choosenWithoutExcluded, $this->choosenNodes);
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }
}
