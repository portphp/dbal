<?php

namespace Port\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Port\Reader\CountableReader;

/**
 * Reads data through the Doctrine DBAL
 */
class DbalReader implements CountableReader
{
    private Connection $connection;

    private array|false|null $data = null;

    private ?Result $result = null;

    private string $sql = '';

    private array $params = [];

    private ?int $rowCount = null;

    private bool $rowCountCalculated = true;

    private int $key = 0;

    public function __construct(Connection $connection, string $sql, array $params = [])
    {
        $this->connection = $connection;
        $this->setSql($sql, $params);
    }

    public function setRowCountCalculated(bool $calculate = true): void
    {
        $this->rowCountCalculated = $calculate;
    }

    public function isRowCountCalculated(): bool
    {
        return $this->rowCountCalculated;
    }

    public function setSql(string $sql, array $params = []): void
    {
        $this->sql = $sql;
        $this->setSqlParameters($params);
    }

    public function setSqlParameters(array $params): void
    {
        $this->params = $params;
        $this->result = null;
        $this->rowCount = null;
        $this->data = null;
        $this->key = 0;
    }

    public function current(): mixed
    {
        if (null === $this->data && null === $this->result) {
            $this->rewind();
        }

        return $this->data;
    }

    public function next(): void
    {
        $this->key++;
        $this->data = $this->result?->fetchAssociative() ?: false;
    }

    public function key(): mixed
    {
        return $this->key;
    }

    public function valid(): bool
    {
        if (null === $this->data && null === $this->result) {
            $this->rewind();
        }

        return false !== $this->data;
    }

    public function rewind(): void
    {
        $this->result = $this->connection->executeQuery($this->sql, $this->params);
        $this->data = $this->result->fetchAssociative();
        $this->key = 0;
    }

    public function count(): int
    {
        if (null === $this->rowCount) {
            if ($this->rowCountCalculated) {
                $this->doCalcRowCount();
            } else {
                if (null === $this->result) {
                    $this->rewind();
                }
                $this->rowCount = $this->result?->rowCount() ?? 0;
            }
        }

        return $this->rowCount;
    }

    private function doCalcRowCount(): void
    {
        $countSql = sprintf('SELECT COUNT(*) FROM (%s) AS port_cnt', $this->sql);
        $this->rowCount = (int) $this->connection->fetchOne($countSql, $this->params);
    }
}
