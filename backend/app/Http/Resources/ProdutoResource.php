<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'preco_venda' => $this->preco_venda,
            'custo_medio' => $this->custo_medio,
            'estoque' => $this->estoque,
            'imagem_url' => $this->imagem_url,
        ];
    }
}
