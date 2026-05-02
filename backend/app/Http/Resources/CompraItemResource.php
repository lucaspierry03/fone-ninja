<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produto' => new ProdutoResource($this->whenLoaded('produto')),
            'quantidade' => $this->quantidade,
            'preco_unitario' => $this->preco_unitario,
        ];
    }
}
