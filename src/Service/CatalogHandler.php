<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use App\Repository\CategoryRepository;

class CatalogHandler
{
    private array $catalog;
    private array $children;
    private array $parents;

    public function __construct(private LoggerInterface $loggerInterface, CategoryRepository $categoryRepository,)
    {
        $rawArr = $categoryRepository->getRawTree();
        // $tree = '{"1":{"parent_id":null,"name":"Servers","id":1,"children":[{"parent_id":1,"name":"Dell","id":6,"children":[{"parent_id":6,"name":"2.5 FormFactor","id":17},{"parent_id":6,"name":"3.5 FormFactor","id":18}]},{"parent_id":1,"name":"HP","id":7,"children":[{"parent_id":7,"name":"2.5 FormFactor","id":19},{"parent_id":7,"name":"3.5 FormFactor","id":20}]}]},"2":{"parent_id":null,"name":"Storage","id":2,"children":[{"parent_id":2,"name":"Dell","id":8},{"parent_id":2,"name":"HP","id":9}]},"3":{"parent_id":null,"name":"Network Equipment","id":3,"children":[{"parent_id":3,"name":"Switches","id":10}]},"4":{"parent_id":null,"name":"Components","id":4,"children":[{"parent_id":4,"name":"RAM","id":11},{"parent_id":4,"name":"CPUs (Processors)","id":12},{"parent_id":4,"name":"Drives","id":13},{"parent_id":4,"name":"Network cards","id":14},{"parent_id":4,"name":"Power supplies","id":15}]},"5":{"parent_id":null,"name":"Others","id":5,"children":[{"parent_id":5,"name":"Racks","id":16}]}}';
        // $parents = '{"6":1,"7":1,"8":2,"9":2,"10":3,"11":4,"12":4,"13":4,"14":4,"15":4,"16":5,"17":6,"18":6,"19":7,"20":7}';
        // $lastCHildren = '{"6":{"17":17,"18":18},"1":{"17":17,"18":18,"19":19,"20":20},"7":{"19":19,"20":20},"2":{"8":8,"9":9},"3":{"10":10},"4":{"11":11,"12":12,"13":13,"14":14,"15":15},"5":{"16":16}}';

        // return [
        //     'catalog' => $tree,
        //     'parents' => $parents,
        //     'lastChildren' => $lastCHildren,
        // ];
        $treeOfChildren = [];
        $treeOfParents = [];
        $mainNode = [];
        foreach ($rawArr as $index => $data) {
            $parentId = $data['parent_id'];
            if ($parentId === null) {
                $mainNode[$index] = $index;
            } else {
                $treeOfChildren[$parentId][] = $index;
                $treeOfParents[$index] = $parentId;
            }
        }

        $lastNodesAndParents = $this->mapLastNodesAndParents($mainNode, $treeOfChildren);

        $buildCatalog = function ($array) use ($rawArr, $treeOfChildren, &$buildCatalog) {
            return array_map(function ($index) use ($rawArr, $treeOfChildren, $buildCatalog) {
                $result = $rawArr[$index];
                $result['id'] = $index;

                if (array_key_exists($index, $treeOfChildren)) {
                    $result['children'] = $buildCatalog($treeOfChildren[$index]);
                }

                return $result;
            }, $array);
        };

        $treeToDisplay = $buildCatalog($mainNode);

        $this->catalog = $treeToDisplay;
        $this->children = $lastNodesAndParents['lastChildren'];
        $this->parents = $treeOfParents;
    }

    private function mapLastNodesAndParents(
        array $array,
        array $treeOfChildren,
        array $predecesors = [],
    ) {
        $collection = new ArrayCollection($array);
        return $collection->reduce(function ($acc, $index) use ($treeOfChildren, $predecesors) {
            if (array_key_exists($index, $treeOfChildren)) {
                $newPredecesors = [$index => $index] + $predecesors;

                $result = $this->mapLastNodesAndParents(
                    $treeOfChildren[$index],
                    $treeOfChildren,
                    $newPredecesors,
                );

                $acc['lastChildren'] = array_replace_recursive($acc['lastChildren'], $result['lastChildren']);
                $acc['lastNodeParents'] += $result['lastNodeParents'];
            } else {
                $lastNodeParents = [$index => $predecesors];
                $newLastChildren = array_map(fn($children) => [$index => $index], $predecesors);

                $acc['lastNodeParents'] += $lastNodeParents;
                $acc['lastChildren'] = array_replace_recursive($acc['lastChildren'], $newLastChildren);
            }

            return $acc;
        }, ['lastNodeParents' => [], 'lastChildren' => []]);
    }

    public function buildMapWithStatusesFromLastNodes(array $ids, string $status)
    {
        if (!$ids) {
            return [];
        }

        $alreadyProceededIds = [];
        $result = [];
        foreach ($ids as $index => $id) {
            if (array_key_exists($id, $alreadyProceededIds)) {
                continue;
            }

            $result[$id] = ['isLastNode' => true, 'status' => $status];
            $alreadyProceededIds[$id] = $id;
            while (array_key_exists($id, $this->parents)) {
                $id = $this->parents[$id];

                if (array_key_exists($id, $alreadyProceededIds)) {
                    break;
                }
                $alreadyProceededIds[$id] = $id;
                $result[$id] = ['isLastNode' => false, 'status' => $status];
            }
        }

        return $result;
    }

    public function revertCategories(int $newId, array $lastNodesChosen, array $lastNodesExcluded)
    {
        $lastNodes = $this->children[$newId];

        $collection = new ArrayCollection($lastNodes);
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => array_key_exists($key, $lastNodesChosen));
        $ifRevertExclude = array_diff_key($lastNodes, $lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['included'] = $lastNodesChosen;
        } elseif ($ifAnyExistInActive) {
            $result['included'] = array_diff_key($lastNodesChosen, $lastNodes);
            $result['excluded'] = $lastNodesExcluded;
        } else {
            $choosenWithoutExcluded = array_diff_key($lastNodes, $lastNodesExcluded);
            $result['included'] = $choosenWithoutExcluded + $lastNodesChosen;
            $result['excluded'] = $lastNodesExcluded;
        }

        return $result;
    }

    public function excludeCategories(int $newId, array $lastNodesChosen, array $lastNodesExcluded)
    {
        $lastNodes = $this->children[$newId];


        $ifRevertExclude = array_diff_key($lastNodes, $lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['excluded'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['included'] = $lastNodesChosen;
        } else {
            $result['excluded'] = $lastNodes + $lastNodesExcluded;
            $result['included'] = array_diff_key($lastNodesChosen, $lastNodes);
        }

        return $result;
    }

    public function includeCategories(int $newId, array $lastNodesChosen, array $lastNodesExcluded)
    {
        $lastNodes = $this->children[$newId];


        $collection = new ArrayCollection($lastNodes);
        $ifAllExistInActive = !$collection->exists(fn($key, $value) => !array_key_exists($key, $lastNodesChosen));

        if ($ifAllExistInActive) {
            $result['included'] = array_diff_key($lastNodesChosen, $lastNodes);
            $result['excluded'] = $lastNodesExcluded;
        } else {
            $result['excluded'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['included'] = $lastNodesChosen + $lastNodes;
        }

        return $result;
    }

    public function prepareNewCatalogsForDrawing(array $newCatalogs)
    {
        $activeCategories = $this->buildMapWithStatusesFromLastNodes($newCatalogs['active'] ?? [], 'active');
        $includedCategories = $this->buildMapWithStatusesFromLastNodes($newCatalogs['included'] ?? [], 'included');
        $excludedCategories = $this->buildMapWithStatusesFromLastNodes($newCatalogs['excluded'] ?? [], 'excluded');
        $neutralCategories = $this->buildMapWithStatusesFromLastNodes($newCatalogs['neutral'] ?? [], 'neutral');
        // $activeCollection = new ArrayCollection($newCatalogs['active'] ?? []);
        // $activeCategories = $activeCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

        // $chosenCollection = new ArrayCollection($newCatalogs['chosen'] ?? []);
        // $chosenWithActive = $chosenCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
        // $chosenCategories = array_diff_key($chosenWithActive, $activeCategories);

        // $excludedCollection = new ArrayCollection($newCatalogs['excluded'] ?? []);
        // $excludedWithActive = $excludedCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);
        // $excludedCategories = array_diff_key($excludedWithActive, $activeCategories, $chosenCategories);

        // $neutralCollection = new ArrayCollection($newCatalogs['neutral'] ?? []);
        // $neutralCategories = $neutralCollection->reduce(fn($acc, $index) => $acc + $this->parents[$index], []);

        return $activeCategories + $excludedCategories + $includedCategories + $neutralCategories;
    }

    public function getAllChildrenOfNode(int $id)
    {
        return $this->children[$id];
    }

    public function getCatalog()
    {
        return $this->catalog;
    }
}
