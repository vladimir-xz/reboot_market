<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CatalogHandler;
use App\Service\MapAllRecords;
use PhpParser\Node\Stmt\Break_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Psr\Log\LoggerInterface;

final class SearchController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductRepository $productRep,
        private MapAllRecords $mapAllRecords,
        private CatalogHandler $catHandler,
    ) {
    }

    #[Route('/search', name: 'search')]
    public function index(Request $request)
    {
        $allParams = $request->query->all();
        $this->logger->info('this is page');
        $this->logger->info(print_r($request->query->all(), true));
        $page = $allParams['p'] ?? 1;
        $query = $allParams['q'] ?? '';
        $excludedCategories = $allParams['e'] ?? [];
        $filters = $allParams['f'] ?? [];
        $includedCategories = $allParams['i'] ?? [];
        if (is_string($includedCategories)) {
            $includedCategories = [];
        }
        if (is_string($excludedCategories)) {
            $excludedCategories = [];
        }
        if (is_string($filters)) {
            $filters = [];
        }

        $allProducts = $this->productRep->getAllWithSpecs();
        $filter = $this->mapAllRecords->mapRecords($allProducts);


        if ($excludedCategories || $includedCategories) {
            $allRecords = $this->productRep->getAllProductsWithCategoryAndFilters($query, $includedCategories, $excludedCategories, $filters);
            $map = $this->mapAllRecords->mapRecords($allRecords, true);

            $categories['active'] = $map['categories'] ?? [];
            $categories['included'] = $includedCategories;
            $categories['excluded'] = $excludedCategories;
            $treeMap = $this->catHandler->prepareNewCatalogsForDrawing($categories);
            $maxNbPages = ceil(count($allRecords) / 12);
        } else {
            // TODO: change to count when mapping
            $maxNbPages = ceil(count($allProducts) / 12);
        }

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'included' => $includedCategories,
            'excluded' => $excludedCategories,
            'filter' => $filter,
            'treeMap' => $treeMap ?? [],
            'activeFilters' => $filters,
            'maxNbPages' => $maxNbPages,
        ]);
    }

    #[Route('/_new_search', name: 'new_search')]
    public function setNewQueryParam(Request $request)
    {
        $newCategory = $request->query->get('cat', null);
        $query = $request->query->getString('q', '');
        if ($newCategory) {
            [$action, $id] = explode('_', $newCategory);
            switch ($action) {
                case 'rev':
                    $result = $this->catHandler->revertCategories($id, [], []);
                    break;
                case 'ex':
                    $result = $this->catHandler->excludeCategories($id, [], []);
                    break;
                case 'in':
                    $result = $this->catHandler->includeCategories($id, [], []);
                    break;
                default:
                    die();
            }
        }

        $url = $this->generateUrl('search', [
            'q' => $query,
            'i' => $result['included'] ?? [],
            'e' => $result['e'] ?? []
        ]);

        return $this->redirect($url);
    }

    #[Route('/_new_product_scroll', name: 'new_product_scroll')]
    public function getNewProductsForScroll(Request $request)
    {
        $allParams = $request->query->all();
        $this->logger->info('this is page');
        $this->logger->info(print_r($request->query->all(), true));
        $page = $allParams['p'] ?? 1;
        $query = $allParams['q'] ?? '';
        $excludedCategories = $allParams['e'] ?? [];
        $filters = $allParams['f'] ?? [];
        $includedCategories = $allParams['i'] ?? [];
        if (is_string($includedCategories)) {
            $includedCategories = [];
        }
        if (is_string($excludedCategories)) {
            $excludedCategories = [];
        }
        if (is_string($filters)) {
            $filters = [];
        }

        $paginator = $this->productRep->getPaginatedValues($query, $includedCategories, $excludedCategories, $filters, $page);
        $maxNbPages = $paginator->getNbPages();
        if ($page > $maxNbPages) {
            $this->logger->info($maxNbPages);
            $this->logger->info($page);
            die();
        }

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->renderBlock('search/infiniteScroll.html.twig', 'new_search_block', [
            'paginator' => $paginator, 'maxNbPages' => $maxNbPages
        ]);
    }

    #[Route('/_product_scroll', name: 'product_scroll')]
    public function getProductsForScroll(Request $request)
    {
        $allParams = $request->query->all();
        $this->logger->info('this is page');
        $this->logger->info(print_r($request->query->get('p'), true));
        $page = $allParams['p'] ?? 1;
        $query = $allParams['q'] ?? '';
        $excludedCategories = $allParams['e'] ?? [];
        $filters = $allParams['f'] ?? [];
        $includedCategories = $allParams['i'] ?? [];
        if (is_string($includedCategories)) {
            $includedCategories = [];
        }
        if (is_string($excludedCategories)) {
            $excludedCategories = [];
        }
        if (is_string($filters)) {
            $filters = [];
        }

        $paginator = $this->productRep->getPaginatedValues($query, $includedCategories, $excludedCategories, $filters, $page);
        $maxNbPages = $paginator->getNbPages();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->renderBlock('search/infiniteScroll.html.twig', 'scroll_block', ['paginator' => $paginator]);
    }

    #[Route('/_filter_options', name: 'filter_options')]
    public function getFilterOptions(Request $request)
    {
        $this->logger->info('Lazy loaded filters');
        $allProducts = $this->productRep->getAllWithSpecs();

        // $filter = array_reduce(function (array $accumulator, Product $value): array {
        // }, []);
        $filter = array_reduce($allProducts, function ($accumulator, $value) {
            $company = $value->getBrand();
            $price = $value->getPrice();
            $type = $value->getType();
            $specs = $value->getSpecifications();
            $currentMax = $accumulator['price']['max'] ?? 0;
            $currentMin = $accumulator['price']['min'] ?? 0;

            $accumulator['brand'][$company] = $company;
            $accumulator['type'][$type] = $type;
            if ($currentMin === 0 && $currentMax < $price) {
                $accumulator['price']['max'] = $price;
                $accumulator['price']['min'] = $currentMax;
            } elseif ($currentMax < $price) {
                $accumulator['price']['max'] = $price;
            } elseif ($currentMin === 0 || $currentMin > $price) {
                $accumulator['price']['min'] = $price;
            }

            foreach ($specs as $spec) {
                $property = $spec->getProperty();
                $propValue = $spec->getValue();
                $accumulator[$property][$propValue] = $propValue;
            }

            return $accumulator;
        }, []);

        // $this->logger->info(print_r($filter, true));

        return $this->render('static/_filterFrame.html.twig', [
            'filter' => $filter,
        ]);
    }

    // #[Route('/_search', name: 'searchStream')]
    // public function getProductSearch(Request $request): Response
    // {
    //     $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
    //     return $this->renderBlock('search/index.html.twig', 'search_block');
    // }

    // #[Route('/search', name: 'search')]
    // public function index(Request $request): Response
    // {
    //     $allParams = $request->query->all();
    //     $page = $allParams['page'] ?? 1;
    //     $query = $allParams['q'] ?? '';
    //     $activeCategories = $allParams['c'] ?? [];
    //     $brands = $allParams['b'] ?? [];
    //     if (is_string($activeCategories)) {
    //         $activeCategories = [];
    //     }
    //     if (is_string($brands)) {
    //         $brands = [];
    //     }
    //     $brands = json_encode($brands);
    //     // $products = $productRepository->getPaginatedValues($query, $activeCategories, $page);
    //     // $productsNotPad = $productRepository->findByNameField($query, $activeCategories);
    //     // $categories = $productRepository->getCategoriesFromSearch($query, $activeCategories);

    //     return $this->render('homepage.html.twig', [
    //         // 'notPaginated' => $productsNotPad,
    //         'categories' => $categories ?? [],
    //         'page' => $page,
    //         'brands' => $brands
    //         // 'array' => $array,
    //     ]);
    // }
}
