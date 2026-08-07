<?php

declare(strict_types=1);

namespace MaxServ\App\Importer;

use GuzzleHttp\Client;
use MaxServ\App\Entity\Import;
use MaxServ\App\Event\Import\ImportProgressEvent;
use MaxServ\App\Hydrator\MediaHydrator;
use MaxServ\App\Hydrator\ProductHydrator;
use MaxServ\App\Interface\ImporterInterface;
use MaxServ\App\Repository\BrandRepository;
use MaxServ\App\Repository\CategoryRepository;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\App\Repository\ProductRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ProductImporter implements ImporterInterface
{
    public function __construct(
        private Client $client,
        private ProductRepository $productRepo,
        private CategoryRepository $categoryRepo,
        private BrandRepository $brandRepo,
        private ImportRepository $importRepository,
        private ProductHydrator $hydrator,
        private MediaHydrator $mediaHydrator,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function supports(string $type): bool
    {
        return $type === 'products';
    }

    public function import(Import $import): void
    {
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
//            usleep(250000);
            $response = $this->client->get(
                uri: 'https://dummyjson.com/products',
                options: [
                    'query' => ['limit' => $limit, 'skip' => $skip],
                ],
            );

            $data = json_decode(json: $response->getBody()->getContents(), associative: true);

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

            $import->markProgress(processed: $count, total: $data['total']);
            $this->importRepository->save(import: $import);

            $this->eventDispatcher->dispatch(
                event: new ImportProgressEvent(import: $import),
            );

            $skip += $limit;
        } while ($skip < $data['total']);
    }
}
