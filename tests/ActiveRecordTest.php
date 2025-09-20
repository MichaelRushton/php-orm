<?php

declare(strict_types=1);

use MichaelRushton\DB\Connections\SQLiteConnection;
use MichaelRushton\DB\Drivers\SQLiteDriver;
use MichaelRushton\ORM\ActiveRecord;
use MichaelRushton\ORM\EntityManager;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Query;
use Tests\Entities\ActiveRecordEntity;

beforeEach(function () {

    $this->connection = new SQLiteConnection(new SQLiteDriver([
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]));

    $this->entity_manager = new EntityManager($this->connection);

});

test('set and get entity manager', function () {

    expect(ActiveRecordEntity::getEntityManager())
    ->toBeNull();

    ActiveRecord::setEntityManager($this->entity_manager);

    expect(ActiveRecordEntity::getEntityManager())
    ->toBe($this->entity_manager);

});

test('table', function () {

    expect(ActiveRecordEntity::table())
    ->toBe('test');

});

test('primary key', function () {

    expect(ActiveRecordEntity::primaryKey())
    ->toBe('id');

});

test('set and get data', function () {

    $entity = new ActiveRecordEntity();

    $entity->setAttributes(['id1' => 1, 'id2' => 2]);
    $entity->setAttributes(['id2' => 3, 'id3' => 4]);

    expect($entity->getAttributes())
    ->toBe(['id2' => 3, 'id3' => 4]);

});

test('merge and get data', function () {

    $entity = new ActiveRecordEntity();

    $entity->mergeAttributes(['id1' => 1, 'id2' => 2]);
    $entity->mergeAttributes(['id2' => 3, 'id3' => 4]);

    expect($entity->getAttributes())
    ->toBe(['id1' => 1, 'id2' => 3, 'id3' => 4]);

});

test('set data in constructor', function () {

    $entity = new ActiveRecordEntity(['id' => 1]);

    expect($entity->getAttributes())
    ->toBe(['id' => 1]);

});

test('magic setter and getter', function () {

    $entity = new ActiveRecordEntity();

    expect($entity->id)
    ->toBeNull();

    $entity->id = 1;

    expect($entity->id)
    ->toBe(1);

});

test('fetch', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = ActiveRecordEntity::fetch(2))
    ->toBeInstanceOf(ActiveRecordEntity::class);

    expect($entity->getAttributes())
    ->toBe([
        'id' => 2,
        'c1' => '',
    ]);

});

test('require', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entity = ActiveRecordEntity::require(2))
    ->toBeInstanceOf(ActiveRecordEntity::class);

    expect($entity->getAttributes())
    ->toBe([
        'id' => 2,
        'c1' => '',
    ]);

});

test('require not found', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, '')");

    ActiveRecordEntity::require(2);

})
->throws(EntityNotFoundException::class);

test('fetch all', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = ActiveRecordEntity::fetchAll())
    ->toBeArray();

    expect(count($entities))
    ->toBe(2);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(ActiveRecordEntity::class);

        expect($entity->getAttributes())
        ->toBe([
            'id' => $i + 1,
            'c1' => '',
        ]);

    }

});

test('fetch all where', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = ActiveRecordEntity::fetchAll(['id' => 2]))
    ->toBeArray();

    expect(count($entities))
    ->toBe(1);

    expect($entities[0]->getAttributes())
    ->toBe([
        'id' => 2,
        'c1' => '',
    ]);

});

test('yield', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = ActiveRecordEntity::yield())
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(ActiveRecordEntity::class);

        expect($entity->getAttributes())
        ->toBe([
            'id' => $i + 1,
            'c1' => '',
        ]);

    }

    expect($i)
    ->toBe(1);

});

test('yield where', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    expect($entities = ActiveRecordEntity::yield(['id' => 2]))
    ->toBeInstanceOf(Generator::class);

    foreach ($entities as $i => $entity) {

        expect($entity)
        ->toBeInstanceOf(ActiveRecordEntity::class);

        expect($entity->getAttributes())
        ->toBe([
            'id' => 2,
            'c1' => '',
        ]);

    }

    expect($i)
    ->toBe(0);

});

test('delete', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    $entity = new ActiveRecordEntity(['id' => 1]);

    expect($entity->delete())
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 2,
        'c1' => '',
    ]]);

});

test('insert', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($this->connection->pdo());

    $entity = new ActiveRecordEntity(['c1' => 'test']);

    expect($entity->insert())
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 1,
        'c1' => 'test',
    ]]);

    expect($entity->id)
    ->toBe(1);

});

test('create', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($this->connection->pdo());

    expect(ActiveRecordEntity::create(['c1' => 'test']))
    ->toBeInstanceOf(ActiveRecordEntity::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 1,
        'c1' => 'test',
    ]]);

});

test('update', function () {

    ActiveRecord::setEntityManager($this->entity_manager);

    createTestTable($pdo = $this->connection->pdo());

    $pdo->query("INSERT INTO test VALUES (1, ''), (2, '')");

    $entity = new ActiveRecordEntity(['id' => 2, 'c1' => 'test1']);

    expect($entity->update([
        'c1' => 'test2'
    ]))
    ->toBeInstanceOf(PDOStatement::class);

    expect($this->connection->fetchAll('SELECT * FROM test'))
    ->toBe([[
        'id' => 1,
        'c1' => '',
    ], [
        'id' => 2,
        'c1' => 'test2',
    ]]);

});

test('query', function () {

    expect(ActiveRecordEntity::query())
    ->toBeInstanceOf(Query::class);

});
