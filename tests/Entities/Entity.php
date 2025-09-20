<?php

declare(strict_types=1);

namespace Tests\Entities;

use MichaelRushton\ORM\Attributes\Column;
use MichaelRushton\ORM\Attributes\Table;

#[Table(name: 'test')]
class Entity
{
    #[Column]
    public int $id;

    #[Column(name: 'c1')]
    public string $name;

    public $not_a_column;
}
