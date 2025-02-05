<?php

namespace App\Service;

class CatalogBuilder
{
    public function build(array $rawCatalog): array
    {
        $treeForChildren = [];
        $treeForParents = [];
        $mainNode = [];
        foreach ($rawCatalog as $index => $data) {
            $parentId = $data['parent_id'];
            if ($parentId === null) {
                $mainNode[] = $index;
            } else {
                $treeForChildren[$parentId][] = $index;
                $treeForParents[$index] = $parentId;
            }
        }

        $buildCatalog = function ($array) use ($rawCatalog, $treeForChildren, &$buildCatalog) {
            return array_map(function ($index) use ($rawCatalog, $treeForChildren, $buildCatalog) {
                $result = $rawCatalog[$index];
                $result['id'] = $index;

                if (array_key_exists($index, $treeForChildren)) {
                    $result['children'] = $buildCatalog($treeForChildren[$index]);
                }

                return $result;
            }, $array);
        };

        return $buildCatalog($mainNode);
    }
}
