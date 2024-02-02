<?php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderByFieldScope implements Scope
{
    protected $orderByField;

    public function __construct($field = 'created_at')
    {
        $this->orderByField = $field;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy($this->orderByField, 'desc');
    }
}
