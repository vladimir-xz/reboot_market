<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use App\Repository\CategoryRepository;
use App\Service\MapAllRecords;

class CatalogHandler
{
    private array $catalog;
    private array $children;
    private array $parents;

    public function __construct(private LoggerInterface $loggerInterface, private MapAllRecords $mapAllRecords, CategoryRepository $categoryRepository,)
    {
        $rawArr = $categoryRepository->getRawTree();
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
        $lastNodes = $this->children[$newId] ?? [$newId => $newId];

        $collection = new ArrayCollection($lastNodes);
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => array_key_exists($key, $lastNodesChosen));
        $ifRevertExclude = array_diff_key($lastNodes, $lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['e'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['i'] = $lastNodesChosen;
        } elseif ($ifAnyExistInActive) {
            $result['i'] = array_diff_key($lastNodesChosen, $lastNodes);
            $result['e'] = $lastNodesExcluded;
        } else {
            $choosenWithoutExcluded = array_diff_key($lastNodes, $lastNodesExcluded);
            $result['i'] = $choosenWithoutExcluded + $lastNodesChosen;
            $result['e'] = $lastNodesExcluded;
        }

        return $result;
    }

    public function excludeCategories(int $newId, array $lastNodesChosen, array $lastNodesExcluded)
    {
        $lastNodes = $this->children[$newId] ?? [$newId => $newId];


        $ifRevertExclude = array_diff_key($lastNodes, $lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $result['e'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['i'] = $lastNodesChosen;
        } else {
            $result['e'] = $lastNodes + $lastNodesExcluded;
            $result['i'] = array_diff_key($lastNodesChosen, $lastNodes);
        }

        return $result;
    }

    public function includeCategories(int $newId, array $lastNodesChosen, array $lastNodesExcluded)
    {
        $lastNodes = $this->children[$newId] ?? [$newId => $newId];


        $collection = new ArrayCollection($lastNodes);
        $ifAllExistInActive = !$collection->exists(fn($key, $value) => !array_key_exists($key, $lastNodesChosen));

        if ($ifAllExistInActive) {
            // included categories
            $result['i'] = array_diff_key($lastNodesChosen, $lastNodes);
            // excluded categories
            $result['e'] = $lastNodesExcluded;
        } else {
            $result['e'] = array_diff_key($lastNodesExcluded, $lastNodes);
            $result['i'] = $lastNodesChosen + $lastNodes;
        }

        return $result;
    }

    public function prepareNewCatalogsForDrawing(array $allRecords, array $includedCategories, array $excludedCategories)
    {
        $mappedRecords = $this->mapAllRecords->mapRecords($allRecords, true);

        if ($includedCategories) {
            $onlyIncluded = array_diff_key($includedCategories, $mappedRecords['categories']);
            $activeCategories = $this->buildMapWithStatusesFromLastNodes($mappedRecords['categories'], 'active');
            $includedCategories = $this->buildMapWithStatusesFromLastNodes($onlyIncluded, 'included');
            $excludedCategories = $this->buildMapWithStatusesFromLastNodes($excludedCategories, 'excluded');

            $mappedCatalogRecords = $activeCategories + $excludedCategories + $includedCategories;
        } else {
            // neutral
            $neutralCategories = $this->buildMapWithStatusesFromLastNodes($mappedRecords['categories'], 'neutral');
            $excludedCategories = $this->buildMapWithStatusesFromLastNodes($excludedCategories, 'excluded');

            $mappedCatalogRecords = $neutralCategories + $excludedCategories;
        }

        return ['mappedCatalogs' => $mappedCatalogRecords, 'mappedRecords' => $mappedRecords];
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
