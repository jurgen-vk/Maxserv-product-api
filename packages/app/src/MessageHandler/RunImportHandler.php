<?php

declare(strict_types=1);

namespace MaxServ\App\MessageHandler;

use MaxServ\App\Message\RunImportMessage;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\App\Service\ImportService;
use MaxServ\App\Service\MercureProgressReporter;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'messenger.bus.default')]
class RunImportHandler
{
  public function __construct(
    private readonly ImportRepository $importRepository,
    private readonly ImportService $importService,
    private readonly HubInterface $hub,
  ) {}

  public function __invoke(RunImportMessage $message): void
  {
    $import = $this->importRepository->find(id: $message->importRunId);
    $import->markRunning();
    $this->importRepository->save(import: $import);

    $reporter = new MercureProgressReporter(hub: $this->hub, importId: $message->importRunId);

    try {
      $count = $this->importService->run(type: $message->type, reporter: $reporter);
    } catch (\Throwable $exception) {
      $import->markFailed();
      $this->importRepository->save(import: $import);

      $this->hub->publish(new Update(
        topics: "imports/{$message->importRunId}",
        data: json_encode(['id' => $message->importRunId, 'status' => 'failed']),
      ));

      throw $exception;
    }

    $import->markCompleted(count: $count);
    $this->importRepository->save(import: $import);

    $this->hub->publish(new Update(
      topics: "imports/{$message->importRunId}",
      data: json_encode(['id' => $message->importRunId, 'status' => 'completed', 'count' => $count]),
    ));
  }
}
