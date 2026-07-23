<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use MaxServ\App\Dto\Pagination\Paginator;
use MaxServ\App\Filter\ProductFilter;
use MaxServ\App\Repository\BrandRepository;
use MaxServ\App\Repository\CategoryRepository;
use MaxServ\App\Repository\ProductRepository;
use MaxServ\Core\Http\Exception\HttpException;
use MaxServ\Core\Render\TemplateRenderer;
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
  ) {}

  #[Route('/products', name: 'products')]
  public function index(Request $request, array $parameters): Response
  {
    $filter = ProductFilter::fromRequest($request);
    $paginator = Paginator::fromRequest($request);

    $result = $this->productRepository->searchPaginated($filter, $paginator);

    if ($request->query->getString('partial') === 'table') {
      return new Response($this->templateRenderer->render('components/product/table.html.twig', [
        'items'     => $result->items,
        'paginator' => $result->paginator,
        'filter'    => $filter,
      ]));
    }

    return new Response($this->templateRenderer->render('pages/products/index.html.twig', [
      'items'      => $result->items,
      'paginator'  => $result->paginator,
      'filter'     => $filter,
      'categories' => $this->categoryRepository->findAll(),
      'brands'     => $this->brandRepository->findAll(),
    ]));
  }

  #[Route('/products/{id}', name: 'product_show')]
  public function show(Request $request, array $parameters): Response
  {
    $product = $this->productRepository->find((int) $parameters['id']);

    if ($product === null) {
      throw new HttpException(404, "Product #{$parameters['id']} not found");
    }

    return new Response($this->templateRenderer->render('pages/products/show.html.twig', [
      'product' => $product,
    ]));
  }
}
