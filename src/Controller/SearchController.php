<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CatalogHandler;
use App\Service\MapAllRecords;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Exception;
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

    #[Route('/{_locale}/search', name: 'search')]
    public function index(
        Request $request,
        #[MapQueryParameter] string $q = '',
        #[MapQueryParameter] array $e = [],
        #[MapQueryParameter] array $f = [],
        #[MapQueryParameter] array $i = [],
    ) {
        $allProducts = $this->productRep->getAllWithSpecs();
        $filter = $this->mapAllRecords->mapRecords($allProducts);
        $excludedCategories = array_filter($e);
        $includedCategories = array_filter($i);
        $filers = array_filter($f);

        return $this->render('search/index.html.twig', [
            'query' => $q,
            'included' => $includedCategories,
            'excluded' => $excludedCategories,
            'filter' => $filter,
            'activeFilters' => $filers,
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
                    throw new Exception('Unknown action to handle the catalog');
            }
        }

        $decodedArray = urldecode(http_build_query($result));


        $url = $this->generateUrl('search');

        return $this->redirect($url . '?q=' . $query . '&' . $decodedArray);
    }

    // #[Route('/_filter_options', name: 'filter_options')]
    // public function getFilterOptions(Request $request, MapAllRecords $mapAllRecords)
    // {
    //     $allProducts = $this->productRep->getAllWithSpecs();

    //     $result = $mapAllRecords->mapRecords($allProducts);
    //     return $this->render('static/_filterFrame.html.twig', [
    //         'filter' => $result,
    //     ]);
    // }
}
