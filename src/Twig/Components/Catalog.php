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
    public array $lastNodesChosen = [];
    #[LiveProp(writable: true, url: new UrlMapping(as: 'c'))]
    public array $lastNodesExcluded = [];
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
        $this->lastNodesChosen = $newCatalogs['chosen'] ?? [];
        $this->lastNodesExcluded = $newCatalogs['excluded'] ?? [];
        $alreadyProceededIds = [];
        $this->logger->info(print_r($newCatalogs, true));
        $allParents = $this->parents;
        $buildMapWithStatuses = function ($ids, $status) use (&$alreadyProceededIds, $allParents) {
            if (!$ids) {
                return [];
            }

            $result = [];
            foreach ($ids as $index => $id) {
                if (array_key_exists($id, $alreadyProceededIds)) {
                    continue;
                }
                $result[$id] = ['isLastNode' => true, 'status' => $status];
                $alreadyProceededIds[$id] = $id;
                while (array_key_exists($id, $allParents)) {
                    $id = $this->parents[$id];

                    if (array_key_exists($id, $alreadyProceededIds)) {
                        break;
                    }
                    $alreadyProceededIds[$id] = $id;
                    $result[$id] = ['isLastNode' => false, 'status' => $status];
                }
            }

            return $result;
        };
        $activeCategories = $buildMapWithStatuses($newCatalogs['active'] ?? [], 'active');
        $chosenCategories = $buildMapWithStatuses($newCatalogs['chosen'] ?? [], 'chosen');
        $excludedCategories = $buildMapWithStatuses($newCatalogs['excluded'] ?? [], 'excluded');
        $neutralCategories = $buildMapWithStatuses($newCatalogs['neutral'] ?? [], 'neutral');

        $treeMap = $activeCategories + $excludedCategories + $chosenCategories + $neutralCategories;

        $this->dispatchBrowserEvent('catalog:renew', [
            'treeMap' => $treeMap,
        ]);
    }

    #[LiveAction]
    public function revertCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->getLastNodesOfCategory([$newId]);

        $collection = new ArrayCollection($lastNodes);
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => array_key_exists($key, $this->lastNodesChosen));
        $ifRevertExclude = array_diff($lastNodes, $this->lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
        } elseif ($ifAnyExistInActive) {
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } else {
            $choosenWithoutExcluded = array_diff_key($lastNodes, $this->lastNodesExcluded);
            $result['included'] = $choosenWithoutExcluded + $this->lastNodesChosen;
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }

    #[LiveAction]
    public function excludeCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->getLastNodesOfCategory([$newId]);


        $ifRevertExclude = array_diff($lastNodes, $this->lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
        } else {
            $result['excluded'] = $lastNodes + $this->lastNodesExcluded;
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }

    #[LiveAction]
    public function includeCategories(#[LiveArg] int $newId)
    {
        $lastNodes = $this->getLastNodesOfCategory([$newId]);


        $collection = new ArrayCollection($lastNodes);
        $ifAllExistInActive = !$collection->exists(fn($key, $value) => !array_key_exists($key, $this->lastNodesChosen));

        if ($ifAllExistInActive) {
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } else {
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
            $result['included'] = $this->lastNodesChosen + $lastNodes;
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }

    private function getLastNodesOfCategory(array $ids, array $acc = [])
    {
        foreach ($ids as $id) {
            if (!array_key_exists($id, $this->children)) {
                $acc[$id] = $id;
            } else {
                $acc += $this->getLastNodesOfCategory($this->children[$id], $acc);
            }
        }
        $this->logger->info(print_r($acc, true));
        return $acc;
    }
}
