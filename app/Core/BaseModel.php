<?php

namespace App\Core;

interface BaseModel
{
    /**
     * @method static create
     * @param array $attributes
     * @return self
     */
    public static function create(array $attributes): self;

    /**
     * @method static findOrFail
     * @param integer $id
     * @return self
     */
    public static function findOrFail(int $id): self;

    /**
     * @method static find
     * @param integer $id
     * @return self|null
     */
    public static function find(int $id): ?self;
}
