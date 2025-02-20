<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

class CatalogBuilder
{
    public function __construct(private LoggerInterface $loggerInterface)
    {
    }
    public function build(array $rawCatalog): array
    {
        $treeOfChildren = [];
        $treeOfParents = [];
        $mainNode = [];
        foreach ($rawCatalog as $index => $data) {
            $parentId = $data['parent_id'];
            if ($parentId === null) {
                $mainNode[$index] = $index;
            } else {
                $treeOfChildren[$parentId][] = $index;
                $treeOfParents[$index] = $parentId;
            }
        }

        $lastNodesAndParents = $this->mapLastNodesAndParents($mainNode, $treeOfChildren);

        $buildCatalog = function ($array) use ($rawCatalog, $treeOfChildren, &$buildCatalog) {
            return array_map(function ($index) use ($rawCatalog, $treeOfChildren, $buildCatalog) {
                $result = $rawCatalog[$index];
                $result['id'] = $index;

                if (array_key_exists($index, $treeOfChildren)) {
                    $result['children'] = $buildCatalog($treeOfChildren[$index]);
                }

                return $result;
            }, $array);
        };

        $treeToDisplay = $buildCatalog($mainNode);

        return [
            'lastNodeParents' => $lastNodesAndParents['lastNodeParents'],
            'allChildren' => $lastNodesAndParents['allChildren'],
            'catalog' => $treeToDisplay
        ];
    }

    private function mapLastNodesAndParents(
        array $array,
        array $treeOfChildren,
        array $predecesors = [],
        array $allChildren = [],
    ) {
        $collection = new ArrayCollection($array);
        return $collection->reduce(function ($acc, $index) use ($treeOfChildren, $predecesors, $allChildren) {
            if (empty($allChildren)) {
                $newAllChildren = [];
            } else {
                $newAllChildren = array_map(fn($children) => $children + [$index => $index], $allChildren);
            }

            if (array_key_exists($index, $treeOfChildren)) {
                $newPredecesors = [$index => $index] + $predecesors;

                $result = $this->mapLastNodesAndParents(
                    $treeOfChildren[$index],
                    $treeOfChildren,
                    $newPredecesors,
                    $newAllChildren + [$index => []],
                );

                $acc['allChildren'] = array_replace_recursive($acc['allChildren'], $result['allChildren']);
                $acc['lastNodeParents'] += $result['lastNodeParents'];
            } else {
                $lastNodeParents = [$index => $predecesors];

                $acc['lastNodeParents'] += $lastNodeParents;
                $acc['allChildren'] = array_replace_recursive($acc['allChildren'], $newAllChildren);
            }

            return $acc;
        }, ['lastNodeParents' => [], 'allChildren' => []]);

        // $betweenResults = ['lastNodeParents' => [], 'allChildren' => []];
        // $this->loggerInterface->info('We start');
        // foreach ($array as $index) {
        //     if (!empty($allChildren)) {
        //         $newAllChildren = array_map(fn($children) => $children + [$index => $index], $allChildren);
        //     } else {
        //         $newAllChildren = [];
        //     }

        //     $this->loggerInterface->info('Dealing with index ' . $index);
        //     if (array_key_exists($index, $treeOfChildren)) {
        //         $newPredecesors = [$index => $index] + $predecesors;

        //         // $this->loggerInterface->info(print_r($newPredecesors, true));
        //         $result = $this->mapLastNodesAndParents(
        //             $treeOfChildren[$index],
        //             $treeOfChildren,
        //             $newPredecesors,
        //             $newAllChildren + [$index => []],
        //         );
        //         // $this->loggerInterface->info('We have some results');
        //         // $this->loggerInterface->info(print_r($result, true));

        //         $betweenResults['allChildren'] = array_replace_recursive($betweenResults['allChildren'], $result['allChildren']);
        //         $betweenResults['lastNodeParents'] += $result['lastNodeParents'];
        //         $this->loggerInterface->info('Merged previos results and this computations, looking at allChildren');
        //         $this->loggerInterface->info(print_r($betweenResults['allChildren'], true));
        //     } else {
        //         $lastNodeParents = [$index => $predecesors];


        //         // $this->loggerInterface->info('We arrived at last node, here are all parents of last node');
        //         // $this->loggerInterface->info(print_r($newAllParents, true));

        //         $betweenResults['lastNodeParents'] += $lastNodeParents;
        //         $betweenResults['allChildren'] = array_replace_recursive($betweenResults['allChildren'], $newAllChildren);
        //         $this->loggerInterface->info('We continue to another loop with this allChildren');
        //         $this->loggerInterface->info(print_r($betweenResults['allChildren'], true));
        //     }
        // }

        // return $betweenResults;
    }
}
