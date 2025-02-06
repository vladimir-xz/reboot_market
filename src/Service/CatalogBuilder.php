<?php

namespace App\Service;

class CatalogBuilder
{
    public function build(array $rawCatalog): array
    {
        $treeOfChildren = [];
        $treeOfParents = [];
        $mainNode = [];
        foreach ($rawCatalog as $index => $data) {
            $parentId = $data['parent_id'];
            if ($parentId === null) {
                $mainNode[] = $index;
            } else {
                $treeOfChildren[$parentId][] = $index;
                $treeOfParents[$index] = $parentId;
            }
        }

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
            'children' => $treeOfChildren,
            'parents' => $treeOfParents,
            'catalog' => $treeToDisplay
        ];
    }
}
