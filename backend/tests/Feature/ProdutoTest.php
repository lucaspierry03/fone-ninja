<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_produto(): void
    {
        $response = $this->postJson('/api/produtos', [
            'nome' => 'Celular X',
            'preco_venda' => 99.90,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['nome' => 'Celular X']);

        $this->assertDatabaseHas('produtos', ['nome' => 'Celular X']);
    }

    public function test_can_list_produtos(): void
    {
        Produto::create(['nome' => 'Produto A', 'preco_venda' => 10.00]);
        Produto::create(['nome' => 'Produto B', 'preco_venda' => 20.00]);

        $response = $this->getJson('/api/produtos');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_validates_produto_name_min_3(): void
    {
        $response = $this->postJson('/api/produtos', [
            'nome' => 'AB',
            'preco_venda' => 10.00,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('nome');
    }
}
