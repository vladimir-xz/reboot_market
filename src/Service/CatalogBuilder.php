<?php

namespace App\Service;

class CatalogBuilder
{
    public function build(array $rawCatalog): array
    {
        $tree = [];
        $mainNode = [];
        foreach ($rawCatalog as $index => $data) {
            $parentId = $data['parent_id'];
            if ($parentId === null) {
                $mainNode[] = $index;
            } else {
                $tree[$parentId][] = $index;
            }
        }

        $buildCatalog = function ($array) use ($rawCatalog, $tree, &$buildCatalog) {
            return array_map(function ($index) use ($rawCatalog, $tree, $buildCatalog) {
                $result = $rawCatalog[$index];
                $result['index'] = $index;

                if (array_key_exists($index, $tree)) {
                    $result['children'] = $buildCatalog($tree[$index]);
                }

                return $result;
            }, $array);
        };

        return $buildCatalog($mainNode);
    }
}
