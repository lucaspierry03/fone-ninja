<?php

namespace App\Repositories;

use App\Models\Produto;

class ProdutoRepository extends BaseRepository
{
    public function __construct(Produto $model)
    {
        parent::__construct($model);
    }
}
