<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait WithUuid
{
    use HasUuids;

    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) str()->ulid();
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
