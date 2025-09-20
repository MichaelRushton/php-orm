<?php

declare(strict_types=1);

use MichaelRushton\ORM\DataMapper;
use MichaelRushton\ORM\Exceptions\EntityMissingTableAttributeException;
use MichaelRushton\ORM\Exceptions\InvalidEntityException;
use Tests\Entities\ActiveRecordEntity;
use Tests\Entities\Entity;
use Tests\Entities\NoIncrementsEntity;
use Tests\Entities\PrimaryKeyEntity;

test('table', function () {

    expect((new DataMapper(Entity::class))->table())
    ->toBe('test');

});

test('missing table attribute', function () {
    (new DataMapper(stdClass::class))->table();
})
->throws(EntityMissingTableAttributeException::class, stdClass::class);

test('primary key', function ($entity, $primary_key) {

    expect((new DataMapper($entity))->primaryKey())
    ->toBe($primary_key);

})
->with([
    [PrimaryKeyEntity::class, ['id1', 'id2']],
    [Entity::class, 'id'],
]);

test('increments', function ($entity, $increments) {

    expect((new DataMapper($entity))->increments())
    ->toBe($increments);

})
->with([
    [Entity::class, true],
    [PrimaryKeyEntity::class, false],
    [NoIncrementsEntity::class, false],
]);

test('merge and get entity data', function () {

    $entity = new Entity();

    $data_mapper = new DataMapper($entity::class);

    $data_mapper->mergeAttributes($entity, [
        'id' => '1',
        'c1' => 2,
    ]);

    expect($entity->id)
    ->toBe(1);

    expect($entity->name)
    ->toBe('2');

    expect($data_mapper->getAttributes($entity))
    ->toBe([
        'id' => 1,
        'c1' => '2',
    ]);

});

test('create entity', function () {

    $data_mapper = new DataMapper(Entity::class);

    expect($entity = $data_mapper->create(['id' => 1]))
    ->toBeInstanceOf(Entity::class);

    expect($data_mapper->getAttributes($entity))
    ->toBe(['id' => 1]);

});

test('set and get active record data', function () {

    $entity = new ActiveRecordEntity();

    $data_mapper = new DataMapper($entity::class);

    $data_mapper->mergeAttributes($entity, ['id' => 1]);

    expect($data_mapper->getAttributes($entity))
    ->toBe(['id' => 1]);

});

test('create active record', function () {

    $data_mapper = new DataMapper(ActiveRecordEntity::class);

    expect($entity = $data_mapper->create(['id' => 1]))
    ->toBeInstanceOf(ActiveRecordEntity::class);

    expect($data_mapper->getAttributes($entity))
    ->toBe(['id' => 1]);

});

test('invalid entity for setting data', function () {

    $data_mapper = new DataMapper(Entity::class);

    $data_mapper->mergeAttributes(new stdClass(), []);

})
->throws(InvalidEntityException::class, stdClass::class . ' entity is not an instance of ' . Entity::class);

test('invalid entity for getting data', function () {

    $data_mapper = new DataMapper(Entity::class);

    $data_mapper->getAttributes(new stdClass());

})
->throws(InvalidEntityException::class, stdClass::class . ' entity is not an instance of ' . Entity::class);
