<?php

namespace App\Services;

use App\Enums\VendaStatus;
use App\Models\Produto;
use App\Models\Venda;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendaService
{
    public function __construct(
        protected ProdutoRepository $produtoRepository,
        protected AuditService $auditService,
    ) {}

    public function registrar(array $data): Venda
    {
        return DB::transaction(function () use ($data) {
            $venda = Venda::create(['cliente' => $data['cliente'], 'total' => 0, 'lucro' => 0]);
            $total = 0;
            $lucro = 0;

            foreach ($data['produtos'] as $item) {
                $produto = Produto::lockForUpdate()->findOrFail($item['id']);

                if ($produto->estoque < $item['quantidade']) {
                    throw ValidationException::withMessages([
                        'produtos' => "Estoque insuficiente para {$produto->nome}.",
                    ]);
                }

                $produto->decrement('estoque', $item['quantidade']);

                $venda->items()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'custo_unitario' => $produto->custo_medio,
                ]);

                $subtotal = $item['preco_unitario'] * $item['quantidade'];
                $custo = $produto->custo_medio * $item['quantidade'];
                $total += $subtotal;
                $lucro += $subtotal - $custo;
            }

            $venda->update(['total' => $total, 'lucro' => $lucro]);

            $this->auditService->log($venda, 'venda.created', null, $venda->toArray());

            return $venda->load('items.produto');
        });
    }

    public function cancelar(Venda $venda): Venda
    {
        if ($venda->status === VendaStatus::Cancelada) {
            throw ValidationException::withMessages([
                'venda' => 'Venda já cancelada.',
            ]);
        }

        return DB::transaction(function () use ($venda) {
            $old = $venda->toArray();

            foreach ($venda->items as $item) {
                $item->produto->increment('estoque', $item->quantidade);
            }

            $venda->update([
                'status' => VendaStatus::Cancelada,
                'total' => 0,
                'lucro' => 0,
            ]);

            $this->auditService->log($venda, 'venda.cancelled', $old, $venda->fresh()->toArray());

            return $venda;
        });
    }
}
