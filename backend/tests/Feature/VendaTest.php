<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendaTest extends TestCase
{
    use RefreshDatabase;

    private function criarProdutoComEstoque(string $nome = 'Fone BT', float $preco = 50.00, int $estoque = 10, float $custo = 20.00): Produto
    {
        return Produto::create([
            'nome' => $nome,
            'preco_venda' => $preco,
            'custo_medio' => $custo,
            'estoque' => $estoque,
        ]);
    }

    public function test_venda_desconta_estoque(): void
    {
        $produto = $this->criarProdutoComEstoque();

        $this->postJson('/api/vendas', [
            'cliente' => 'João',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 3, 'preco_unitario' => 50.00],
            ],
        ])->assertStatus(201);

        $this->assertEquals(7, $produto->fresh()->estoque);
    }

    public function test_venda_calcula_lucro_corretamente(): void
    {
        $produto = $this->criarProdutoComEstoque(custo: 20.00);

        // Venda: 5 unidades a R$50, custo médio R$20
        // Lucro = (50*5) - (20*5) = 250 - 100 = 150
        $response = $this->postJson('/api/vendas', [
            'cliente' => 'Maria',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 50.00],
            ],
        ]);

        $response->assertStatus(201);
        $venda = Venda::first();
        $this->assertEquals('250.00', $venda->total);
        $this->assertEquals('150.00', $venda->lucro);
    }

    public function test_venda_rejeita_estoque_insuficiente(): void
    {
        $produto = $this->criarProdutoComEstoque(estoque: 2);

        $response = $this->postJson('/api/vendas', [
            'cliente' => 'Carlos',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 50.00],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertEquals(2, $produto->fresh()->estoque);
    }

    public function test_cancelamento_reverte_estoque(): void
    {
        $produto = $this->criarProdutoComEstoque(estoque: 10);

        $response = $this->postJson('/api/vendas', [
            'cliente' => 'Ana',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 3, 'preco_unitario' => 50.00],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertEquals(7, $produto->fresh()->estoque);

        $venda = Venda::first();
        $this->patchJson("/api/vendas/{$venda->id}/cancelar")->assertStatus(200);

        $this->assertEquals(10, $produto->fresh()->estoque);
        $this->assertEquals('cancelada', $venda->fresh()->status->value);
    }
}
