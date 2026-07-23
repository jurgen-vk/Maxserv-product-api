<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use DateTimeImmutable;
use MaxServ\App\Entity\Import;
use MaxServ\App\Extractor\ImportExtractor;
use MaxServ\App\Message\RunImportMessage;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\Core\Render\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

readonly class ImportController
{
  public function __construct(
    private MessageBusInterface $bus,
    private ImportRepository $importRepository,
    private ImportExtractor $extractor,
    private TemplateRenderer $templateRenderer,
  ) {}

  #[Route('/imports', name: 'imports_create', methods: ['POST'])]
  public function import(Request $request, array $parameters): JsonResponse
  {
    $types = json_decode($request->getContent(), true)['importers'] ?? [];

    if (!is_array($types) || $types === []) {
      return new JsonResponse(['error' => 'importers must be a non-empty array'], 400);
    }

    $imports = [];
    foreach ($types as $type) {
      $import = new Import(type: $type, startedAt: new DateTimeImmutable());
      $this->importRepository->save(import: $import);
      $this->bus->dispatch(new RunImportMessage(importRunId: $import->id, type: $type));
      $imports[] = $this->extractor->extract($import);
    }

    return new JsonResponse(['imports' => $imports], 202);
  }

  #[Route('/imports', name: 'imports_index', methods: ['GET'])]
  public function index(Request $request, array $parameters): Response
  {
    $imports = [];
    foreach ($this->importRepository->findAll() as $import) {
      $imports[$import->id] = $this->extractor->extract($import);
    }

    return new Response($this->templateRenderer->render('pages/imports/index.html.twig', [
      'imports' => $imports,
    ]));
  }

  #[Route('/imports/{id}', name: 'imports_show', methods: ['GET'])]
  public function show(Request $request, array $parameters): JsonResponse
  {
    $import = $this->importRepository->find(id: (int) $parameters['id']);

    if ($import === null) {
      return new JsonResponse(['error' => 'Import not found'], 404);
    }

    return new JsonResponse($this->extractor->extract($import));
  }
}
