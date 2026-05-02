<?php

namespace App\Repositories;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Collection;

class CompraRepository extends BaseRepository
{
    public function __construct(Compra $model)
    {
        parent::__construct($model);
    }

    public function withItems(): Collection
    {
        return $this->model->with('items.produto')->latest()->get();
    }

    public function withItemsPaginated(int $perPage = 20)
    {
        return $this->model->with('items.produto')->latest()->paginate($perPage);
    }
}
