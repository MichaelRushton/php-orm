# PHP ORM

A PHP library to extend https://github.com/MichaelRushton/php-db with an ORM.

## Installation

```bash
composer require michaelrushton/orm
```

## Documentation

### Entity Manager

See [PHP DB](https://github.com/MichaelRushton/php-db) for documentation on creating a connection to a database.

```php
use MichaelRushton\ORM\EntityManager;

$entity_manager = new EntityManager($connection);
```

<br>

### Defining an entity

Add the `Table` attribute to a class to define the table name and a `Column` attribute to each property that represents a column in the table.

```php
#[Table(name: 'users')]
class User
{
    #[Column]
    public int $id;

    #[Column]
    public string $name;
}
```

<br>

If the primary key of the table is not an `id` column then add the `PrimaryKey` attribute to the class to define the primary key. You may pass an array of column names if the primary key is a composite key. If the primary key does not auto increment then pass `increments: false` to the attribute. Composite primary keys are automatically set to not auto increment.

```php
#[Table(name: 'users')]
#[PrimaryKey(column: 'user_id')]
class User
{
    #[Column]
    public int $user_id;

    #[Column]
    public string $name;
}
```

<br>

If the property name does not match the column name then pass the column name to the `Column` attribute.

```php
#[Table(name: 'users')]
#[PrimaryKey(column: 'user_id')]
class User
{
    #[Column(column: 'user_id')]
    public int $id;

    #[Column]
    public string $name;
}
```

<br>

### Fetching entities

To fetch an entity pass the entity class name and the primary key value to the `fetch` method. An array can be passed for composite primary keys, where the array key is the column name.

```php
$user = $entity_manager->fetch(User::class, 1);
```

<br>

You may also pass a callable instead of a primary key for more complex `WHERE` conditions. See the documentation [here](https://github.com/MichaelRushton/php-db/blob/main/docs/statements/select.md#where) for more information.

```php
use MichaelRushtwhere\DB\SQL\Components\Where;

$user = $entity_manager->fetch(User::class, function (Where $where)
{
    $where->where('id', 1)
    ->whereNull('deleted_at');
});
```

<br>

The `require` method is identical to the `fetch` method except an `EntityNotFoundException` exception will be thrown if the entity does not exist.

```php
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;

try {
    $user = $entity_manager->require(User::class, 1);
}

catch (EntityNotFoundException $e) {

}
```

<br>

Use the `fetchAll` method to fetch an array of entities, optionally passing an array of column values (or a callable; see above) to be used as the `WHERE` condition.

```php
$users = $entity_manager->fetchAll(User::class, ['active' => 1]);
```

<br>

The `yield` method is identical to the `fetchAll` method except that a `Generator` will be returned instead of an array.

```php
$users = $entity_manager->yield(User::class, ['active' => 1]);
```

<br>

### Inserting an entity

Use the `insert` method to insert an entity into the database. If the primary key auto increments then the corresponding property will be set after a successful insert.

```php
$user = new User;
$user->name = 'Michael';

$entity_manager->insert($user);

echo $user->id;
```

<br>

### Updating an entity

Use the `update` method to update an entity's database record.

```php
$user = $entity_manager->require(User::class, 1);

$user->active = 0;

$entity_manager->update($user);
```

<br>

### Deleting an entity

Use the `delete` method to delete an entity's database record.

```php
$user = $entity_manager->require(User::class, 1);

$entity_manager->delete($user);
```

<br>

### Querying the database for entities

Use the `query` method to perform more complex fetches. This method returns a slimmed-down version of the `SELECT` statement builder documented [here](https://github.com/MichaelRushton/php-db/blob/main/docs/statements/select.md). Entities can be retrieved using the `fetch`, `require`, `fetchAll`, or `yield` methods.

```php
$users = $entity_manager->query(User::class)
->where('active', 1)
->orderBy('id')
->limit(100, 100)
->fetchAll();
```

The following notable method groups are not supported by the entity query builder:

- `distinct` (applies by default)
- `columns` (all, and only, columns from the entity's table will be selected)
- `union`/`except`/`intersect`
- `group by`
- `having`

<br>

### Active records

An Active Record pattern is also available. To use active records, first attach an entity manager to the `ActiveRecord` class.

```php
use MichaelRushton\ORM\ActiveRecord;

ActiveRecord::setEntityManager($entity_manager);
```

<br>

### Defining an active record

Active records must extend the `ActiveRecord` class, setting the `Table` and (optionally) `PrimaryKey` attributes as required. Columns do not need to be defined as they will be accessed using the `__get` and `__set` magic methods

```php
#[Table(name: 'users')]
class User extends ActiveRecord {}
```

<br>

### Fetching active records

To fetch an active record pass the primary key value to the static `fetch` method. An array can be passed for composite primary keys, where the array key is the column name.

```php
$user = User::fetch(1);
```

<br>

You may also pass a callable instead of a primary key for more complex `WHERE` conditions. See the documentation [here](https://github.com/MichaelRushton/php-db/blob/main/docs/statements/select.md#where) for more information.

```php
use MichaelRushtwhere\DB\SQL\Components\Where;

$user = User::fetch(function (Where $where)
{
    $where->where('id', 1)
    ->whereNull('deleted_at');
});
```

<br>

The static `require` method is identical to the `fetch` method except an `EntityNotFoundException` exception will be thrown if the active record does not exist.

```php
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;

try {
    $user = User::require(1);
}

catch (EntityNotFoundException $e) {

}
```

<br>

Use the static `fetchAll` method to fetch an array of active records, optionally passing an array of column values (or a callable; see above) to be used as the `WHERE` condition.

```php
$users = User::fetchAll(['active' => 1]);
```

<br>

The static `yield` method is identical to the `fetchAll` method except that a `Generator` will be returned instead of an array.

```php
$users = User::yield(['active' => 1]);
```

<br>

### Inserting an active record

Use the `insert` method to insert an active record into the database. If the primary key auto increments then the corresponding property will be set after a successful insert.

```php
$user = new User([
    'name' => 'Michael',
]);

$user->insert();

echo $user->id;
```

<br>

You may also pass an array of column values directly to the `insert` method. This data will then be merged with any existing data.

```php
$user = new User([
    'name' => 'Michael',
]);

$user->insert([
    'email_address' => 'michael@example.com',
]);
```

<br>

The static `create` method can also be used to insert an active record.

```php
$user = User::create([
    'name' => 'Michael',
]);
```

<br>

### Updating an active

Use the `update` method to update an active record.

```php
$user = User::require(1);

$user->active = 0;

$user->update();
```

<br>

You may also pass an array of column values directly to the `update` method. This data will then be merged with any existing data.

```php
$user = User::require(1);

$user->update([
    'active' => 0,
]);
```

<br>

### Deleting an entity

Use the `delete` method to delete an active record.

```php
$user = User::require(1);

$user->delete();
```

<br>

### Querying the database for active records

Use the static `query` method to perform more complex fetches. This method returns a slimmed-down version of the `SELECT` statement builder documented [here](https://github.com/MichaelRushton/php-db/blob/main/docs/statements/select.md). Entities can be retrieved using the `fetch`, `require`, `fetchAll`, or `yield` methods.

```php
$users = User::query()
->where('active', 1)
->orderBy('id')
->limit(100, 100)
->fetchAll();
```
