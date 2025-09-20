<?php

declare(strict_types=1);

use MichaelRushton\DB\Connections\SQLiteConnection;
use MichaelRushton\DB\Drivers\SQLiteDriver;
use MichaelRushton\ORM\EntityManager;
use MichaelRushton\ORM\Exceptions\EntityMissingPrimaryKeyValueException;
use MichaelRushton\ORM\Exceptions\EntityMissingTableAttributeException;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Query;
use Tests\Entities\Entity;
use Tests\Entities\PrimaryKeyEntity;

beforeEach(function () {

    $this->connection = new SQLiteConnection(new SQLiteDriver([
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]));

});

test('connection', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->connection())
    ->toBe($this->connection);

});

test('data mapper', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->dataMapper(Entity::class))
    ->toBe($entity_manager->dataMapper(Entity::class))
    ->not->toBe($entity_manager->dataMapper(PrimaryKeyEntity::class));

});

test('table', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->table(Entity::class))
    ->toBe('test');

});

test('missing table attribute', function () {

    $entity_manager = new EntityManager($this->connection);

    $entity_manager->table(stdClass::class);

})
->throws(EntityMissingTableAttributeException::class, stdClass::class);

test('primary key', function ($entity, $primary_key) {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->primaryKey($entity))
    ->toBe($primary_key);

})
->with([
    [PrimaryKeyEntity::class, ['id1', 'id2']],
    [Entity::class, 'id'],
]);

test('fetch', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = $entity_manager->fetch(Entity::class, 2))
    ->toBeInstanceOf(Entity::class);

    expect($entity->id)
    ->toBe(2);

    expect($entity->name)
    ->toBe('');

});

test('fetch failure', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->fetch(Entity::class, 1))
    ->toBeFalse();

});

test('require', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = $entity_manager->require(Entity::class, 2))
    ->toBeInstanceOf(Entity::class);

    expect($entity->id)
    ->toBe(2);

    expect($entity->name)
    ->toBe('');

});

test('require not found', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, '')");

    $entity_manager->require(Entity::class, 2);

})
->throws(EntityNotFoundException::class);

test('fetch all', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $entity_manager->fetchAll(Entity::class))
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

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->fetchAll(Entity::class))
    ->toBeFalse();

});

test('fetch all where', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $entity_manager->fetchAll(Entity::class, ['id' => 2]))
    ->toBeArray();

    expect(count($entities))
    ->toBe(1);

    expect($entities[0]->id)
    ->toBe(2);

    expect($entities[0]->name)
    ->toBe('');

});

test('yield', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $entity_manager->yield(Entity::class))
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

    $entity_manager = new EntityManager($this->connection);

    expect($entities = $entity_manager->yield(Entity::class))
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $entity) {
    }

    expect(!isset($entity))
    ->toBeTrue();

});

test('yield where', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = $entity_manager->yield(Entity::class, ['id' => 2]))
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(Entity::class);

        expect($entity->id)
        ->toBe(2);

        expect($entity->name)
        ->toBe('');

    }

    expect($i)
    ->toBe(0);

});

test('delete', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    $entity = new Entity();

    $entity->id = 1;

    expect($entity_manager->delete($entity))
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 2,
        'c1' => '',
    ]]);

});

test('missing primary key value for delete', function () {

    $entity_manager = new EntityManager($this->connection);

    $entity_manager->delete(new Entity());

})
->throws(EntityMissingPrimaryKeyValueException::class, Entity::class . ': id');

test('insert', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($this->connection->pdo());

    $entity = new Entity();

    $entity->name = 'test';

    expect($entity_manager->insert($entity))
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 1,
        'c1' => 'test',
    ]]);

    expect($entity->id)
    ->toBe(1);

});

test('insert failure', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->insert(new Entity()))
    ->toBeFalse();

});

test('update', function () {

    $entity_manager = new EntityManager($this->connection);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    $entity = new Entity();

    $entity->id = 2;
    $entity->name = 'test';

    expect($entity_manager->update($entity))
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 1,
        'c1' => '',
    ], [
        'id' => 2,
        'c1' => 'test',
    ]]);

});

test('missing primary key value for update', function () {

    $entity_manager = new EntityManager($this->connection);

    $entity_manager->update(new Entity());

})
->throws(EntityMissingPrimaryKeyValueException::class, Entity::class . ': id');

test('query', function () {

    $entity_manager = new EntityManager($this->connection);

    expect($entity_manager->query(Entity::class))
    ->toBeInstanceOf(Query::class);

});
