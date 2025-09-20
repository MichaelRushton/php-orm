<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Interfaces;

use Generator;
use MichaelRushton\DB\Interfaces\ConnectionInterface;
use PDOStatement;

interface EntityManagerInterface
{
    public function connection(): ConnectionInterface;

    public static function dataMapper(string $class): DataMapperInterface;

    public function table(string $class): string;

    public function primaryKey(string $class): string|array;

    public function fetch(
        string $class,
        int|string|array|callable $where = []
    ): object|false;

    public function require(
        string $class,
        int|string|array|callable $where = []
    ): object;

    public function fetchAll(
        string $class,
        array|callable $where = []
    ): array|false;

    public function yield(
        string $class,
        array|callable $where = []
    ): Generator;

    public function delete(object $entity): PDOStatement|false;

    public function insert(object $entity): PDOStatement|false;

    public function update(object $entity): PDOStatement|false;

    public function query(string $class): QueryInterface;
}
