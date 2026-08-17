<?php

declare(strict_types=1);

namespace MaxServ\App\Importer;

use GuzzleHttp\Client;
use MaxServ\App\Entity\Import;
use MaxServ\App\Hydrator\MediaHydrator;
use MaxServ\App\Hydrator\ProductHydrator;
use MaxServ\App\Repository\BrandRepository;
use MaxServ\App\Repository\CategoryRepository;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\App\Repository\ProductRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ProductImporter extends AbstractImporter
{
    public function __construct(
        private Client $client,
        private ProductRepository $productRepo,
        private CategoryRepository $categoryRepo,
        private BrandRepository $brandRepo,
        private ProductHydrator $hydrator,
        private MediaHydrator $mediaHydrator,
        ImportRepository $importRepository,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct(
            importRepository: $importRepository,
            eventDispatcher: $eventDispatcher,
        );
    }

    public function supports(string $type): bool
    {
        return $type === 'products';
    }

    protected function run(Import $import): void
    {
        $probe = $this->client->get(
            uri: 'https://dummyjson.com/products',
            options: ['query' => ['limit' => 1]],
        );
        $total = json_decode(
            json: $probe->getBody()->getContents(),
            associative: true,
        )['total'];

        $this->markRunning(import: $import, total: $total);

        $count = 0;
        $skip = 0;
        $limit = 100;

        $categories = [];
        foreach ($this->categoryRepo->findAll() as $category) {
            $categories[$category->name] = $category;
        }
        $brands = [];
        foreach ($this->brandRepo->findAll() as $brand) {
            $brands[$brand->name] = $brand;
        }

        do {
//            usleep(microseconds: 50000);
            $response = $this->client->get(
                uri: 'https://dummyjson.com/products',
                options: [
                    'query' => ['limit' => $limit, 'skip' => $skip],
                ],
            );

            $data = json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
            );

            $products = [];

            foreach ($data['products'] as $item) {
                $category = $categories[$item['category']]
                    ??= $this->categoryRepo->findOrCreateOneByName(name: $item['category']);
                $brand = !empty($item['brand'])
                    ? ($brands[$item['brand']]
                        ??= $this->brandRepo->findOrCreateOneByName(name: $item['brand']))
                    : null;

                $media = $this->mediaHydrator->hydrateManyFromApi(
                    thumbnail: $item['thumbnail'] ?? null,
                    images: $item['images'] ?? [],
                    entityType: 'product',
                    entityId: (int)$item['id'],
                );

                $products[] = $this->hydrator->hydrateFromApi(
                    data: $item,
                    category: $category,
                    brand: $brand,
                    media: $media,
                );

                $count++;
            }

            $this->productRepo->saveMany(products: $products);
            $this->productRepo->saveManyMedia(products: $products);

            $this->updateProgress(import: $import, processed: $count);

            $skip += $limit;
        } while ($skip < $total);
    }
}
