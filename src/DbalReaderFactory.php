<?php

namespace Port\Dbal;

use Doctrine\DBAL\Connection;

/**
 * Factory that creates DbalReaders
 */
class DbalReaderFactory
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function getReader(string $sql, array $params = []): DbalReader
    {
        return new DbalReader($this->connection, $sql, $params);
    }
}
