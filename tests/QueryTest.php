<?php

declare(strict_types=1);

use MichaelRushton\DB\Connections\SQLiteConnection;
use MichaelRushton\DB\Drivers\SQLiteDriver;
use MichaelRushton\ORM\DataMapper;
use MichaelRushton\ORM\EntityManager;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Query;
use Tests\Entities\Entity;
use Tests\Entities\PrimaryKeyEntity;

beforeEach(function () {

    $this->connection = new SQLiteConnection(new SQLiteDriver([
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    ]));

    $this->entity_manager = new EntityManager($this->connection);

});

test('select', function () {

    expect(
        (string) new Query($this->connection, new DataMapper(PrimaryKeyEntity::class))
        ->with('cte', 'SELECT')
        ->highPriority()
        ->straightJoinAll()
        ->sqlSmallResult()
        ->sqlBigResult()
        ->sqlBufferResult()
        ->sqlCache()
        ->top(1)
        ->percent()
        ->join('t1')
        ->where('c1')
        ->window('w1')
        ->orderBy('c1')
        ->limit(1)
        ->rowsExamined(1)
        ->forUpdate()
        ->forNoKeyUpdate()
        ->forShare()
        ->forKeyShare()
        ->lockInShareMode()
    )
    ->toBe(implode(' ', [
        'WITH cte AS (SELECT)',
        'SELECT',
        'DISTINCT',
        'HIGH_PRIORITY',
        'STRAIGHT_JOIN',
        'SQL_SMALL_RESULT',
        'SQL_BIG_RESULT',
        'SQL_BUFFER_RESULT',
        'SQL_CACHE',
        'TOP (1)',
        'PERCENT',
        'test.*',
        'FROM test',
        'JOIN t1',
        'WHERE c1',
        'WINDOW w1 AS ()',
        'ORDER BY c1',
        'LIMIT 1',
        'ROWS EXAMINED 1',
        'FOR UPDATE',
        'FOR NO KEY UPDATE',
        'FOR SHARE',
        'FOR KEY SHARE',
        'LOCK IN SHARE MODE',
    ]));

});

test('select offset fetch', function () {

    expect(
        (string) new Query($this->connection, new DataMapper(Entity::class))
        ->orderBy('c1')
        ->offsetFetch(1, 2)
        ->withTies()
        ->rowsExamined(1)
    )
    ->toBe(implode(' ', [
        'SELECT',
        'DISTINCT',
        'test.*',
        'FROM test',
        'ORDER BY c1',
        'OFFSET 1 ROWS FETCH NEXT 2 ROWS WITH TIES',
        'ROWS EXAMINED 1'
    ]));

});

test('connection', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    expect($query->connection())
    ->toBe($this->connection);

});

test('data mapper', function () {

    $query = new Query($this->connection, $data_mapper = new DataMapper(Entity::class));

    expect($query->dataMapper())
    ->toBe($data_mapper);

});

test('fetch', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = $query->where('id', 2)->fetch())
    ->toBeInstanceOf(Entity::class);

    expect($entity->id)
    ->toBe(2);

    expect($entity->name)
    ->toBe('');

});

test('fetch failure', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    expect($query->fetch())
    ->toBeFalse();

});

test('require', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = $query->where('id', 2)->require())
    ->toBeInstanceOf(Entity::class);

    expect($entity->id)
    ->toBe(2);

    expect($entity->name)
    ->toBe('');

});

test('require not found', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, '')");

    $query->where('id', 2)->require();

})
->throws(EntityNotFoundException::class);

test('fetch all', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $query->fetchAll())
    ->toBeArray();

    expect(count($entities))
    ->toBe(2);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(Entity::class);

        expect($entity->id)
        ->toBe($i + 1);

        expect($entity->name)
        ->toBe('');

    }

});

test('fetch all failure', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    expect($query->fetchAll())
    ->toBeFalse();

});

test('yield', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $query->yield())
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(Entity::class);

        expect($entity->id)
        ->toBe($i + 1);

        expect($entity->name)
        ->toBe('');

    }

    expect($i)
    ->toBe(1);

});

test('yield failure', function () {

    $query = new Query($this->connection, new DataMapper(Entity::class));

    expect($entities = $query->yield())
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $entity) {
    }

    expect(!isset($entity))
    ->toBeTrue();

});
