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
        $allParents = $this->parents;
        $buildMapWithStatuses = function ($ids, $status) use (&$alreadyProceededIds, $allParents) {
            if (!$ids) {
                return [];
            }

            $result = [];
            foreach ($ids as $index => $value) {
                if (array_key_exists($index, $alreadyProceededIds)) {
                    continue;
                }
                $result[$index] = ['isLastNode' => true, 'status' => $status];
                $alreadyProceededIds[$index] = $index;
                while (array_key_exists($index, $allParents)) {
                    $index = $this->parents[$index];

                    if (array_key_exists($index, $alreadyProceededIds)) {
                        break;
                    }
                    $result[$index] = ['isLastNode' => false, 'status' => $status];
                }
            }

            return $result;
        };
        $activeCategories = $buildMapWithStatuses($newCatalogs['active'] ?? [], 'active');
        $excludedCategories = $buildMapWithStatuses($newCatalogs['excluded'] ?? [], 'excluded');
        $chosenCategories = $buildMapWithStatuses($newCatalogs['chosen'] ?? [], 'chosen');
        $neutralCategories = $buildMapWithStatuses($newCatalogs['neutral'] ?? [], 'neutral');

        $treeMap = $activeCategories + $excludedCategories + $chosenCategories + $neutralCategories;

        $this->dispatchBrowserEvent('catalog:renew', [
            'treeMap' => $treeMap,
        ]);
    }

    #[LiveAction]
    public function updateCategories(#[LiveArg] int $newId, #[LiveArg] bool $ifExclude = false)
    {
        $children = $this->children;
        $logger = $this->logger;

        $getLastNodes = function ($ids, $acc) use ($children, &$getLastNodes, $logger) {

            foreach ($ids as $id) {
                if (!array_key_exists($id, $children)) {
                    $acc[$id] = $id;
                } else {
                    $acc += $getLastNodes($children[$id], $acc);
                }
            }
            $logger->info(print_r($acc, true));
            return $acc;
        };

        $lastNodes = $getLastNodes([$newId], []);
        $this->logger->info('new getLastNodes result :');
        $this->logger->info(print_r($lastNodes, true));

        $collection = new ArrayCollection($lastNodes);
        $ifAnyExistInActive = $collection->exists(fn($key, $value) => in_array($value, $this->lastNodesChosen));
        $ifRevertExclude = array_diff($lastNodes, $this->lastNodesExcluded) === [];

        if ($ifRevertExclude) {
            $this->logger->info('What im doing wrong?');
            $this->logger->info('Rever exclude');
            $result['excluded'] = array_diff_key($this->lastNodesExcluded, $lastNodes);
        } elseif ($ifExclude) {
            $this->logger->info('Maybe this?');
            $this->logger->info('adding new exluding to array');
            $result['excluded'] = $lastNodes + $this->lastNodesExcluded;
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } elseif ($ifAnyExistInActive) {
            $this->logger->info('Deleting new');
            $result['included'] = array_diff_key($this->lastNodesChosen, $lastNodes);
        } else {
            $this->logger->info('Adding new without old');
            $choosenWithoutExcluded = array_diff_key($lastNodes, $this->lastNodesExcluded);
            $result['included'] = $choosenWithoutExcluded + $this->lastNodesChosen;
        }

        $this->emit('receiveCategories', [
            'newCategories' => $result,
        ]);
    }
}
