<?php

declare(strict_types=1);

function createTestTable(PDO $pdo): void
{

    $pdo->query("DROP TABLE IF EXISTS test");

    $pdo->query("CREATE TABLE test (id INTEGER PRIMARY KEY, c1 TEXT DEFAULT '')");

}
