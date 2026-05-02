<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(
        protected ProdutoRepository $produtoRepository,
        protected AuditService $auditService,
    ) {}

    public function registrar(array $data): Compra
    {
        return DB::transaction(function () use ($data) {
            $compra = Compra::create(['fornecedor' => $data['fornecedor'], 'total' => 0]);
            $total = 0;

            foreach ($data['produtos'] as $item) {
                $produto = Produto::lockForUpdate()->findOrFail($item['id']);

                $custoTotal = ($produto->custo_medio * $produto->estoque)
                            + ($item['preco_unitario'] * $item['quantidade']);
                $novoEstoque = $produto->estoque + $item['quantidade'];
                $produto->custo_medio = $custoTotal / $novoEstoque;
                $produto->estoque = $novoEstoque;
                $produto->save();

                $compra->items()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                ]);

                $total += $item['preco_unitario'] * $item['quantidade'];
            }

            $compra->update(['total' => $total]);

            $this->auditService->log($compra, 'compra.created', null, $compra->toArray());

            return $compra->load('items.produto');
        });
    }
}
