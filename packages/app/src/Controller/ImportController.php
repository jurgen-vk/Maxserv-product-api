<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use MaxServ\App\Entity\Import;
use MaxServ\App\Extractor\ImportExtractor;
use MaxServ\App\Message\RunImportMessage;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\Core\Twig\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ImportController
{

    public function __construct(
        private MessageBusInterface $bus,
        private ImportRepository $importRepository,
        private ImportExtractor $extractor,
        private TemplateRenderer $templateRenderer,
    ) {}

    #[Route(path: '/imports', name: 'imports_index', methods: ['GET'])]
    public function index(Request $request, array $parameters): Response
    {
        $result = $this->importRepository->findAllPaginated(
            page: $request->query->get(key: 'page'),
            perPage: $request->query->get(key: 'perPage'),
        );

        return new Response(
            content: $this->templateRenderer->render(
                template: 'pages.imports',
                data: [
                    'imports' => $result->items,
                    'paginator' => $result->paginator,
                ],
            ),
        );
    }

    #[Route(path: '/imports', name: 'imports_create', methods: ['POST'])]
    public function import(Request $request, array $parameters): JsonResponse
    {
        $types = $request->getPayload()->all(key: 'imports');

        if (empty($types)) {
            return new JsonResponse(
                data: ['error' => 'imports must be a non-empty array'],
                status: 400,
            );
        }

        $imports = [];
        foreach ($types as $type) {
            $import = new Import(type: $type);
            $this->importRepository->save(import: $import);
            $this->bus->dispatch( // no named argument on purpose, because interface
                new RunImportMessage(importRunId: $import->id, type: $type),
            );
            $imports[] = $this->extractor->extract(import: $import);
        }

        return new JsonResponse(data: ['imports' => $imports], status: 202);
    }

    #[Route(path: '/imports/{id}', name: 'imports_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, array $parameters): JsonResponse
    {
        $import = $this->importRepository->find(id: (int)$parameters['id']);

        if ($import === null) {
            return new JsonResponse(data: ['error' => 'Import not found'], status: 404);
        }

        return new JsonResponse(data: $this->extractor->extract(import: $import));
    }
}
