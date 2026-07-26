<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use MaxServ\App\Filter\ProductFilter;
use MaxServ\App\Repository\BrandRepository;
use MaxServ\App\Repository\CategoryRepository;
use MaxServ\App\Repository\ProductRepository;
use MaxServ\Core\Http\Exception\HttpException;
use MaxServ\Core\Render\FragmentRenderer;
use MaxServ\Core\Render\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

readonly class ProductController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private TemplateRenderer $templateRenderer,
        private FragmentRenderer $fragmentRenderer,
    ) {}

    #[Route(path: '/products', name: 'products', methods: ['GET'])]
    public function index(Request $request, array $parameters): Response
    {
        $filter = ProductFilter::fromRequest(request: $request);
        $result = $this->productRepository->searchPaginated(
            filter: $filter,
            page: $request->query->get('page'),
            perPage: $request->query->get('perPage'),
        );

        $data = [
            'items' => $result->items,
            'paginator' => $result->paginator,
            'filter' => $filter,
            'categories' => $this->categoryRepository->findAll(),
            'brands' => $this->brandRepository->findAll(),
        ];

        $requestedFragments = $request->query->all(key: 'fragments');
        if ($requestedFragments !== []) {
            return new JsonResponse(
                $this->fragmentRenderer->render(
                    page: 'products',
                    fragments: $requestedFragments,
                    data: $data,
                ),
            );
        }

        return new Response(
            content: $this->templateRenderer->render(
                template: 'pages.products',
                arguments: $data,
            ),
        );
    }

    #[Route(path: '/products/{id}', name: 'product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, array $parameters): Response
    {
        $product = $this->productRepository->find(id: (int)$parameters['id']);

        if ($product === null) {
            throw new HttpException(
                statusCode: 404,
                message: "Product #{$parameters['id']} not found",
            );
        }

        return new Response(
            content: $this->templateRenderer->render(
                template: 'pages.products.show',
                arguments: [
                    'product' => $product,
                ],
            ),
        );
    }
}
