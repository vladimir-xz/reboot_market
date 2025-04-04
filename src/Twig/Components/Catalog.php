<?php

namespace App\Twig\Components;

use App\Service\CatalogHandler;
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

    // public function __construct(
    //     CatalogBuilder $builder,
    //     CategoryRepository $categoryRepository,
    //     LoggerInterface $logger
    // ) {
    //     $rawArr = $categoryRepository->getRawTree();
    //     $result = $builder->build($rawArr);

    //     $this->catalog = $result['catalog'];
    //     $this->children = $result['lastChildren'];
    //     $this->parents = $result['parents'];

    //     // $this->children = json_decode($result['lastChildren'], true);
    //     // $this->parents = json_decode($result['parents'], true);

    //     $this->logger = $logger;
    // }

    // #[LiveListener('redraw')]
    // public function defineActiveCatalogs(#[LiveArg] array $newCatalogs = [])
    // {
    //     $alreadyProceededIds = [];
    //     $this->lastNodesChosen = $newCatalogs['included'] ?? [];
    //     $this->lastNodesExcluded = $newCatalogs['excluded'] ?? [];
    //     $allParents = $this->parents;
    //     $buildMapWithStatuses = function ($ids, $status) use (&$alreadyProceededIds, $allParents) {
    //         if (!$ids) {
    //             return [];
    //         }

    //         $result = [];
    //         foreach ($ids as $index => $id) {
    //             if (array_key_exists($id, $alreadyProceededIds)) {
    //                 continue;
    //             }
    //             $result[$id] = ['isLastNode' => true, 'status' => $status];
    //             $alreadyProceededIds[$id] = $id;
    //             while (array_key_exists($id, $allParents)) {
    //                 $id = $this->parents[$id];

    //                 if (array_key_exists($id, $alreadyProceededIds)) {
    //                     break;
    //                 }
    //                 $alreadyProceededIds[$id] = $id;
    //                 $result[$id] = ['isLastNode' => false, 'status' => $status];
    //             }
    //         }

    //         return $result;
    //     };
    //     $activeCategories = $buildMapWithStatuses($newCatalogs['active'] ?? [], 'active');
    //     $includedCategories = $buildMapWithStatuses($newCatalogs['included'] ?? [], 'included');
    //     $excludedCategories = $buildMapWithStatuses($newCatalogs['excluded'] ?? [], 'excluded');
    //     $neutralCategories = $buildMapWithStatuses($newCatalogs['neutral'] ?? [], 'neutral');
    //     // $activeCollection = new ArrayCollection($newCatalogs['active'] ?? []);
    //     // $activeCategories = $activeCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

    //     // $chosenCollection = new ArrayCollection($newCatalogs['chosen'] ?? []);
    //     // $chosenWithActive = $chosenCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
    //     // $chosenCategories = array_diff_key($chosenWithActive, $activeCategories);

    //     // $excludedCollection = new ArrayCollection($newCatalogs['excluded'] ?? []);
    //     // $excludedWithActive = $excludedCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
    //     // $excludedCategories = array_diff_key($excludedWithActive, $activeCategories, $chosenCategories);

    //     // $neutralCollection = new ArrayCollection($newCatalogs['neutral'] ?? []);
    //     // $neutralCategories = $neutralCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

    //     $treeMap = $activeCategories + $excludedCategories + $includedCategories + $neutralCategories;

    //     $this->dispatchBrowserEvent('catalog:renew', [
    //         'treeMap' => $treeMap,
    //     ]);
    // }

    // #[LiveAction]
    // public function revertCategories(#[LiveArg] int $newId)
    // {
    //     $lastNodes = $this->children[$newId];

    //     $collection = new ArrayCollection($lastNodes);
    //     $ifAnyExistInActive = $collection->exists(fn($key, $value) => array_key_exists($key, $this->lastNodesChosen));
    //     $ifRevertExclude = array_diff_key($lastNodes, $this->lastNodesExcluded) === [];

    //     if ($ifRevertExclude) {
    //         $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
    //     } elseif ($ifAnyExistInActive) {
    //         $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
    //     } else {
    //         $choosenWithoutExcluded = array_diff_key($lastNodes, $this->lastNodesExcluded);
    //         $result['included'] = $choosenWithoutExcluded + $this->lastNodesChosen;
    //     }

    //     $this->updateLastNodesAndSendResults($result);
    // }

    // #[LiveAction]
    // public function excludeCategories(#[LiveArg] int $newId)
    // {
    //     $lastNodes = $this->children[$newId];


    //     $ifRevertExclude = array_diff_key($lastNodes, $this->lastNodesExcluded) === [];

    //     if ($ifRevertExclude) {
    //         $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
    //     } else {
    //         $result['excluded'] = $lastNodes + $this->lastNodesExcluded;
    //         $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
    //     }

    //     $this->updateLastNodesAndSendResults($result);
    // }

    // #[LiveAction]
    // public function includeCategories(#[LiveArg] int $newId)
    // {
    //     $lastNodes = $this->children[$newId];


    //     $collection = new ArrayCollection($lastNodes);
    //     $ifAllExistInActive = !$collection->exists(fn($key, $value) => !array_key_exists($key, $this->lastNodesChosen));

    //     if ($ifAllExistInActive) {
    //         $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
    //     } else {
    //         $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
    //         $result['included'] = $this->lastNodesChosen + $lastNodes;
    //     }

    //     $this->updateLastNodesAndSendResults($result);
    // }

    // // private function getLastNodesOfCategory(array $ids, array $acc = [])
    // // {
    // //     foreach ($ids as $id) {
    // //         if (!array_key_exists($id, $this->children)) {
    // //             $acc[$id] = $id;
    // //         } else {
    // //             $acc += $this->getLastNodesOfCategory($this->children[$id], $acc);
    // //         }
    // //     }

    // //     return $acc;
    // // }

    // private function updateLastNodesAndSendResults(array $result)
    // {
    //     if (array_key_exists('included', $result)) {
    //         $this->lastNodesChosen = $result['included'];
    //     }
    //     if (array_key_exists('excluded', $result)) {
    //         $this->lastNodesExcluded = $result['excluded'];
    //     }

    //     // $this->dispatchBrowserEvent('catalog:loadProducts');

    //     $this->emit('receiveCategories', [
    //         'newCategories' => $result,
    //     ]);
    // }
}
