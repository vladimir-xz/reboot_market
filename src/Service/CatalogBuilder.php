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
        $tree = '{"1":{"parent_id":null,"name":"Servers","id":1,"children":[{"parent_id":1,"name":"Dell","id":6,"children":[{"parent_id":6,"name":"2.5 FormFactor","id":17},{"parent_id":6,"name":"3.5 FormFactor","id":18}]},{"parent_id":1,"name":"HP","id":7,"children":[{"parent_id":7,"name":"2.5 FormFactor","id":19},{"parent_id":7,"name":"3.5 FormFactor","id":20}]}]},"2":{"parent_id":null,"name":"Storage","id":2,"children":[{"parent_id":2,"name":"Dell","id":8},{"parent_id":2,"name":"HP","id":9}]},"3":{"parent_id":null,"name":"Network Equipment","id":3,"children":[{"parent_id":3,"name":"Switches","id":10}]},"4":{"parent_id":null,"name":"Components","id":4,"children":[{"parent_id":4,"name":"RAM","id":11},{"parent_id":4,"name":"CPUs (Processors)","id":12},{"parent_id":4,"name":"Drives","id":13},{"parent_id":4,"name":"Network cards","id":14},{"parent_id":4,"name":"Power supplies","id":15}]},"5":{"parent_id":null,"name":"Others","id":5,"children":[{"parent_id":5,"name":"Racks","id":16}]}}';
        $parents = '{"6":1,"7":1,"8":2,"9":2,"10":3,"11":4,"12":4,"13":4,"14":4,"15":4,"16":5,"17":6,"18":6,"19":7,"20":7}';
        $lastCHildren = '{"6":{"17":17,"18":18},"1":{"17":17,"18":18,"19":19,"20":20},"7":{"19":19,"20":20},"2":{"8":8,"9":9},"3":{"10":10},"4":{"11":11,"12":12,"13":13,"14":14,"15":15},"5":{"16":16}}';

        return [
            'catalog' => $tree,
            'parents' => $parents,
            'lastChildren' => $lastCHildren,
        ];
        // $treeOfChildren = [];
        // $treeOfParents = [];
        // $mainNode = [];
        // foreach ($rawCatalog as $index => $data) {
        //     $parentId = $data['parent_id'];
        //     if ($parentId === null) {
        //         $mainNode[$index] = $index;
        //     } else {
        //         $treeOfChildren[$parentId][] = $index;
        //         $treeOfParents[$index] = $parentId;
        //     }
        // }

        // $lastNodesAndParents = $this->mapLastNodesAndParents($mainNode, $treeOfChildren);

        // $buildCatalog = function ($array) use ($rawCatalog, $treeOfChildren, &$buildCatalog) {
        //     return array_map(function ($index) use ($rawCatalog, $treeOfChildren, $buildCatalog) {
        //         $result = $rawCatalog[$index];
        //         $result['id'] = $index;

        //         if (array_key_exists($index, $treeOfChildren)) {
        //             $result['children'] = $buildCatalog($treeOfChildren[$index]);
        //         }

        //         return $result;
        //     }, $array);
        // };

        // $treeToDisplay = $buildCatalog($mainNode);

        // return [
        //     'parents' => $treeOfParents,
        //     'lastChildren' => $lastNodesAndParents['lastChildren'],
        //     'catalog' => $treeToDisplay
        // ];
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
