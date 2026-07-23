<?php

declare(strict_types=1);

namespace MaxServ\Core\Messenger;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as BridgeConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class DatabaseTransportFactory
{
    public function __construct(
        private readonly string $host,
        private readonly string $user,
        private readonly string $password,
        private readonly string $database,
        private readonly SerializerInterface $serializer,
        private readonly string $tableName,
        private readonly bool $autoSetup,
    ) {
    }

    public function create(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $dbalConnection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $this->host,
            'user' => $this->user,
            'password' => $this->password,
            'dbname' => $this->database,
        ]);

        $configuration = BridgeConnection::buildConfiguration($dsn, $options);

        return new DoctrineTransport(new BridgeConnection($configuration, $dbalConnection), $serializer);
    }

    public function createDefault(string $queueName): TransportInterface
    {
        return $this->create('doctrine://default', [
            'table_name' => $this->tableName,
            'queue_name' => $queueName,
            'auto_setup' => $this->autoSetup,
        ], $this->serializer);
    }
}
