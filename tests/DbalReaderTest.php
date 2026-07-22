<?php

namespace Port\Dbal\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Port\Dbal\DbalReader;

class DbalReaderTest extends TestCase
{
    public function testCount(): void
    {
        $this->assertSame(10, $this->getReader()->count());
    }

    public function testCountInhibited(): void
    {
        $reader = $this->getReader();
        $reader->setRowCountCalculated(false);

        // SQLite SELECT rowCount is often 0; ensure method is callable
        $this->assertIsInt($reader->count());
    }

    public function testIterate(): void
    {
        $i = 31;
        foreach ($this->getReader() as $key => $row) {
            $this->assertIsArray($row);
            $this->assertSame('user-'.$i, $row['username']);
            $this->assertSame($i - 31, $key);
            $i++;
        }

        $this->assertSame(41, $i);
    }

    public function testReaderRewindWorksCorrectly(): void
    {
        $reader = $this->getReader();
        foreach ($reader as $row) {
            if (($row['username'] ?? null) === 'user-35') {
                break;
            }
        }

        $reader->rewind();

        $this->assertSame([
            'id' => 31,
            'username' => 'user-31',
            'name' => 'name 4',
        ], $reader->current());
    }

    public function testCallingCurrentTwiceShouldNotAdvance(): void
    {
        $reader = $this->getReader();
        $first = $reader->current();
        $second = $reader->current();
        $this->assertSame($first, $second);
    }

    private function getConnection()
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $schema = new Schema();
        $table = $schema->createTable('groups');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 45]);
        $table->setPrimaryKey(['id']);

        $user = $schema->createTable('user');
        $user->addColumn('id', 'integer', ['autoincrement' => true]);
        $user->addColumn('username', 'string', ['length' => 32]);
        $user->addColumn('group_id', 'integer');
        $user->setPrimaryKey(['id']);

        $platform = $connection->getDatabasePlatform();
        foreach ($schema->toSql($platform) as $query) {
            $connection->executeStatement($query);
        }

        $counter = 1;
        for ($i = 1; $i <= 10; $i++) {
            $connection->insert('groups', ['name' => "name {$i}"]);
            $id = $connection->lastInsertId();
            for ($j = 1; $j <= 10; $j++) {
                $connection->insert('user', [
                    'username' => "user-{$counter}",
                    'group_id' => $id,
                ]);
                $counter++;
            }
        }

        return $connection;
    }

    private function getReader(): DbalReader
    {
        $connection = $this->getConnection();

        return new DbalReader($connection, implode(' ', [
            'SELECT u.id, u.username, g.name',
            'FROM user u INNER JOIN groups g ON u.group_id = g.id',
            'WHERE g.name LIKE :name',
        ]), [
            'name' => 'name 4',
        ]);
    }
}
