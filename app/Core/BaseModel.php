<?php

namespace App\Core;

interface BaseModel
{
    /**
     * Create a new record in the database and return an instance of the model.
     */
    public static function create(array $attributes): self;

    /**
     * Find a record by its ID or throw an exception if not found.
     * Returns an instance of the model.
     */
    public static function findOrFail(int $id): self;

    /**
     * Find a record by its ID or return null if not found.
     * Returns an instance of the model or null.
     */
    public static function find(int $id): ?self;
}
