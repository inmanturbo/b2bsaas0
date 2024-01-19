<?php

namespace B2bSaas;

use Illuminate\Database\Eloquent\Model;

trait HasEloquentModelableValue
{
    /**
     * Create an instance of the class defined by the value property of this instance.
     */
    public function createModel(array $attributes): Model
    {
        $model = $this->value;

        return $model::create($attributes);
    }

    public function toNewModel(): Model
    {
        $model = $this->value;

        return new $model;
    }
}
