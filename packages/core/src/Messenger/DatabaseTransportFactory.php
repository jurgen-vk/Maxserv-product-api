<?php

declare(strict_types=1);

namespace MaxServ\Core\Messenger;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as BridgeConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly  class DatabaseTransportFactory
{
    public function __construct(
        private string $host,
        private string $user,
        private string $password,
        private string $database,
        private SerializerInterface $serializer,
        private string $tableName,
        private bool $autoSetup,
    ) {}

    public function create(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $dbalConnection = DriverManager::getConnection(
            params: [
                'driver' => 'pdo_mysql',
                'host' => $this->host,
                'user' => $this->user,
                'password' => $this->password,
                'dbname' => $this->database,
            ],
        );

        $configuration = BridgeConnection::buildConfiguration(dsn: $dsn, options: $options);

        return new DoctrineTransport(
            connection: new BridgeConnection(
                configuration: $configuration,
                driverConnection: $dbalConnection,
            ),
            serializer: $serializer,
        );
    }

    public function createDefault(string $queueName): TransportInterface
    {
        return $this->create(
            dsn: 'doctrine://default',
            options: [
                'table_name' => $this->tableName,
                'queue_name' => $queueName,
                'auto_setup' => $this->autoSetup,
            ],
            serializer: $this->serializer,
        );
    }
}
