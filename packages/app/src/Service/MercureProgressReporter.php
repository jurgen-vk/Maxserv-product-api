<?php

declare(strict_types=1);

namespace MaxServ\App\Service;

use MaxServ\App\Interface\ImportProgressReporterInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureProgressReporter implements ImportProgressReporterInterface
{
  public function __construct(
    private readonly HubInterface $hub,
    private readonly int $importId,
  ) {}

  public function report(int $processed, int $total): void
  {
    try {
      $this->hub->publish(new Update(
        topics: "imports/{$this->importId}",
        data: json_encode(['id' => $this->importId, 'status' => 'running', 'processed' => $processed, 'total' => $total]),
      ));
    } catch (\Throwable $exception) {
      // A Mercure hiccup is a lost UI notification, not an import failure — the import
      // itself must keep running regardless of whether anyone was able to see this update.
      error_log($exception);
    }
  }
}
