<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    public function test_compra_incrementa_estoque(): void
    {
        $produto = Produto::create(['nome' => 'Fone BT', 'preco_venda' => 50.00]);

        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor A',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 10, 'preco_unitario' => 20.00],
            ],
        ])->assertStatus(201);

        $this->assertEquals(10, $produto->fresh()->estoque);
    }

    public function test_compra_atualiza_custo_medio(): void
    {
        $produto = Produto::create(['nome' => 'Cabo USB', 'preco_venda' => 30.00]);

        // Primeira compra: 10 unidades a R$10
        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor A',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 10, 'preco_unitario' => 10.00],
            ],
        ])->assertStatus(201);

        $produto->refresh();
        $this->assertEquals('10.00', $produto->custo_medio);

        // Segunda compra: 10 unidades a R$20
        // Custo médio = (10*10 + 20*10) / 20 = 300/20 = 15
        $this->postJson('/api/compras', [
            'fornecedor' => 'Fornecedor B',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 10, 'preco_unitario' => 20.00],
            ],
        ])->assertStatus(201);

        $produto->refresh();
        $this->assertEquals('15.00', $produto->custo_medio);
        $this->assertEquals(20, $produto->estoque);
    }
}
